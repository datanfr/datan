<?php

namespace App\Entity;

use App\Repository\DepartementRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Département, collectivité ou circonscription d'élection des députés.
 *
 * Reprise de la table `departement` de l'application d'origine, elle-même issue
 * du jeu de données `villes_fr`. Rien dans l'open data de l'Assemblée ne la
 * remplace : les articles sont un choix de rédaction, et la région ne se déduit
 * d'aucun mandat.
 */
#[ORM\Entity(repositoryClass: DepartementRepository::class)]
#[ORM\Table(name: 'departement')]
class Departement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Code INSEE : « 01 », « 2A », « 099 » pour les Français de l'étranger. */
    #[ORM\Column(length: 15, unique: true)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /** Slug de l'application d'origine, qui fait l'adresse : `ain-01`. */
    #[ORM\Column(length: 100, unique: true)]
    private ?string $slug = null;

    /**
     * Article de lieu : « dans l' », « dans les », « dans le ».
     *
     * Attention, ces deux libellés portent leur espace final quand ils en ont
     * besoin (« des » suivi d'un espace, « de l' » sans) : l'application
     * d'origine les concatène directement au nom. Les rogner casse la moitié
     * des titres du site.
     */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $libelleDans = null;

    /** Article d'appartenance : « de l' », « du », « des ». */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $libelleDe = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $region = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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

    public function getLibelleDans(): ?string
    {
        return $this->libelleDans;
    }

    public function setLibelleDans(?string $libelleDans): static
    {
        $this->libelleDans = $libelleDans;

        return $this;
    }

    public function getLibelleDe(): ?string
    {
        return $this->libelleDe;
    }

    public function setLibelleDe(?string $libelleDe): static
    {
        $this->libelleDe = $libelleDe;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;

        return $this;
    }

    /** « de l'Ain », « du Nord », « des Hauts-de-Seine ». */
    public function getNomAvecArticle(): string
    {
        return $this->libelleDe . $this->nom;
    }
}
