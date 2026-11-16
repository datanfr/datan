<?php

namespace App\Entity;

use App\Repository\ParticipationCirconscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Participation à un tour de législatives, par circonscription (table
 * « elect_legislatives_infos » de l'application d'origine).
 *
 * Le bloc « Son élection » de la fiche d'un député en tire la phrase « la
 * participation a atteint X % dans cette circonscription ». Elle n'existe que
 * pour l'élection générale : une élection partielle ({@see PartielleLegislative})
 * n'a pas de ligne ici, et le legacy n'affiche alors aucun taux.
 *
 * La source ne renseigne le premier tour intégralement que pour 2024 ; 2017 et
 * 2022 n'y portent guère que le second. Ce n'est pas un défaut d'import.
 */
#[ORM\Entity(repositoryClass: ParticipationCirconscriptionRepository::class)]
#[ORM\Table(name: 'participation_circonscription')]
#[ORM\UniqueConstraint(name: 'uniq_participation_circonscription', columns: ['annee', 'code_departement', 'circonscription', 'tour'])]
class ParticipationCirconscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $annee = null;

    #[ORM\Column(length: 3)]
    private ?string $codeDepartement = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $circonscription = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $tour = null;

    #[ORM\Column(nullable: true)]
    private ?int $inscrits = null;

    #[ORM\Column(nullable: true)]
    private ?int $abstentions = null;

    #[ORM\Column(nullable: true)]
    private ?int $votants = null;

    #[ORM\Column(nullable: true)]
    private ?int $blancs = null;

    #[ORM\Column(nullable: true)]
    private ?int $nuls = null;

    #[ORM\Column(nullable: true)]
    private ?int $exprimes = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCodeDepartement(): ?string
    {
        return $this->codeDepartement;
    }

    public function setCodeDepartement(string $codeDepartement): static
    {
        $this->codeDepartement = $codeDepartement;

        return $this;
    }

    public function getCirconscription(): ?int
    {
        return $this->circonscription;
    }

    public function setCirconscription(int $circonscription): static
    {
        $this->circonscription = $circonscription;

        return $this;
    }

    public function getTour(): ?int
    {
        return $this->tour;
    }

    public function setTour(int $tour): static
    {
        $this->tour = $tour;

        return $this;
    }

    public function getInscrits(): ?int
    {
        return $this->inscrits;
    }

    public function setInscrits(?int $inscrits): static
    {
        $this->inscrits = $inscrits;

        return $this;
    }

    public function getAbstentions(): ?int
    {
        return $this->abstentions;
    }

    public function setAbstentions(?int $abstentions): static
    {
        $this->abstentions = $abstentions;

        return $this;
    }

    public function getVotants(): ?int
    {
        return $this->votants;
    }

    public function setVotants(?int $votants): static
    {
        $this->votants = $votants;

        return $this;
    }

    public function getBlancs(): ?int
    {
        return $this->blancs;
    }

    public function setBlancs(?int $blancs): static
    {
        $this->blancs = $blancs;

        return $this;
    }

    public function getNuls(): ?int
    {
        return $this->nuls;
    }

    public function setNuls(?int $nuls): static
    {
        $this->nuls = $nuls;

        return $this;
    }

    public function getExprimes(): ?int
    {
        return $this->exprimes;
    }

    public function setExprimes(?int $exprimes): static
    {
        $this->exprimes = $exprimes;

        return $this;
    }
}
