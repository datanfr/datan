<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Candidature d'un député à une élection autre que celle qui l'a fait entrer à
 * l'Assemblée : un député sortant qui se présente aux régionales, aux
 * européennes ou se représente aux législatives.
 *
 * C'est une donnée de la rédaction, pas de l'open data. Elle est relevée à la
 * main sur les listes du ministère, d'où les colonnes `visible` et `source` :
 * une candidature n'est publiée que lorsqu'elle est vérifiée, et la référence
 * qui l'atteste est conservée.
 */
#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
#[ORM\Table(name: 'candidature')]
#[ORM\UniqueConstraint(name: 'uniq_depute_election', columns: ['depute_id', 'election_id'])]
#[ORM\Index(name: 'idx_election_visible', columns: ['election_id', 'visible'])]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Depute $depute = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Election $election = null;

    /**
     * Circonscription d'exercice, dont la nature dépend du scrutin : identifiant
     * de région aux régionales, code de département aux départementales et aux
     * législatives, code INSEE de commune aux municipales.
     *
     * Cette colonne est polymorphe dans la base d'origine et le reste ici. La
     * lire suppose de connaître le scrutin — c'est ce que fait
     * `Elections_model::get_district()`, qui branche sur `libelleAbrev`.
     */
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $district = null;

    /** « Tête de liste », « Colistier » — vide hors scrutin de liste. */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $position = null;

    /** Nuance politique du ministère, quand elle a été relevée. */
    #[ORM\Column(length: 25, nullable: true)]
    private ?string $nuance = null;

    /**
     * Le député est-il candidat ? Distinct de {@see $visible} : une candidature
     * peut être vérifiée et négative — le député sortant ne se représente pas —,
     * ce que la page affiche au même titre qu'une candidature déclarée.
     */
    #[ORM\Column(nullable: true)]
    private ?bool $candidat = null;

    /** La candidature a été vérifiée et peut être publiée. */
    #[ORM\Column]
    private bool $visible = false;

    /** Qualifié pour le second tour. Nul tant que le premier n'est pas dépouillé. */
    #[ORM\Column(nullable: true)]
    private ?bool $secondTour = null;

    /** Élu à l'issue du scrutin. Nul tant qu'il n'est pas achevé. */
    #[ORM\Column(nullable: true)]
    private ?bool $elu = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $lien = null;

    /** Référence qui atteste la candidature, telle que la rédaction l'a notée. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $source = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepute(): ?Depute
    {
        return $this->depute;
    }

    public function setDepute(Depute $depute): static
    {
        $this->depute = $depute;

        return $this;
    }

    public function getElection(): ?Election
    {
        return $this->election;
    }

    public function setElection(Election $election): static
    {
        $this->election = $election;

        return $this;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function setDistrict(?string $district): static
    {
        $this->district = $district;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getNuance(): ?string
    {
        return $this->nuance;
    }

    public function setNuance(?string $nuance): static
    {
        $this->nuance = $nuance;

        return $this;
    }

    public function estCandidat(): ?bool
    {
        return $this->candidat;
    }

    public function setCandidat(?bool $candidat): static
    {
        $this->candidat = $candidat;

        return $this;
    }

    public function estVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

        return $this;
    }

    public function estSecondTour(): ?bool
    {
        return $this->secondTour;
    }

    public function setSecondTour(?bool $secondTour): static
    {
        $this->secondTour = $secondTour;

        return $this;
    }

    public function estElu(): ?bool
    {
        return $this->elu;
    }

    public function setElu(?bool $elu): static
    {
        $this->elu = $elu;

        return $this;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(?string $lien): static
    {
        $this->lien = $lien;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): static
    {
        $this->source = $source;

        return $this;
    }
}
