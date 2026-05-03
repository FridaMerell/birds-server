<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Odm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter as FilterSearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\Post;
use App\Entity\Taxon\Species;
use App\Repository\SightingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SightingRepository::class)]
#[ORM\Table(name: 'sighting')]
#[ApiResource(
	operations: [
		new GetCollection(
			uriTemplate: 'sightings',
			normalizationContext: [
				'groups' => ['sighting:list', 'species:list']
			],
			order: ['dateTime' => 'DESC']
		),
		new Get(
			uriTemplate: 'sighting/{id}'
		),
		new Post(
			uriTemplate:'sighting',
			security: 'is_granted("ROLE_USER")'
		),
		new Delete(
			uriTemplate: 'sighting/{id}',
			security: 'is_granted("ROLE_USER") '
		)
	]
)]

#[ApiFilter(
	FilterSearchFilter::class,
	properties: [
		'species.vernacularName' => 'partial',
		'place' => 'partial',
		'location.id' => 'partial',
		'user' => 'exact'
	]
)]
#[ApiFilter(
	DateFilter::class,
	properties: [
		'dateTime']
)]
class Sighting implements EditableEntityInterface
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[Groups(['sighting:list', 'sighting:read', 'location:read', 'card:read'])]
	private ?int $id;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sightings')]
	#[ORM\JoinColumn(nullable: false)]
	#[Groups(['sighting:list', 'sighting:read', 'location'])]
	private ?User $user;

	#[ORM\ManyToOne(targetEntity: Species::class, inversedBy: 'sightings')]
	#[ORM\JoinColumn(nullable: false)]
	#[ORM\JoinTable(name: 'sighting_species')]
	#[Groups(['sighting:list', 'sighting:read', 'card:read'])]
	private ?Species $species;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	#[Groups(['sighting:list', 'sighting:read'])]
	private ?string $place;

	#[ORM\Column(type: 'datetime')]
	#[Groups(['sighting:list', 'sighting:read'])]
	private ?DateTime $dateTime;

	#[ORM\Column(type: 'string', length: 512, nullable: true)]
	#[Groups(['sighting:read'])]
	private ?string $comment;

	#[ORM\ManyToMany(targetEntity: Card::class, mappedBy: 'sightings')]
	#[Groups(['sighting:read'])]
	private Collection $cards;

	#[ORM\ManyToOne(inversedBy: 'sightings')]
	#[Groups(['sighting:list', 'sighting:read'])]
	private ?Location $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(length: 255, nullable: true, type: Types::JSON)]
    private ?string $coordinates = null;

	public function __construct()
	{
		$this->cards = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUser(): ?User
	{
		return $this->user;
	}

	public function setUser(?User $User): self
	{
		$this->user = $User;

		return $this;
	}

	public function getSpecies(): ?Species
	{
		return $this->species;
	}

	public function setSpecies(?Species $Species): self
	{
		$this->species = $Species;

		return $this;
	}

	public function getPlace(): ?string
	{
		return $this->place;
	}

	public function setPlace(?string $Place): self
	{
		$this->place = $Place;

		return $this;
	}

	public function getDateTime(): ?\DateTimeInterface
	{
		return $this->dateTime;
	}

	public function setDateTime(\DateTimeInterface $DateTime): self
	{
		$this->dateTime = $DateTime;

		return $this;
	}

	public function getComment(): ?string
	{
		return $this->comment;
	}

	public function setComment(?string $Comment): self
	{
		$this->comment = $Comment;

		return $this;
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
			$card->addSighting($this);
		}

		return $this;
	}

	public function removeCard(Card $card): self
	{
		if ($this->cards->removeElement($card)) {
			$card->removeSighting($this);
		}

		return $this;
	}

	public function getLocation(): ?Location
	{
		return $this->location;
	}

	public function setLocation(?Location $Location): self
	{
		$this->location = $Location;

		return $this;
	}

	function isRestricted(): bool
	{
		return true;
	}

	function getOwners(): array
	{
		return [$this->getUser()];
	}

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getCoordinates(): ?string
    {
        return $this->coordinates;
    }

    public function setCoordinates(?string $coordinates): static
    {
        $this->coordinates = $coordinates;

        return $this;
    }
}
