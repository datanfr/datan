<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\DossierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Dossier législatif auquel se rattache un scrutin (table « dossiers » d'origine).
 */
#[ORM\Entity(repositoryClass: DossierRepository::class)]
#[ORM\Table(name: 'dossier')]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['dossier:read']],
)]
#[ApiFilter(SearchFilter::class, properties: ['dossierId' => 'exact', 'legislature' => 'exact'])]
class Dossier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['dossier:read', 'scrutin:read'])]
    private ?int $id = null;

    /** Identifiant Assemblée nationale du dossier (ex. « DLR5L17N47883 »). */
    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['dossier:read', 'dossier:write', 'scrutin:read'])]
    private ?string $dossierId = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['dossier:read', 'dossier:write', 'scrutin:read'])]
    private ?int $legislature = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['dossier:read', 'dossier:write', 'scrutin:read'])]
    private ?string $titre = null;

    /** Segment d'URL du dossier sur assemblee-nationale.fr. */
    #[ORM\Column(length: 300, nullable: true)]
    #[Groups(['dossier:read', 'dossier:write', 'scrutin:read'])]
    private ?string $titreChemin = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['dossier:read', 'dossier:write'])]
    private ?string $procedureParlementaire = null;

    /**
     * Code Assemblée nationale de la procédure parlementaire. Les textes portés
     * par le Gouvernement sont les projets de loi : code 1 (projet de loi
     * ordinaire) et 21 (projet de loi de finances rectificative).
     */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['dossier:read', 'dossier:write'])]
    private ?int $procedureCode = null;

    /**
     * Codes de procédure des textes présentés par le Gouvernement, tels que
     * retenus par l'application d'origine pour le taux de soutien.
     */
    public const PROCEDURES_GOUVERNEMENT = [1, 21];

    /**
     * Organe de la commission saisie au fond (ex. « PO59048 »), lu dans l'acte
     * `AN1-COM-FOND` du dossier. Fonde le score « Votes par spécialisation » :
     * la participation d'un député aux scrutins des textes examinés dans sa
     * commission. Nul pour un dossier jamais renvoyé en commission (86 % des
     * scrutins de la 17e ont un dossier qui le porte).
     */
    #[ORM\Column(length: 25, nullable: true)]
    #[Groups(['dossier:read', 'dossier:write'])]
    private ?string $commissionFond = null;

    /**
     * @var Collection<int, Scrutin>
     */
    #[ORM\OneToMany(targetEntity: Scrutin::class, mappedBy: 'dossier')]
    private Collection $scrutins;

    public function __construct()
    {
        $this->scrutins = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDossierId(): ?string
    {
        return $this->dossierId;
    }

    public function setDossierId(string $dossierId): static
    {
        $this->dossierId = $dossierId;

        return $this;
    }

    public function getLegislature(): ?int
    {
        return $this->legislature;
    }

    public function setLegislature(?int $legislature): static
    {
        $this->legislature = $legislature;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getTitreChemin(): ?string
    {
        return $this->titreChemin;
    }

    public function setTitreChemin(?string $titreChemin): static
    {
        $this->titreChemin = $titreChemin;

        return $this;
    }

    public function getProcedureParlementaire(): ?string
    {
        return $this->procedureParlementaire;
    }

    public function setProcedureParlementaire(?string $procedureParlementaire): static
    {
        $this->procedureParlementaire = $procedureParlementaire;

        return $this;
    }

    public function getProcedureCode(): ?int
    {
        return $this->procedureCode;
    }

    public function setProcedureCode(?int $procedureCode): static
    {
        $this->procedureCode = $procedureCode;

        return $this;
    }

    public function getCommissionFond(): ?string
    {
        return $this->commissionFond;
    }

    public function setCommissionFond(?string $commissionFond): static
    {
        $this->commissionFond = $commissionFond;

        return $this;
    }

    /**
     * @return Collection<int, Scrutin>
     */
    public function getScrutins(): Collection
    {
        return $this->scrutins;
    }
}
