<?php

namespace App\Entity\Taxon;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Repository\Taxon\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => ['tax:list','order:list', 'tax:list']
            ],
        ),
        new Get(
            normalizationContext: [
                'groups' => ['species:list', 'order:read', 'tax:list']
            ],
            uriTemplate: '/order/{scientificName}/species'
        ),
        new Get(
            normalizationContext: [
                'groups' => ['order:read', 'family:list', 'tax:list']
            ],
            uriTemplate: '/order/{scientificName}',
        ),
        new Delete(
            security: "is_granted('ROLE_USER')",
            uriTemplate: '/order/{taxonomyId}'
        )
    ]

)]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['tax:list','order:list', 'order:read'])]
    #[ApiProperty(identifier: false)]
    private ?int $id;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Groups(['tax:list','order:list', 'order:read'])]
    private ?int $taxonomyId;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotNull]
    #[Groups(['tax:list','order:list', 'order:read'])]
    private ?string $scientificName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['tax:list','order:list', 'order:read'])]
    private ?string $vernacularName;

    #[ORM\ManyToOne(targetEntity: TaxClass::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotNull]
    #[Groups(['order:read'])]
    private ?TaxClass $class;

    #[ORM\OneToMany(mappedBy: 'taxOrder', targetEntity: Family::class, orphanRemoval: true)]
    #[Groups(groups: ['order:read'])]
    #[SerializedName('children')]
    private Collection $families;


    public function __construct()
    {
        $this->families = new ArrayCollection();
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

    public function getClass(): ?TaxClass
    {
        return $this->class;
    }

    public function setClass(?TaxClass $Class): self
    {
        $this->class = $Class;

        return $this;
    }

    /**
     * @return Collection<int, Family>
     */
    public function getFamilies(): Collection
    {
        return $this->families;
    }

    public function addFamily(Family $family): self
    {
        if (!$this->families->contains($family)) {
            $this->families[] = $family;
            $family->setTaxOrder($this);
        }

        return $this;
    }

    public function removeFamily(Family $family): self
    {
        if ($this->families->removeElement($family)) {
            // set the owning side to null (unless already changed)
            if ($family->getTaxOrder() === $this) {
                $family->setTaxOrder(null);
            }
        }

        return $this;
    }

    #[ApiProperty()]
    #[Groups(['species:list'])]
    function getSpecies()
    {
        $species = [];
        foreach ($this->families as $family) {
            foreach ($family->getGenus() as $genus) {
                foreach ($genus->getSpecies() as $specie) {
                    $species[] = $specie;
                }
            }
        }
        return $species;
    }

    #[Groups(['order:read',  'tax:list'])]
    #[SerializedName('speciesCount')]
    function getSpeciesCount(): int
    {
        return count($this->getSpecies());
    }
}
