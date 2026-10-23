<?php

namespace App\Entity;

use App\Repository\ResultatElectoralRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Résultat d'un candidat aux élections législatives, dans une commune et pour
 * une circonscription donnée.
 *
 * La participation — inscrits, votants, blancs, nuls, exprimés — se répète sur
 * chaque ligne de candidat, comme dans la source. Ce n'est pas un oubli de
 * normalisation : toute lecture de la page ville veut les résultats et la
 * participation ensemble, et une requête par commune vaut mieux qu'une jointure
 * sur 431 000 lignes.
 *
 * Une commune peut relever de plusieurs circonscriptions — Paris, Lyon,
 * Marseille et toutes les villes assez peuplées pour être découpées. La
 * circonscription fait donc partie de la clé, et la page présente un bloc par
 * circonscription.
 *
 * Les circonscriptions des Français établis hors de France y figurent aussi :
 * le référentiel des communes leur donne un code à six caractères — `099069`
 * pour Dubaï —, d'où une colonne plus large que les cinq caractères d'un code
 * INSEE de métropole.
 */
#[ORM\Entity(repositoryClass: ResultatElectoralRepository::class)]
#[ORM\Table(name: 'resultat_legislative')]
#[ORM\UniqueConstraint(name: 'uniq_legislative', columns: ['annee', 'tour', 'code_insee', 'circonscription', 'candidat'])]
#[ORM\Index(name: 'idx_legislative_commune', columns: ['code_insee', 'annee', 'tour'])]
class ResultatLegislative
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $annee = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $tour = null;

    #[ORM\Column(length: 6)]
    private ?string $codeInsee = null;

    #[ORM\Column(length: 3)]
    private ?string $codeDepartement = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $circonscription = null;

    /** Numéro de panneau du candidat (« c_2 »), qui l'identifie dans son tour. */
    #[ORM\Column(length: 5)]
    private ?string $candidat = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $sexe = null;

    /** Nuance du ministère : « LR », « RN », « NUP »… */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $nuance = null;

    #[ORM\Column(nullable: true)]
    private ?int $voix = null;

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

    public function getTour(): ?int
    {
        return $this->tour;
    }

    public function setTour(int $tour): static
    {
        $this->tour = $tour;

        return $this;
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

    public function getCandidat(): ?string
    {
        return $this->candidat;
    }

    public function setCandidat(string $candidat): static
    {
        $this->candidat = $candidat;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): static
    {
        $this->sexe = $sexe;

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

    public function getVoix(): ?int
    {
        return $this->voix;
    }

    public function setVoix(?int $voix): static
    {
        $this->voix = $voix;

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
