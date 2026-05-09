<?php

namespace App\Repository\Birdnet;

use App\Entity\Birdnet\Detection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Detection>
 */
class DetectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Detection::class);
    }

    /** @return Detection[] */
    public function findLast24Hours(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.detectedAt > :since')
            ->setParameter('since', new \DateTimeImmutable('-24 hours'))
            ->orderBy('d.detectedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Detection[] detections newer than a given ID */
    public function findNewerThan(int $lastId): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.id > :lastId')
            ->setParameter('lastId', $lastId)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return array{speciesId: int, scientificName: string, vernacularName: ?string, detectionCount: string, latestDetection: mixed}[] */
    public function findSpeciesSummaryByDevice(int $deviceId): array
    {
        return $this->createQueryBuilder('d')
            ->select(
                's.id AS speciesId',
                's.scientificName AS scientificName',
                's.vernacularName AS vernacularName',
                'COUNT(d.id) AS detectionCount',
                'MAX(d.detectedAt) AS latestDetection',
            )
            ->join('d.species', 's')
            ->where('d.detectedAt > :since')
            ->andWhere('d.device = :deviceId')
            ->setParameter('since', new \DateTimeImmutable('-24 hours'))
            ->setParameter('deviceId', $deviceId)
            ->groupBy('d.species')
            ->orderBy('latestDetection', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function deleteOlderThan24Hours(): int
    {
        return $this->createQueryBuilder('d')
            ->delete()
            ->where('d.detectedAt < :cutoff')
            ->setParameter('cutoff', new \DateTimeImmutable('-24 hours'))
            ->getQuery()
            ->execute();
    }
}
