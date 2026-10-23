<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Catégorie thématique d'un décryptage (table « fields » de l'app d'origine).
 */
#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ORM\Table(name: 'categorie')]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['categorie:read']],
)]
#[ApiFilter(SearchFilter::class, properties: ['slug' => 'exact', 'name' => 'partial'])]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['categorie:read', 'decryptage:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['categorie:read', 'categorie:write', 'decryptage:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 64)]
    #[Groups(['categorie:read', 'categorie:write', 'decryptage:read'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['categorie:read', 'categorie:write'])]
    private ?string $libelle = null;

    /**
     * @var Collection<int, Decryptage>
     */
    #[ORM\OneToMany(targetEntity: Decryptage::class, mappedBy: 'categorie')]
    private Collection $decryptages;

    public function __construct()
    {
        $this->decryptages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Decryptage>
     */
    public function getDecryptages(): Collection
    {
        return $this->decryptages;
    }

    public function addDecryptage(Decryptage $decryptage): static
    {
        if (!$this->decryptages->contains($decryptage)) {
            $this->decryptages->add($decryptage);
            $decryptage->setCategorie($this);
        }

        return $this;
    }

    public function removeDecryptage(Decryptage $decryptage): static
    {
        if ($this->decryptages->removeElement($decryptage)) {
            if ($decryptage->getCategorie() === $this) {
                $decryptage->setCategorie(null);
            }
        }

        return $this;
    }
}
