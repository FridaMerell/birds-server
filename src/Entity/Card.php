<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Taxon\Species;
use App\Entity\Taxon\TaxClass;
use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ApiResource(
	operations: [
		new Get(
			uriTemplate: 'card/{id}',
			normalizationContext: [
				'groups' => ['card:read']
			]
		),
		new Get(
			uriTemplate: 'card/families/{id}',
			normalizationContext: [
				'groups' => ['card:families']
			]
		),
		new GetCollection(
			uriTemplate: 'cards',
			normalizationContext: [
				'groups' => ['card:list']
			],
		),
		new Delete(
			security: "is_granted('ROLE_USER') ",
			uriTemplate: 'card/{id}'
		)
	],
)]
class Card implements EditableEntityInterface
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups(['card:read', 'card:created', 'card:list'])]
	private ?int $id = null;

	#[Groups(['card:read', 'card:created', 'card:list'])]
	#[ORM\Column(length: 255)]
	private ?string $name = null;

	#[ORM\ManyToMany(targetEntity: Species::class, inversedBy: 'cards')]
	#[ORM\JoinTable(name: 'card_species')]
	#[Groups(['card:read', 'card:created'])]
	private Collection $species;

	#[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'cards')]
	#[Groups(['card:read', 'card:created'])]

	private Collection $subscribers;

	#[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
	#[Groups(['card:read', 'card:list', 'card:created'])]
	private ?\DateTimeInterface $start = null;

	#[Groups(['card:read', 'card:list', 'card:created'])]
	#[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
	private ?\DateTimeInterface $ends = null;

	#[ORM\ManyToMany(targetEntity: Sighting::class, inversedBy: 'cards')]
	#[ORM\JoinTable(name: 'card_sightings')]
	#[Groups(['card:read'])]
	private Collection $sightings;

	#[ORM\ManyToOne(inversedBy: 'cards')]
	#[Groups(['card:read', 'card:created', 'card:list'])]
	private ?TaxClass $taxonomy = null;

	public function __construct()
	{
		$this->species = new ArrayCollection();
		$this->subscribers = new ArrayCollection();
		$this->sightings = new ArrayCollection();
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

	#[Groups(['card:read'])]
	function getSpeciesByFamily(): array
	{
		$speciesByFamily = [];
		foreach ($this->species as $species) {
			$genus = $species->getGenus();
			if ($genus) {
				$family = $genus->getFamily();
				if ($family) {
					$familyName = $family->getVernacularName();
					if (!isset($speciesByFamily[$familyName])) {
						$speciesByFamily[$familyName] = [];
					}
					$speciesByFamily[$familyName][] = [
						'id' => $species->getId(),
						'taxonomyId' => $species->getTaxonomyId(),
						'scientificName' => $species->getScientificName(),
						'vernacularName' => $species->getVernacularName(),
						'hasSighting' => $this->sightings->exists(function (int $key, Sighting $element) use ($species) {
							return $species === $element->getSpecies();
						}),
					];
				}
			}
		}
		return $speciesByFamily;
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
			$this->species->add($species);
		}

		return $this;
	}

	function setSpecies(Collection $species): self
	{
		$this->species = $species;
		return $this;
	}

	public function removeSpecies(Species $species): self
	{
		$this->species->removeElement($species);

		return $this;
	}

	public function hasSpecies(Species $species): bool
	{
		return $this->species->contains($species);
	}

	/**
	 * @return Collection<int, User>
	 */
	public function getSubscribers(): Collection
	{
		return $this->subscribers;
	}

	public function addSubscriber(User $subscriber): self
	{
		if (!$this->subscribers->contains($subscriber)) {
			$this->subscribers->add($subscriber);
		}

		return $this;
	}

	public function removeSubscriber(User $subscriber): self
	{
		$this->subscribers->removeElement($subscriber);

		return $this;
	}

	public function getStart(): ?\DateTimeInterface
	{
		return $this->start;
	}

	public function setStart(?\DateTimeInterface $Start): self
	{
		$this->start = $Start;

		return $this;
	}

	public function getEnds(): ?\DateTimeInterface
	{
		return $this->ends;
	}

	public function setEnds(?\DateTimeInterface $Ends): self
	{
		$this->ends = $Ends;

		return $this;
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
		}

		return $this;
	}

	public function removeSighting(Sighting $sighting): self
	{
		$this->sightings->removeElement($sighting);

		return $this;
	}

	function getFirstSighting(Species $species): ?Sighting
	{
		return $this->sightings->findFirst(function (int $key, Sighting $element) use ($species) {
			return $species === $element->getSpecies();
		});
	}

	function getDistinctSightings(): array
	{
		$ret = [];
		$retSpecies = [];
		foreach ($this->sightings as $sighting) {
			if (!in_array($sighting->getSpecies(), $retSpecies)) {
				$ret[] = $sighting;
				$retSpecies[] = $sighting->getSpecies();
			}
		}

		return $ret;
	}

	function getOwners(): array
	{
		return $this->getSubscribers()->toArray();
	}

	function isRestricted(): bool
	{
		return true;
	}

	#[Groups(['card:families'])]
	function getFamilies(): array
	{
		$families = [];
		foreach ($this->species as $species) {
			$genus = $species->getGenus();
			if ($genus) {
				$family = $genus->getFamily();
				if ($family && !in_array($family->getVernacularName(), array_column($families, 'vernacularName'))) {
					$families[] = [
						'vernacularName' => $family->getVernacularName(),
						'scientificName' => $family->getScientificName(),
						'id' => $family->getId(),
					];
				}
			}
		}
		return $families;
	}

	public function getTaxonomy(): ?TaxClass
	{
		return $this->taxonomy;
	}

	public function setTaxonomy(?TaxClass $taxonomy): static
	{
		$this->taxonomy = $taxonomy;

		return $this;
	}
}
