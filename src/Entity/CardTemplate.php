<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Taxon\Species;
use App\Entity\Taxon\TaxClass;
use App\Repository\CardTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardTemplateRepository::class)]
#[ApiResource(
    security: "is_granted('ROLE_USER')"
)]
class CardTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cardTemplates')]
    private ?User $owner = null;

    /**
     * @var Collection<int, Species>
     */
    #[ORM\ManyToMany(targetEntity: Species::class, inversedBy: 'cardTemplates')]
    private Collection $species;

    #[ORM\ManyToOne(inversedBy: 'cardTemplates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TaxClass $Category = null;

    public function __construct()
    {
        $this->species = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, Species>
     */
    public function getSpecies(): Collection
    {
        return $this->species;
    }

    public function addSpecies(Species $species): static
    {
        if (!$this->species->contains($species)) {
            $this->species->add($species);
        }

        return $this;
    }

    public function removeSpecies(Species $species): static
    {
        $this->species->removeElement($species);

        return $this;
    }

    public function getCategory(): ?TaxClass
    {
        return $this->Category;
    }

    public function setCategory(?TaxClass $Category): static
    {
        $this->Category = $Category;

        return $this;
    }
}
