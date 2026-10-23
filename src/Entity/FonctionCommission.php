<?php

namespace App\Entity;

use App\Repository\FonctionCommissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mandat d'un député dans une commission permanente, avec la qualité exercée
 * (membre, président, vice-président, secrétaire, rapporteur spécial) et ses
 * bornes de date.
 *
 * Un député cumule plusieurs lignes par législature : l'Assemblée ferme et
 * rouvre le mandat à chaque remplacement, y compris pour quelques jours. C'est
 * pourquoi la commission d'un député ne se lit pas en prenant son mandat encore
 * ouvert, mais celle où il a cumulé le plus de jours — règle appliquée par
 * {@see \App\Controller\CommissionController}.
 */
#[ORM\Entity(repositoryClass: FonctionCommissionRepository::class)]
#[ORM\Table(name: 'fonction_commission')]
#[ORM\UniqueConstraint(name: 'uniq_fonction_commission_uid', columns: ['uid'])]
#[ORM\Index(name: 'idx_commission_legislature', columns: ['commission_id', 'legislature'])]
#[ORM\Index(name: 'idx_depute_legislature', columns: ['depute_id', 'legislature'])]
class FonctionCommission
{
    /** Qualité de l'immense majorité des mandats ; les autres forment le bureau. */
    public const QUALITE_MEMBRE = 'Membre';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Identifiant du mandat dans l'open data (ex. « PM323983 ») : clé naturelle
     * de l'upsert, seule à distinguer deux passages d'un même député dans une
     * même commission.
     */
    #[ORM\Column(length: 30)]
    private ?string $uid = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Depute $depute = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Commission $commission = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $legislature = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $codeQualite = null;

    /** Libellé complet : celui des rapporteurs spéciaux nomme leur mission. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelleQualite = null;

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

    public function getCommission(): ?Commission
    {
        return $this->commission;
    }

    public function setCommission(?Commission $commission): static
    {
        $this->commission = $commission;

        return $this;
    }

    public function getLegislature(): ?int
    {
        return $this->legislature;
    }

    public function setLegislature(int $legislature): static
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
