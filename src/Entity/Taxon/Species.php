<?php

namespace App\Entity\Taxon;

use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Entity\Birdnet\Detection;
use App\Entity\Card;
use App\Entity\CardTemplate;
use App\Entity\Sighting;
use App\Entity\User;
use App\Repository\Taxon\SpeciesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\QueryParameter;
use App\State\IsSubscribedProvider;
use App\State\SpeciesParentFilter;

#[ORM\Entity(repositoryClass: SpeciesRepository::class)]

#[ApiResource(
	security: "is_granted('VIEW', object)",
	provider: IsSubscribedProvider::class,
	operations: [
		new GetCollection(
			normalizationContext: [
				'groups' => ['species:list']
			],
			order: ['vernacularName' => 'ASC'],
			parameters: [
				'vernacularName' => new QueryParameter(
					filter: new PartialSearchFilter,
					properties: ['vernacularName']
				),
				'taxonomy' => new QueryParameter(
					key: 'taxonomy',
					filter: new SpeciesParentFilter(),
				)
			],
		),
		new Get(
			normalizationContext: [
				'groups' => ['species:read']
			],
			uriTemplate: '/species/{scientificName}',
			security: " is_granted('VIEW', object)",
		),
		new Post(
			security: "is_granted('ROLE_USER')",
			uriTemplate: '/species'
		)

	],
)]
class Species
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[ApiProperty(identifier: false)]
	#[Groups(['species:list', 'sighting:list', 'species:read', 'card:read'])]
	private ?int $id;
	#[ORM\Column(type: 'integer')]
	#[Assert\NotNull]
	#[Groups(['species:list', 'species:read', 'sighting:list', 'card:read', 'card:created'])]
	private ?int $taxonomyId;
	#[ORM\Column(type: 'string', length: 255)]
	#[Assert\NotNull]
	#[Groups(['species:list', 'species:read', 'card:read'])]
	#[ApiProperty(identifier: true)]
	private ?string $scientificName;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	#[Groups(['species:list', 'species:read', 'sighting:list', 'card:read'])]
	private ?string $vernacularName;
	#[ORM\ManyToOne(targetEntity: Genus::class, inversedBy: 'species')]
	#[ORM\JoinColumn(nullable: true)]
	#[Groups(['species:read'])]
	private ?Genus $genus;
	private ?Family $Family;
	#[ORM\OneToMany(mappedBy: 'species', targetEntity: Sighting::class, orphanRemoval: true)]
	#[Groups(['species:read'])]
	private Collection $sightings;
	#[ORM\ManyToMany(targetEntity: Card::class, mappedBy: 'species')]
	#[ORM\JoinTable(name: 'card_species')]
	#[Groups(['species:read'])]
	private Collection $cards;
	#[ORM\Column(length: 255, nullable: true)]
	#[Groups(['species:read'])]
	private ?string $swedishProminence = null;

	#[ORM\ManyToOne(inversedBy: 'species',	targetEntity: TaxClass::class)]
	#[ORM\JoinColumn(name: 'direct_tax_class_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
	private ?TaxClass $taxClass = null;

	#[Groups(['species:list', 'species:read', 'tax:read', 'tax:list', 'tax:with-species'])]
	private ?bool $isSubscribed = null;

	/**
	 * @var Collection<int, User>
	 */
	#[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'subscribedSpecies')]
	#[Groups(['species:read'])]
	private Collection $subscribers;

	/**
	 * @var Collection<int, CardTemplate>
	 */
	#[ORM\ManyToMany(targetEntity: CardTemplate::class, mappedBy: 'species')]
	private Collection $cardTemplates;

    /**
     * @var Collection<int, Detection>
     */
    #[ORM\OneToMany(mappedBy: 'species', targetEntity: Detection::class, orphanRemoval: true)]
    private Collection $detections;


	public function __construct()
	{
		$this->sightings = new ArrayCollection();
		$this->cards = new ArrayCollection();
		$this->subscribers = new ArrayCollection();
		$this->cardTemplates = new ArrayCollection();
        $this->detections = new ArrayCollection();
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

	public function getGenus(): ?Genus
	{
		return $this->genus;
	}

	public function setGenus(?Genus $Genus): self
	{
		$this->genus = $Genus;

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
			$this->sightings[] = $sighting;
			$sighting->setSpecies($this);
		}

		return $this;
	}

	public function removeSighting(Sighting $sighting): self
	{
		if ($this->sightings->removeElement($sighting)) {
			// set the owning side to null (unless already changed)
			if ($sighting->getSpecies() === $this) {
				$sighting->setSpecies(null);
			}
		}

		return $this;
	}

	function getFullName(): string
	{
		return "{$this->vernacularName} ({$this->scientificName})";
	}

	function getFamily(): ?Family
	{
		return $this->genus->getFamily();
	}

	/**
	 * @return Collection<int, Card>
	 */
	public function getCards(): Collection
	{
		return $this->cards;
	}

	public function addCard(Card $card): self
	{
		if (!$this->cards->contains($card)) {
			$this->cards->add($card);
			$card->addSpecies($this);
		}

		return $this;
	}

	public function removeCard(Card $card): self
	{
		if ($this->cards->removeElement($card)) {
			$card->removeSpecies($this);
		}

		return $this;
	}

	public function getSwedishProminence(): ?string
	{
		return $this->swedishProminence;
	}

	public function setSwedishProminence(string $SwedishProminence): self
	{
		$this->swedishProminence = $SwedishProminence;

		return $this;
	}

	#[Groups(['species:list', 'species:read'])]
	function getClassification(): ?TaxClass
	{
		if ($this->taxClass !== null) {
			return $this->taxClass;
		}
		if ($this->genus === null) {
			return null;
		}
		return $this->genus->getFamily()->getTaxOrder()->getClass();
	}

	public function getTaxClass(): ?TaxClass
	{
		return $this->taxClass;
	}

	public function setTaxClass(?TaxClass $taxClass): static
	{
		$this->taxClass = $taxClass;

		return $this;
	}

	/**
	 * @return Collection<int, User>
	 */
	public function getSubscribers(): Collection
	{
		return $this->subscribers;
	}

	public function addSubscriber(User $subscriber): static
	{
		if (!$this->subscribers->contains($subscriber)) {
			$this->subscribers->add($subscriber);
			$subscriber->addSubscribedSpecies($this);
		}

		return $this;
	}

	public function removeSubscriber(User $subscriber): static
	{
		if ($this->subscribers->removeElement($subscriber)) {
			$subscriber->removeSubscribedSpecies($this);
		}

		return $this;
	}

	/**
	 * @return Collection<int, CardTemplate>
	 */
	public function getCardTemplates(): Collection
	{
		return $this->cardTemplates;
	}

	public function addCardTemplate(CardTemplate $cardTemplate): static
	{
		if (!$this->cardTemplates->contains($cardTemplate)) {
			$this->cardTemplates->add($cardTemplate);
			$cardTemplate->addSpecies($this);
		}

		return $this;
	}

	public function removeCardTemplate(CardTemplate $cardTemplate): static
	{
		if ($this->cardTemplates->removeElement($cardTemplate)) {
			$cardTemplate->removeSpecies($this);
		}

		return $this;
	}

	public function getIsSubscribed(): ?bool
	{
		return $this->isSubscribed;
	}

	public function setIsSubscribed(?bool $isSubscribed): static
	{
		$this->isSubscribed = $isSubscribed;

		return $this;
	}

	#[Groups(['tax:list', 'species:read'])]
	public function getTaxonomy(): ?string
	{
		return 'species';
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
            $detection->setSpecies($this);
        }

        return $this;
    }

    public function removeDetection(Detection $detection): static
    {
        if ($this->detections->removeElement($detection)) {
            // set the owning side to null (unless already changed)
            if ($detection->getSpecies() === $this) {
                $detection->setSpecies(null);
            }
        }

        return $this;
    }
}
