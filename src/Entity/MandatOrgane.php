<?php

namespace App\Entity;

use App\Repository\MandatOrganeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mandat d'un député dans un {@see Organe} — l'équivalent, pour les organes
 * sans table dédiée, de {@see FonctionCommission} (COMPER) et de
 * {@see FonctionGroupe} (GP). À ce jour : les délégations du Bureau
 * (`DELEGBUREAU`), affichées par l'écran « Postes Assemblée » de la rédaction.
 *
 * **Clé métier : l'uid du mandat** (« PM849098 »), unique dans l'open data ;
 * l'upsert par lot s'y appuie pour rester idempotent d'une moisson à l'autre.
 *
 * Pas de `#[ApiResource]` : donnée de référence interne, hors API publique.
 */
#[ORM\Entity(repositoryClass: MandatOrganeRepository::class)]
#[ORM\Table(name: 'mandat_organe')]
class MandatOrgane
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Identifiant Assemblée nationale du mandat (« PM849098 »). */
    #[ORM\Column(length: 50, unique: true)]
    private ?string $uid = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Depute $depute = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Organe $organe = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $legislature = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $codeQualite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelleQualite = null;

    /**
     * Drapeau du rattachement principal (0/1). Comme pour {@see FonctionGroupe},
     * un député peut porter plusieurs mandats dans un organe ; seul le principal
     * compte dans un effectif. Stocké en SMALLINT, jamais en booléen PHP (le
     * pilote mysqli lierait un `false` en chaîne vide, refusée par MariaDB).
     */
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 1])]
    private int $nominPrincipale = 1;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateFin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getDepute(): ?Depute
    {
        return $this->depute;
    }

    public function setDepute(?Depute $depute): static
    {
        $this->depute = $depute;

        return $this;
    }

    public function getOrgane(): ?Organe
    {
        return $this->organe;
    }

    public function setOrgane(?Organe $organe): static
    {
        $this->organe = $organe;

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

    public function getCodeQualite(): ?string
    {
        return $this->codeQualite;
    }

    public function setCodeQualite(?string $codeQualite): static
    {
        $this->codeQualite = $codeQualite;

        return $this;
    }

    public function getLibelleQualite(): ?string
    {
        return $this->libelleQualite;
    }

    public function setLibelleQualite(?string $libelleQualite): static
    {
        $this->libelleQualite = $libelleQualite;

        return $this;
    }

    public function getNominPrincipale(): int
    {
        return $this->nominPrincipale;
    }

    public function setNominPrincipale(int $nominPrincipale): static
    {
        $this->nominPrincipale = $nominPrincipale;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }
}
