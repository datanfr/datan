<?php

namespace App\Entity;

use App\Repository\CommuneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Commune française, avec sa population et sa ou ses circonscriptions
 * législatives.
 *
 * Reprise des tables `circos` (découpage électoral) et `cities` (référentiel
 * INSEE) de l'application d'origine. C'est la seule source qui relie une
 * commune à son député : l'open data de l'Assemblée s'arrête à la
 * circonscription.
 */
#[ORM\Entity(repositoryClass: CommuneRepository::class)]
#[ORM\Table(name: 'commune')]
#[ORM\Index(name: 'idx_departement_slug', columns: ['departement_id', 'slug'])]
#[ORM\Index(name: 'idx_departement_population', columns: ['departement_id', 'population'])]
class Commune
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, unique: true)]
    private ?string $codeInsee = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    /**
     * Le slug fait l'adresse (`ville_bourg-en-bresse`) mais n'est pas unique,
     * même dans un département : Château-Chinon (Ville) et Château-Chinon
     * (Campagne) partagent `chateau-chinon` dans la Nièvre.
     */
    #[ORM\Column(length: 100)]
    private ?string $slug = null;

    /** Population INSEE, absente de 794 communes fusionnées ou récentes. */
    #[ORM\Column(nullable: true)]
    private ?int $population = null;

    /**
     * Population au recensement de 2012, qui ne sert qu'à énoncer l'évolution
     * sur dix ans. Absente des mêmes communes que la population courante.
     */
    #[ORM\Column(nullable: true)]
    private ?int $population2012 = null;

    /**
     * Code postal — au pluriel en réalité : 48 communes en portent plusieurs,
     * séparés par des barres obliques (Nice, « 06000/06100/06200/06300 »), et
     * le site les affiche tels quels. Paris, Lyon et Marseille n'en ont aucun :
     * le leur dépend de l'arrondissement, et le référentiel de l'application
     * d'origine ne descend pas à ce niveau.
     */
    #[ORM\Column(length: 40, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Departement $departement = null;

    /**
     * Maire de la commune, en trois colonnes plutôt qu'une entité : le site
     * n'en affiche que le nom et n'a besoin de la civilité que pour accorder
     * « le / la maire ». 34 874 communes sur 35 720 en ont un — les absentes
     * sont surtout des communes fusionnées.
     *
     * Le nom arrive en capitales du référentiel (« FABRE ») et s'affiche en
     * casse mixte ; on stocke la graphie de la source, comme pour le reste.
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $mairePrenom = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $maireNom = null;

    /** « M » ou « F », tel que le référentiel l'écrit. */
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $maireCivilite = null;

    /**
     * Communes limitrophes, servant la barre de navigation en haut de la fiche.
     * La relation est déclarée dans un seul sens mais la table porte les deux
     * couples : l'application d'origine les stocke ainsi et la lecture se fait
     * en SQL.
     *
     * @var Collection<int, Commune>
     */
    #[ORM\ManyToMany(targetEntity: self::class)]
    #[ORM\JoinTable(name: 'commune_adjacente')]
    #[ORM\JoinColumn(name: 'commune_id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'adjacente_id', onDelete: 'CASCADE')]
    private Collection $adjacentes;

    /**
     * Une commune peut être partagée entre plusieurs circonscriptions : 118 le
     * sont, dont Paris, découpée en dix-huit.
     *
     * @var Collection<int, CommuneCirconscription>
     */
    #[ORM\OneToMany(targetEntity: CommuneCirconscription::class, mappedBy: 'commune')]
    private Collection $circonscriptions;

    public function __construct()
    {
        $this->circonscriptions = new ArrayCollection();
        $this->adjacentes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeInsee(): ?string
    {
        return $this->codeInsee;
    }

    public function setCodeInsee(string $codeInsee): static
    {
        $this->codeInsee = $codeInsee;

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

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(?int $population): static
    {
        $this->population = $population;

        return $this;
    }

    public function getPopulation2012(): ?int
    {
        return $this->population2012;
    }

    public function setPopulation2012(?int $population2012): static
    {
        $this->population2012 = $population2012;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): static
    {
        $this->departement = $departement;

        return $this;
    }

    public function getMairePrenom(): ?string
    {
        return $this->mairePrenom;
    }

    public function setMairePrenom(?string $mairePrenom): static
    {
        $this->mairePrenom = $mairePrenom;

        return $this;
    }

    public function getMaireNom(): ?string
    {
        return $this->maireNom;
    }

    public function setMaireNom(?string $maireNom): static
    {
        $this->maireNom = $maireNom;

        return $this;
    }

    public function getMaireCivilite(): ?string
    {
        return $this->maireCivilite;
    }

    public function setMaireCivilite(?string $maireCivilite): static
    {
        $this->maireCivilite = $maireCivilite;

        return $this;
    }

    /**
     * @return Collection<int, CommuneCirconscription>
     */
    public function getCirconscriptions(): Collection
    {
        return $this->circonscriptions;
    }

    /**
     * @return Collection<int, Commune>
     */
    public function getAdjacentes(): Collection
    {
        return $this->adjacentes;
    }
}
