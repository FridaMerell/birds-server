<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Geographic\ValueObject\Point;
use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[ApiResource(
	security: "is_granted('ROLE_USER')",
	operations: [
		new GetCollection(
			uriTemplate: '/locations',
			normalizationContext: [
				'groups' => ['location:list', 'location:read']
			],
			security: "is_granted('PUBLIC_ACCESS')",
		),
		new Get(
			uriTemplate: '/locations/{id}',
			normalizationContext: [
				'groups' => ['location:list', 'location:read']
			],
		),
		new Delete(
			uriTemplate: '/locations/{id}',
			normalizationContext: [
				'groups' => ['location:list', 'location:read']
			],
			security: "is_granted('ROLE_USER')",
		),
		new Patch(
			uriTemplate: '/locations/{id}',
			normalizationContext: [
				'groups' => ['location:list', 'location:read']
			],
			security: "is_granted('ROLE_USER')",
		),
		new Post(
			uriTemplate: '/locations',
			normalizationContext: [
				'groups' => ['location:list']
			],
			denormalizationContext: [
				'groups' => ['location:write']
			],
			
		),
	]
)]
class Location
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups(['location:list', 'location:read', 'sighting:read'])]
	private ?int $id = null;
	#[ORM\Column(length: 255)]
	#[Groups(['location:list','location:write', 'location:read', 'sighting:read', 'sighting:list'])]
	private ?string $name = null;
	#[ORM\Column(type: 'point')]
	#[Groups(['location:read', 'location:write', 'location:list','sighting:read'])]
	private ?Point $point = null;

	#[ORM\Column(type: Types::FLOAT)]
	#[Groups(['location:list', 'location:write','location:read'])]
	private ?float $radius = null;

	#[ORM\OneToMany(mappedBy: 'location', targetEntity: Sighting::class)]
	private Collection $sightings;


	public function __construct()
	{
		$this->sightings = new ArrayCollection();
	}

	function __tostring()
	{
		return $this->name;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(string $Name): self
	{
		$this->name = $Name;

		return $this;
	}

	public function setPoint(Point $point): void
	{
		$this->point = $point;
	}

	public function getPoint(): Point
	{
		return $this->point;
	}

	#[Groups(['location:list', 'location:read', 'sighting:read'])]
	public function getLongitude(): float
	{
		return $this->point->getLongitude();
	}

	#[Groups(['location:list', 'location:read', 'sighting:read'])]
	public function getLatitude(): float
	{
		return $this->point->getLatitude();
	}

	/**
	 * @return float|null
	 */
	public function getRadius(): ?float
	{
		return $this->radius;
	}

	/**
	 * @param float|null $radius
	 */
	public function setRadius(?float $radius): void
	{
		$this->radius = $radius;
	}

	/**
	 * @return Collection<int, Sighting>
	 */
	public function getSightings(): Collection
	{
		return $this->sightings;
	}

	public function addSighting(Sighting $sighting): self
	{
		if (!$this->sightings->contains($sighting)) {
			$this->sightings->add($sighting);
			$sighting->setLocation($this);
		}

		return $this;
	}

	public function removeSighting(Sighting $sighting): self
	{
		if ($this->sightings->removeElement($sighting)) {
			// set the owning side to null (unless already changed)
			if ($sighting->getLocation() === $this) {
				$sighting->setLocation(null);
			}
		}

		return $this;
	}

	#[Groups(['location:list', 'location:read', 'sighting:read'])]
	function getTotalSightings(): int
	{
		return $this->sightings->count();
	}
}
