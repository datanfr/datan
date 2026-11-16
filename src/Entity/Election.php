<?php

namespace App\Entity;

use App\Repository\ElectionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Un scrutin national soumis aux électeurs : régionales, départementales,
 * présidentielle, législatives, européennes.
 *
 * C'est le catalogue qui commande les pages `/elections` et
 * `/elections/{scrutin}`, et le pivot auquel se rattachent les {@see Candidature}
 * des députés. Il ne recense pas les élections municipales : l'application
 * d'origine les traite à part, sous un identifiant qui n'est pas dans son propre
 * catalogue.
 */
#[ORM\Entity(repositoryClass: ElectionRepository::class)]
#[ORM\Table(name: 'election')]
class Election
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Numéro du scrutin dans la base de production, que `elect_deputes_candidats`
     * désigne par sa colonne `election`.
     *
     * Il est repris tel quel plutôt que déduit du slug : c'est la seule clé qui
     * relie une candidature à son scrutin de part et d'autre de l'import, et un
     * slug peut être réécrit sans que la correspondance en pâtisse.
     */
    #[ORM\Column(type: Types::SMALLINT, unique: true)]
    private ?int $identifiant = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $slug = null;

    /** « Élections législatives », tel que le site l'écrit en titre. */
    #[ORM\Column(length: 100)]
    private ?string $libelle = null;

    /** « Législatives » : la forme courte, et la clé des textes de présentation. */
    #[ORM\Column(length: 50)]
    private ?string $libelleAbrege = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $annee = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateTour1 = null;

    /**
     * Nulle pour un scrutin à tour unique — les européennes de 2024. La base
     * d'origine y écrit `0000-00-00`, que l'import traduit en absence de date.
     */
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateTour2 = null;

    /** Vrai quand le scrutin a des candidats renseignés, donc une page à servir. */
    #[ORM\Column]
    private bool $candidats = false;

    /** Page de résultats du ministère de l'Intérieur, quand elle existe encore. */
    #[ORM\Column(length: 500, nullable: true)]
    private ?string $urlResultats = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifiant(): ?int
    {
        return $this->identifiant;
    }

    public function setIdentifiant(int $identifiant): static
    {
        $this->identifiant = $identifiant;

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

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getLibelleAbrege(): ?string
    {
        return $this->libelleAbrege;
    }

    public function setLibelleAbrege(string $libelleAbrege): static
    {
        $this->libelleAbrege = $libelleAbrege;

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function getDateTour1(): ?\DateTimeImmutable
    {
        return $this->dateTour1;
    }

    public function setDateTour1(?\DateTimeImmutable $dateTour1): static
    {
        $this->dateTour1 = $dateTour1;

        return $this;
    }

    public function getDateTour2(): ?\DateTimeImmutable
    {
        return $this->dateTour2;
    }

    public function setDateTour2(?\DateTimeImmutable $dateTour2): static
    {
        $this->dateTour2 = $dateTour2;

        return $this;
    }

    public function estCandidats(): bool
    {
        return $this->candidats;
    }

    public function setCandidats(bool $candidats): static
    {
        $this->candidats = $candidats;

        return $this;
    }

    public function getUrlResultats(): ?string
    {
        return $this->urlResultats;
    }

    public function setUrlResultats(?string $urlResultats): static
    {
        $this->urlResultats = $urlResultats;

        return $this;
    }
}
