<?php

namespace App\Controller;

use App\Entity\Birdnet\Detection;
use App\Repository\Birdnet\DetectionRepository;
use App\Repository\Birdnet\DeviceRepository;
use App\Repository\SpeciesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/birdnet')]
class DetectionController extends AbstractController
{
    public function __construct(
        private readonly DetectionRepository  $repo,
        private readonly EntityManagerInterface $em, 
        private readonly DeviceRepository $deviceRepo,
        private readonly SpeciesRepository $speciesRepo,
        private readonly string $piApiKey,         // injected via services.yaml
    ) {}


    #[Route('/species-summary', methods: ['GET'])]
    public function statusRequest(Request $request): JsonResponse
    {
        $deviceId = $request->query->get('deviceId');
        if (!ctype_digit((string) $deviceId)) {
            return $this->json(['error' => 'Missing or invalid deviceId'], 400);
        }

        $rows = $this->repo->findSpeciesSummaryByDevice((int) $deviceId);

        $data = array_map(function (array $row): array {
            $latestRaw = $row['latestDetection'];
            $latest = $latestRaw instanceof \DateTimeInterface
                ? \DateTimeImmutable::createFromInterface($latestRaw)
                : new \DateTimeImmutable($latestRaw);

            return [
                'species' => [
                    'id'             => $row['speciesId'],
                    'scientificName' => $row['scientificName'],
                    'vernacularName' => $row['vernacularName'],
                ],
                'detectionCount'  => (int) $row['detectionCount'],
                'latestDetection' => $latest->setTimezone(new \DateTimeZone('UTC'))
                    ->format(\DateTimeInterface::ATOM),
            ];
        }, $rows);

        return $this->json($data);
    }

    // ── POST /api/detections ──────────────────────────────────────────────────
    #[Route('/detections', methods: ['POST'])]
    public function ingest(Request $request): JsonResponse
    {
        if ($request->headers->get('X-Api-Key') !== $this->piApiKey) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        
        $data = json_decode($request->getContent(), true);
        $deviceId =$data['device_id'] ?? null;
        if (!$this->isValidPayload($data)) {
            return $this->json(['error' => 'Invalid payload'], 422);
        }

        $detection = new Detection();
        $detection->setSpecies($this->speciesRepo->findOneBy(['scientificName' => $data['species']]));
        $detection->setDetectedAt(new \DateTimeImmutable());
        $detection->setDevice(
            $this->deviceRepo->findOneBy(['name' => $deviceId]) ?? null
        ); // Optional: associate with a Device if you have that info
        $detection->setConfidence((float) $data['confidence']);

        $this->em->persist($detection);
        $this->em->flush();

        return $this->json($detection->toArray(), 201);
    }

    // ── GET /api/detections ───────────────────────────────────────────────────
    #[Route('/detections', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $rows = array_map(fn(Detection $d) => $d->toArray(), $this->repo->findLast24Hours());
        return $this->json($rows);
    }

    // ── GET /api/detections/stream  (Server-Sent Events) ─────────────────────
    #[Route('/detections/stream', methods: ['GET'])]
    public function streamDetections(Request $request): StreamedResponse
    {
        // Prefer Last-Event-ID header (sent automatically by the browser on reconnect)
        // over the query-string fallback used for the initial connection.
        $lastId = (int) ($request->headers->get('Last-Event-ID')
            ?? $request->query->get('lastId', 0));

        $response = new StreamedResponse(function () use ($lastId) {
            set_time_limit(0);       // prevent PHP from timing out this worker
            ignore_user_abort(true); // keep running after disconnect so connection_aborted() works

            while (ob_get_level() > 0) ob_end_clean();

            $currentLastId = $lastId;
            $lastHeartbeat = time();
            $startTime     = time();

            // Reconnect every 55 s so reverse-proxy idle timeouts (usually 60 s) are never hit.
            // The browser respects the retry hint and reconnects automatically.
            echo "retry: 3000\n\n";
            flush();

            while (true) {
                if (connection_aborted()) break;

                // Force a clean reconnect cycle rather than running forever
                if ((time() - $startTime) >= 55) break;

                $detections = $this->repo->findNewerThan($currentLastId);

                foreach ($detections as $d) {
                    $id = $d->getId();
                    echo "id: {$id}\ndata: " . json_encode($d->toArray()) . "\n\n";
                    $currentLastId = $id;
                }

                if ($detections) flush();

                if (time() - $lastHeartbeat >= 15) {
                    echo ": heartbeat\n\n";
                    flush();
                    $lastHeartbeat = time();
                }

                if (!$this->em->isOpen()) break;

                $this->em->clear();

                sleep(2);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Connection', 'keep-alive');
        return $response;
    }

    private function isValidPayload(mixed $data): bool
    {
        return is_array($data)
            && isset($data['species'], $data['confidence'])
            && is_string($data['species'])
            && is_numeric($data['confidence'])
            && $data['confidence'] >= 0.0
            && $data['confidence'] <= 1.0;
    }
}
