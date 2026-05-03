<?php

namespace App\Entity\Taxon;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\Taxon\GenusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: GenusRepository::class)]
#[ApiResource(
	operations: [
		new GetCollection(
			normalizationContext: [
				'groups' => ['genus:list']
			]
			),
			new Get(
				normalizationContext: [
					'groups' => ['genus:read', 'species:list','tax:list']
				],
				uriTemplate: '/genus/{scientificName}',
			)
	]
)]
class Genus
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[ApiProperty(identifier: false)]
	#[Groups(['genus:list','genus:read'])]
	private ?int $id;

	#[ORM\Column(type: 'integer')]
	#[Assert\NotNull]
	#[Groups(['genus:list','genus:read'])]
	private ?int $taxonomyId;

	#[ORM\Column(type: 'string', length: 255)]
	#[Assert\NotNull]
	#[Groups(['genus:list','genus:read'])]
	#[ApiProperty(identifier: true)]
	private ?string $scientificName;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	#[Groups(['genus:list','genus:read'])]
	private ?string $vernacularName;

	#[ORM\ManyToOne(targetEntity: Family::class, inversedBy: 'genus')]
	#[ORM\JoinColumn(nullable: false)]
	#[Assert\NotNull]
	#[Groups(['genus:read'])]
	private ?Family $family;

	#[ORM\OneToMany(mappedBy: 'genus', targetEntity: Species::class, orphanRemoval: true)]
	#[Groups(['genus:read'])]
	#[SerializedName('children')]
	private Collection $species;

	public function __construct()
	{
		$this->species = new ArrayCollection();
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
		return $this->vernacularName;
	}

	public function setVernacularName(?string $VernacularName): self
	{
		$this->vernacularName = $VernacularName;

		return $this;
	}

	public function getFamily(): ?Family
	{
		return $this->family;
	}

	public function setFamily(?Family $Family): self
	{
		$this->family = $Family;

		return $this;
	}

	/**
	 * @return Collection<int, Species>
	 */
	public function getSpecies(): Collection
	{
		return $this->species;
	}

	public function addSpecies(Species $species): self
	{
		if (!$this->species->contains($species)) {
			$this->species[] = $species;
			$species->setGenus($this);
		}

		return $this;
	}

	public function removeSpecies(Species $species): self
	{
		if ($this->species->removeElement($species)) {
			// set the owning side to null (unless already changed)
			if ($species->getGenus() === $this) {
				$species->setGenus(null);
			}
		}

		return $this;
	}

	#[Groups(['genus:read', 'genus:list', 'tax:list'])]
	#[SerializedName('speciesCount')]
	function getSpeciesCount(): int
	{
		return $this->species->count();
	}
}
