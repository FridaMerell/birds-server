<?php

namespace App\Entity\Taxon;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Repository\Taxon\FamilyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FamilyRepository::class)]
#[ApiResource(
	operations: [
		new GetCollection(
			normalizationContext: [
				'groups' => ['family:list']
			]
		),
		new Get(
			normalizationContext: [
				'groups' => ['family:read', 'genus:list']
			],
			uriTemplate: '/family/{scientificName}',
		)
	]
)]
class Family
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[ApiProperty(identifier: false)]
	#[Groups(['family:list', 'family:read'])]
	private ?int $id;

	#[ORM\Column(type: 'integer')]
	#[Assert\NotNull]
	#[Groups(['family:list', 'family:read'])]
	private ?int $taxonomyId;

	#[ORM\Column(type: 'string', length: 255)]
	#[Assert\NotNull]
	#[Groups(['family:list', 'family:read'])]
	#[ApiProperty(identifier: true)]
	private ?string $scientificName;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	#[Groups(['family:list', 'family:read'])]
	private ?string $vernacularName;

	#[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'families')]
	#[ORM\JoinColumn(nullable: true)]
	#[Assert\NotNull]
	#[Groups(['family:read'])]
	private ?Order $taxOrder;

	#[ORM\OneToMany(mappedBy: 'family', targetEntity: Genus::class, orphanRemoval: true)]
	#[Groups(['family:read'])]
	#[SerializedName('children')]
	private Collection $genus;

	public function __construct()
	{
		$this->genus = new ArrayCollection();
	}

	function __toString()
	{
		return "{$this->vernacularName}  ({$this->scientificName})";
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTaxonomyId(): ?int
	{
		return $this->taxonomyId;
	}

	public function setTaxonomyId(int $TaxonomyId): self
	{
		$this->taxonomyId = $TaxonomyId;

		return $this;
	}

	public function getScientificName(): ?string
	{
		return $this->scientificName;
	}

	public function setScientificName(string $ScientificName): self
	{
		$this->scientificName = $ScientificName;

		return $this;
	}

	public function getVernacularName(): ?string
	{
		return $this->vernacularName ?? $this->scientificName;
	}

	public function setVernacularName(?string $VernacularName): self
	{
		$this->vernacularName = $VernacularName;

		return $this;
	}

	public function getTaxOrder(): ?Order
	{
		return $this->taxOrder;
	}

	public function setTaxOrder(?Order $TaxOrder): self
	{
		$this->taxOrder = $TaxOrder;

		return $this;
	}

	/**
	 * @return Collection<int, Genus>
	 */
	public function getGenus(): Collection
	{
		return $this->genus;
	}

	public function addGenu(Genus $genu): self
	{
		if (!$this->genus->contains($genu)) {
			$this->genus[] = $genu;
			$genu->setFamily($this);
		}

		return $this;
	}

	public function removeGenu(Genus $genu): self
	{
		if ($this->genus->removeElement($genu)) {
			// set the owning side to null (unless already changed)
			if ($genu->getFamily() === $this) {
				$genu->setFamily(null);
			}
		}

		return $this;
	}


	#[Groups(['family:read', 'family:list', 'tax:list'])]
	#[SerializedName('speciesCount')]
	function getSpeciesCount(): int
	{
		$count = 0;
		foreach ($this->genus as $genus) {
			$count += $genus->getSpecies()->count();
		}
		return $count;
	}
}
