<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Acteur rattaché à un dossier législatif : son initiateur (qui a déposé le
 * texte) ou l'un de ses rapporteurs (`dossiers_acteurs` de l'application
 * d'origine).
 *
 * C'est la donnée des deux variantes du bloc auteur de la page de vote que
 * datan.fr sert sur les scrutins **sans** amendement : « L'auteur de la
 * proposition de loi » sur une proposition ou une résolution, « Le rapporteur »
 * partout ailleurs (`Votes::index`, `get_dossier_mp_authors` /
 * `get_dossier_mp_rapporteurs`). Alimentée par `app:import:dossiers-acteurs`.
 *
 * Un dossier porte plusieurs lignes : un texte peut être déposé par plusieurs
 * députés, et il a un rapporteur par étape d'examen — jusqu'à 116 sur un projet
 * de loi de finances, un par mission budgétaire.
 *
 * `ref` désigne un acteur (`PA…`) ou un organe (`PO…`) selon `type` ; c'est
 * `depute.mp_id` pour le premier. Pas de relation Doctrine : la page de vote lit
 * en DBAL, et l'entité n'existe ici que pour tenir le schéma.
 */
#[ORM\Entity]
#[ORM\Table(name: 'dossier_acteur')]
// La clé porte `etape` et `type` : un même député est rapporteur à plusieurs
// étapes du même dossier, et parfois rapporteur *et* rapporteur pour avis sur
// une seule. Sans elles, l'upsert écraserait ces lignes les unes sur les autres.
#[ORM\UniqueConstraint(name: 'uniq_dossier_acteur', columns: ['dossier_id', 'role', 'type', 'ref', 'etape'])]
#[ORM\Index(name: 'idx_dossier_acteur_role', columns: ['dossier_id', 'role'])]
class DossierActeur
{
    /** Dépôt du texte : l'initiateur est l'auteur de la proposition de loi. */
    public const ROLE_INITIATEUR = 'initiateur';

    public const ROLE_RAPPORTEUR = 'rapporteur';

    /** `type` d'un initiateur qui est une personne, par opposition à `organe`. */
    public const TYPE_ACTEUR = 'acteur';

    /** `type` d'un initiateur qui est un organe — le Gouvernement d'un projet de loi. */
    public const TYPE_ORGANE = 'organe';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $dossierId = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $legislature = null;

    /** {@see self::ROLE_INITIATEUR} ou {@see self::ROLE_RAPPORTEUR}. */
    #[ORM\Column(length: 20)]
    private ?string $role = null;

    /**
     * Nature de la ligne : « acteur » ou « organe » pour un initiateur, le
     * `typeRapporteur` de l'open data pour un rapporteur — « rapporteur »,
     * « rapporteur pour avis », « rapporteur spécial », « rapporteur général ».
     * Le site ne les distingue pas : les quatre remplissent la même carte.
     */
    #[ORM\Column(length: 40)]
    private ?string $type = null;

    /** Référence de l'acteur (`PA…`) ou de l'organe (`PO…`). */
    #[ORM\Column(length: 30)]
    private ?string $ref = null;

    /**
     * Code de l'acte législatif où le rapporteur a été désigné (« AN1 », « CMP »,
     * « SN1 »…). Chaîne vide pour un initiateur, qui n'est rattaché à aucune
     * étape : la valeur nulle rendrait la clé unique inopérante — MariaDB admet
     * autant de NULL qu'on veut dans un index unique.
     */
    #[ORM\Column(length: 30, options: ['default' => ''])]
    private string $etape = '';

    /** Mandat sous lequel l'initiateur a déposé le texte, quand la source le dit. */
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $mandatRef = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDossierId(): ?int
    {
        return $this->dossierId;
    }

    public function setDossierId(int $dossierId): static
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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): static
    {
        $this->ref = $ref;

        return $this;
    }

    public function getEtape(): string
    {
        return $this->etape;
    }

    public function setEtape(string $etape): static
    {
        $this->etape = $etape;

        return $this;
    }

    public function getMandatRef(): ?string
    {
        return $this->mandatRef;
    }

    public function setMandatRef(?string $mandatRef): static
    {
        $this->mandatRef = $mandatRef;

        return $this;
    }
}
