<?php

namespace App\Entity\Birdnet;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Birdnet\DeviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ApiResource(
    uriTemplate: '/api/devices',
    operations: [
        new \ApiPlatform\Metadata\GetCollection(),
        new \ApiPlatform\Metadata\Post(),
    ]
)]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $installedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $apiKey = null;

    #[ORM\Column(nullable: true)]
    private ?bool $active = null;

    /**
     * @var Collection<int, Detection>
     */
    #[ORM\OneToMany(mappedBy: 'device', targetEntity: Detection::class, orphanRemoval: true)]
    private Collection $detections;

    public function __construct()
    {
        $this->detections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getInstalledAt(): ?\DateTime
    {
        return $this->installedAt;
    }

    public function setInstalledAt(\DateTime $installedAt): static
    {
        $this->installedAt = $installedAt;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, Detection>
     */
    public function getDetections(): Collection
    {
        return $this->detections;
    }

    public function addDetection(Detection $detection): static
    {
        if (!$this->detections->contains($detection)) {
            $this->detections->add($detection);
            $detection->setDevice($this);
        }

        return $this;
    }

    public function removeDetection(Detection $detection): static
    {
        if ($this->detections->removeElement($detection)) {
            // set the owning side to null (unless already changed)
            if ($detection->getDevice() === $this) {
                $detection->setDevice(null);
            }
        }

        return $this;
    }

     public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'installedAt' => $this->installedAt->format('Y-m-d'),
            'active'      => $this->active,
        ];
    }
}
