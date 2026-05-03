<?php

/** @noinspection ALL */

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Entity\Taxon\Species;
use App\Repository\UserRepository;
use App\State\CurrentUserProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ApiResource(
	operations: [
		new Get(
			security: "is_granted('ROLE_USER')",
			uriTemplate: 'user/me',
			provider: CurrentUserProvider::class,
			normalizationContext: [
				'groups' => ['user:self']
			]
		),
		new Get(
			uriTemplate: 'user/{id}',
			normalizationContext: [
				'groups' => ['user:read']
			]
		),
		new GetCollection(
			uriTemplate: 'user',
			normalizationContext: [
				'groups' => ['user:list']
			]
		),
		new Patch(
			security: "is_granted('ROLE_USER') and object == user",
			uriTemplate: 'user/me',
			normalizationContext: [
				'groups' => ['user:self']
			]
		)
	]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups([
		'user:self',
		'user:list',
		'user:read',
		'sighting:list',
		'card:created',
	])]
	private ?int $id = null;

	#[ORM\Column(length: 180, unique: true)]
	#[Groups([
		'user:self',
		'user:list',
		'card:created',
		'user:read',
	])]
	private ?string $email = null;

	#[ORM\Column]
	private array $roles = [];

	#[ORM\Column(length: 255, nullable: true)]
	#[Groups([
		'user:self',
		'user:list',
		'user:read',
		'card:created',
		'sighting:list'
	])]
	private ?string $username = null;

	/**
	 * @var ?string The hashed password
	 */
	#[ORM\Column]
	private ?string $password = null;

	#[ORM\OneToMany(mappedBy: 'user', targetEntity: Sighting::class, orphanRemoval: true)]
	#[ORM\OrderBy(['id' => 'DESC'])]
	private Collection $sightings;

	#[ORM\Column(type: 'boolean')]
	private $isVerified = false;

	#[ORM\ManyToMany(targetEntity: Card::class, mappedBy: 'subscribers')]
	private Collection $cards;

	#[ORM\OneToMany(mappedBy: 'user', targetEntity: AccessToken::class)]
	private Collection $accessTokens;

	/**
	 * @var Collection<int, Species>
	 */
	#[ORM\ManyToMany(targetEntity: Species::class, inversedBy: 'subscribers')]
	#[Groups(['user:self'])]
	private Collection $subscribedSpecies;

	/**
	 * @var Collection<int, CardTemplate>
	 */
	#[ORM\OneToMany(mappedBy: 'owner', targetEntity: CardTemplate::class)]
	#[Groups(['user:self'])]
	private Collection $cardTemplates;

	#[ORM\Column(type: 'boolean', options: ['default' => false])]
	private ?bool $bildspelIntegration = false;

	public function __construct()
	{
		$this->sightings = new ArrayCollection();
		$this->cards = new ArrayCollection();
		$this->accessTokens = new ArrayCollection();
		$this->subscribedSpecies = new ArrayCollection();
		$this->cardTemplates = new ArrayCollection();
	}

	function __toString(): string
	{
		return $this->getUserIdentifier();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getEmail(): ?string
	{
		return $this->email;
	}

	public function setEmail(string $email): self
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUserIdentifier(): string
	{
		return $this->Username ?? (string)$this->email;
	}

	/**
	 * @see UserInterface
	 */
	public function getRoles(): array
	{
		$roles = $this->roles;
		// guarantee every user at least has ROLE_USER
		$roles[] = 'ROLE_USER';

		return array_unique($roles);
	}

	public function setRoles(array $roles): self
	{
		$this->roles = $roles;

		return $this;
	}

	/**
	 * @see PasswordAuthenticatedUserInterface
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	public function setPassword(string $password): self
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials(): void
	{
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
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
			$sighting->setUser($this);
		}

		return $this;
	}

	public function removeSighting(Sighting $sighting): self
	{
		if ($this->sightings->removeElement($sighting)) {
			// set the owning side to null (unless already changed)
			if ($sighting->getUser() === $this) {
				$sighting->setUser(null);
			}
		}

		return $this;
	}

	public function isVerified(): bool
	{
		return $this->isVerified;
	}

	public function setIsVerified(bool $isVerified): self
	{
		$this->isVerified = $isVerified;

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
			$card->addSubscriber($this);
		}

		return $this;
	}

	public function removeCard(Card $card): self
	{
		if ($this->cards->removeElement($card)) {
			$card->removeSubscriber($this);
		}

		return $this;
	}

	function getActiveCards(?\DateTime $dateTime): array
	{
		$cards = [];
		if (!$dateTime)
			$dateTime = new \DateTime();
		/** @var Card $card */
		foreach ($this->cards as $card) {
			if (!$card->getEnds() && !$card->getStart())
				$cards[] = $card;
			if ($card->getStart() > $dateTime || $card->getEnds() < $dateTime) continue;
			$cards[] = $card;
		}
		return $cards;
	}

	/**
	 * @return Collection<int, AccessToken>
	 */
	public function getAccessTokens(): Collection
	{
		return $this->accessTokens;
	}

	public function addAccessToken(AccessToken $accessToken): self
	{
		if (!$this->accessTokens->contains($accessToken)) {
			$this->accessTokens->add($accessToken);
			$accessToken->setUser($this);
		}

		return $this;
	}

	public function removeAccessToken(AccessToken $accessToken): self
	{
		if ($this->accessTokens->removeElement($accessToken)) {
			// set the owning side to null (unless already changed)
			if ($accessToken->getUser() === $this) {
				$accessToken->setUser(null);
			}
		}

		return $this;
	}

	public function getUsername(): ?string
	{
		return $this->username;
	}

	public function setUsername(string $Username): self
	{
		$this->username = $Username;

		return $this;
	}

	/**
	 * @return Collection<int, Species>
	 */
	public function getSubscribedSpecies(): Collection
	{
		return $this->subscribedSpecies;
	}

	public function addSubscribedSpecies(Species $subscribedSpecies): static
	{
		if (!$this->subscribedSpecies->contains($subscribedSpecies)) {
			$this->subscribedSpecies->add($subscribedSpecies);
		}

		return $this;
	}

	public function removeSubscribedSpecies(Species $subscribedSpecies): static
	{
		$this->subscribedSpecies->removeElement($subscribedSpecies);

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
			$cardTemplate->setOwner($this);
		}

		return $this;
	}

	public function removeCardTemplate(CardTemplate $cardTemplate): static
	{
		if ($this->cardTemplates->removeElement($cardTemplate)) {
			// set the owning side to null (unless already changed)
			if ($cardTemplate->getOwner() === $this) {
				$cardTemplate->setOwner(null);
			}
		}

		return $this;
	}

	public function isBildspelIntegration(): ?bool
	{
		return $this->bildspelIntegration;
	}

	public function setBildspelIntegration(bool $bildspelIntegration): static
	{
		$this->bildspelIntegration = $bildspelIntegration;

		return $this;
	}
}
