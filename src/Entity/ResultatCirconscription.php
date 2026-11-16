<?php

namespace App\Entity;

use App\Repository\ResultatCirconscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Résultat d'un candidat aux législatives, au grain de la **circonscription**
 * (table « elect_legislatives_results » de l'application d'origine).
 *
 * À ne pas confondre avec {@see ResultatLegislative}, qui est au grain de la
 * commune et sert les pages « ville ». Celle-ci agrège la circonscription
 * entière et alimente le bloc « Son élection » de la fiche d'un député — une
 * page qui raisonne par circonscription, pas par commune.
 *
 * La source range ses noms de deux façons selon l'année : jusqu'en 2022 le nom
 * complet est dans `candidat` (« M. Xavier BRETON »), en 2024 il est éclaté en
 * {@see $nom}/{@see $prenom}. L'import unifie les deux en remplissant toujours
 * {@see $candidat} avec un libellé d'affichage, seule colonne garantie non nulle
 * — ce qui en fait la clé de déduplication de l'upsert.
 *
 * `code_departement` reprend le code « court » du ministère : « 01 » en
 * métropole, « 2a »/« 2b » pour la Corse (que les collations de MariaDB
 * rapprochent de « 2A »/« 2B » du côté député), « 099 » pour les Français de
 * l'étranger, trois chiffres en outre-mer.
 */
#[ORM\Entity(repositoryClass: ResultatCirconscriptionRepository::class)]
#[ORM\Table(name: 'resultat_circonscription')]
#[ORM\UniqueConstraint(name: 'uniq_circonscription', columns: ['annee', 'code_departement', 'circonscription', 'tour', 'candidat'])]
#[ORM\Index(name: 'idx_circonscription_depute', columns: ['code_departement', 'circonscription', 'annee'])]
class ResultatCirconscription
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
    private ?float $partInscrits = null;

    #[ORM\Column(nullable: true)]
    private ?float $partExprimes = null;

    /** 1 pour l'élu de la circonscription, 0 pour les autres candidats. */
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

    public function getPartInscrits(): ?float
    {
        return $this->partInscrits;
    }

    public function setPartInscrits(?float $partInscrits): static
    {
        $this->partInscrits = $partInscrits;

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
