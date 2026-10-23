<?php

namespace App\Entity;

use App\Repository\PartielleLegislativeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Résultat d'une élection législative **partielle** (table
 * « elect_legislatives_partielles » de l'application d'origine).
 *
 * Une partielle rejoue le scrutin d'une seule circonscription en cours de
 * législature — démission, invalidation, décès. Pour le député qui la remporte,
 * elle **remplace** le résultat de l'élection générale : le bloc « Son élection »
 * lit d'abord ici, et n'interroge {@see ResultatCirconscription} que faute de
 * partielle. C'est {@see $dateScrutin} qui rattache la partielle à une
 * législature — la même circonscription pouvant en connaître plusieurs au fil
 * des années.
 *
 * Le nom y est toujours éclaté en {@see $nom}/{@see $prenom} (jamais un champ
 * « candidat » comme la table générale d'avant 2024) ; l'import en compose
 * malgré tout un {@see $candidat} d'affichage, clé de déduplication de l'upsert.
 */
#[ORM\Entity(repositoryClass: PartielleLegislativeRepository::class)]
#[ORM\Table(name: 'partielle_legislative')]
#[ORM\UniqueConstraint(name: 'uniq_partielle', columns: ['code_departement', 'circonscription', 'tour', 'date_scrutin', 'candidat'])]
#[ORM\Index(name: 'idx_partielle_depute', columns: ['code_departement', 'circonscription', 'date_scrutin'])]
class PartielleLegislative
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

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $dateScrutin = null;

    /** Nuance du ministère : « LR », « RN », « NUP »… */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $nuance = null;

    /** Libellé d'affichage du candidat, toujours renseigné (voir docblock de classe). */
    #[ORM\Column(length: 255)]
    private ?string $candidat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $sexe = null;

    #[ORM\Column(nullable: true)]
    private ?int $voix = null;

    #[ORM\Column(nullable: true)]
    private ?float $partExprimes = null;

    /** 1 pour l'élu de la partielle, 0 pour les autres candidats. */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $elu = null;

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

    public function getDateScrutin(): ?\DateTimeImmutable
    {
        return $this->dateScrutin;
    }

    public function setDateScrutin(\DateTimeImmutable $dateScrutin): static
    {
        $this->dateScrutin = $dateScrutin;

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

    public function getVoix(): ?int
    {
        return $this->voix;
    }

    public function setVoix(?int $voix): static
    {
        $this->voix = $voix;

        return $this;
    }

    public function getPartExprimes(): ?float
    {
        return $this->partExprimes;
    }

    public function setPartExprimes(?float $partExprimes): static
    {
        $this->partExprimes = $partExprimes;

        return $this;
    }

    public function getElu(): ?int
    {
        return $this->elu;
    }

    public function setElu(int $elu): static
    {
        $this->elu = $elu;

        return $this;
    }
}
