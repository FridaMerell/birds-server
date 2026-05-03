<?php

namespace App\Entity\Taxon;

use ApiPlatform\Doctrine\Odm\State\ItemProvider;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Card;
use App\Entity\CardTemplate;
use App\Repository\Taxon\TaxClassRepository;
use App\State\IsSubscribedProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaxClassRepository::class)]
#[ApiResource(
	operations: [
		new GetCollection(
			normalizationContext: [
				'groups' => ['tax:list', "tax:list"]
			]
		),
		new Get(
			normalizationContext: [
				'groups' => ['species:list', 'tax:read', 'tax:list', 'tax:with-species']
			],
			uriTemplate: '/tax_class/{scientificName}/species',
			order: ['scientificName' => 'ASC'],
		),
		new Get(
			normalizationContext: [
				'groups' => ['tax:read', 'order:list', 'tax:list']
			],
			uriTemplate: '/tax_class/{scientificName}',
			security: " is_granted('VIEW', object)",
			provider: IsSubscribedProvider::class,
		),
		new Post(
			security: "is_granted('ROLE_USER')",
			uriTemplate: '/tax_class'
		),
		new Patch(
			security: "is_granted('ROLE_USER')",
			uriTemplate: '/tax_class/{scientificName}'
		)
	]
)]
class TaxClass
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[Groups(['tax:list', 'tax:read', 'card:created'])]
	#[ApiProperty(identifier: false)]
	private ?int $id;

	#[ORM\Column(type: 'integer', nullable: true)]
	#[Groups(['tax:list', 'tax:read', 'card:created'])]
	private ?int $taxonomyId;

	#[ORM\Column(type: 'string', length: 255)]
	#[Assert\NotNull]
	#[Groups(['tax:list', 'tax:read', 'card:created'])]
	#[ApiProperty(identifier: true)]
	private ?string $scientificName;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	#[Groups(['tax:list', 'tax:read', 'card:created'])]
	private ?string $vernacularName;

	#[ORM\OneToMany(mappedBy: 'class', targetEntity: Order::class, orphanRemoval: true)]
	#[Groups(['tax:read'])]
	#[SerializedName('children')]
	private Collection $orders;

	#[ORM\Column(length: 128, nullable: true)]
	#[Groups(['tax:list', 'tax:read', 'sighting:list', 'species:list'])]
	private ?string $icon = null;

	/**
	 * @var Collection<int, Species>
	 */
	#[ORM\OneToMany(mappedBy: 'taxClass', targetEntity: Species::class)]
	private Collection $species;

    /**
     * @var Collection<int, CardTemplate>
     */
    #[ORM\OneToMany(mappedBy: 'Category', targetEntity: CardTemplate::class)]
    private Collection $cardTemplates;

    /**
     * @var Collection<int, Card>
     */
    #[ORM\OneToMany(mappedBy: 'taxonomy', targetEntity: Card::class)]
    private Collection $cards;


	public function __construct()
	{
		$this->orders = new ArrayCollection();
		$this->species = new ArrayCollection();
        $this->cardTemplates = new ArrayCollection();
        $this->cards = new ArrayCollection();
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

	/**
	 * @return Collection<int, Order>
	 */
	public function getOrders(): Collection
	{
		return $this->orders;
	}

	public function addOrder(Order $order): self
	{
		if (!$this->orders->contains($order)) {
			$this->orders[] = $order;
			$order->setClass($this);
		}

		return $this;
	}

	public function removeOrder(Order $order): self
	{
		if ($this->orders->removeElement($order)) {
			// set the owning side to null (unless already changed)
			if ($order->getClass() === $this) {
				$order->setClass(null);
			}
		}

		return $this;
	}


	#[Groups(['tax:read', "tax:list"])]
	#[SerializedName('unsortedSpeciesCount')]
	function getUnsortedSpeciesCount(): int
	{
		return $this->getUnsortedSpecies()->count();
	}

	#[Groups(['tax:read', "tax:list"])]
	#[SerializedName('speciesCount')]
	function getSpeciesCount(): int
	{

		$species = $this->species;

		foreach ($this->getOrders() as $order) {
			foreach ($order->getFamilies() as $family) {
				foreach ($family->getGenus() as $genus) {
					foreach ($genus->getSpecies() as $specie) {
						$species->add($specie);
					}
				}
			}
		}
		return $species->count();
	}

	public function getIcon(): ?string
	{
		return $this->icon;
	}

	public function setIcon(?string $icon): static
	{
		$this->icon = $icon;

		return $this;
	}

	function getUnsortedSpecies(): Collection
	{
		return $this->species->filter(function (Species $element) {
			return $element && $element->getGenus() === null;;
		});
	}

	/**
	 * @return Collection<int, Species>
	 */
	#[Groups(['tax:with-species'])]
	public function getSpecies(): Collection
	{

		$species = $this->species;
		foreach ($this->getOrders() as $order) {
			foreach ($order->getFamilies() as $family) {
				foreach ($family->getGenus() as $genus) {
					foreach ($genus->getSpecies() as $specie) {
						$species->add($specie);
					}
				}
			}
		}

		return $this->species;
	}

	public function addSpecies(Species $species): static
	{
		if (!$this->species->contains($species)) {
			$this->species->add($species);
			$species->setTaxClass($this);
		}

		return $this;
	}

	public function removeSpecies(Species $species): static
	{
		if ($this->species->removeElement($species)) {
			// set the owning side to null (unless already changed)
			if ($species->getTaxClass() === $this) {
				$species->setTaxClass(null);
			}
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
            $cardTemplate->setCategory($this);
        }

        return $this;
    }

    public function removeCardTemplate(CardTemplate $cardTemplate): static
    {
        if ($this->cardTemplates->removeElement($cardTemplate)) {
            // set the owning side to null (unless already changed)
            if ($cardTemplate->getCategory() === $this) {
                $cardTemplate->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): static
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setTaxonomy($this);
        }

        return $this;
    }

    public function removeCard(Card $card): static
    {
        if ($this->cards->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getTaxonomy() === $this) {
                $card->setTaxonomy(null);
            }
        }

        return $this;
    }
}
