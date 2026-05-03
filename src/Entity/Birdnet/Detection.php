<?php

namespace App\Entity\Birdnet;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Taxon\Species;
use App\Repository\Birdnet\DetectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetectionRepository::class)]
#[ApiResource]
class Detection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'detections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Species $species = null;

    #[ORM\Column]
    private ?float $confidence = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $detectedAt = null;

    #[ORM\ManyToOne(inversedBy: 'detections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Device $device = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpecies(): ?Species
    {
        return $this->species;
    }

    public function setSpecies(?Species $species): static
    {
        $this->species = $species;

        return $this;
    }

    public function getConfidence(): ?float
    {
        return $this->confidence;
    }

    public function setConfidence(float $confidence): static
    {
        $this->confidence = $confidence;

        return $this;
    }

    public function getDetectedAt(): ?\DateTimeImmutable
    {
        return $this->detectedAt;
    }

    public function setDetectedAt(\DateTimeImmutable $detectedAt): static
    {
        $this->detectedAt = $detectedAt;

        return $this;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): static
    {
        $this->device = $device;

        return $this;
    }


    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'species'     => [
                'scientificName' => $this->species?->getScientificName(),
                'vernacularName'     => $this->species?->getVernacularName(),
            ],
            'confidence'  => $this->confidence,
            'detectedAt'  => $this->detectedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
