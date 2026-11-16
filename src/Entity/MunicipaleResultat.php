<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Score d'une liste aux municipales 2026, dans une commune et pour un tour
 * (table `elect_results_cities_ministry` de l'application d'origine).
 *
 * L'entité ne sert qu'au schéma : `app:import:municipales` écrit par DBAL, et la
 * page de résultats lit par DBAL.
 *
 * **`codeCommune` est le code de circonscription électorale du ministère**
 * (`CodCirElec`), qui vaut le code INSEE de la commune — Corse comprise, en
 * capitales (`2A004`). Paris, Lyon et Marseille n'y ont qu'une ligne par liste :
 * depuis la loi du 11 août 2025, leur conseil municipal s'élit sur un scrutin à
 * l'échelle de la ville, publié sous le code de la commune (`13055`), à côté
 * des scrutins de secteur que le site n'affiche pas.
 *
 * Les trois colonnes de **situation** ne valent qu'au premier tour d'une
 * commune allée au second : ce que la liste est devenue entre les deux tours
 * (maintien, fusion, désistement). L'application d'origine la déduit à chaque
 * affichage en croisant les listes des deux tours ; l'import la calcule une
 * fois pour toutes, la règle restant la sienne
 * ({@see \App\Election\SituationListes}).
 */
#[ORM\Entity]
#[ORM\Table(name: 'municipale_resultat')]
#[ORM\UniqueConstraint(name: 'uniq_municipale_resultat', columns: ['tour', 'code_commune', 'numero_panneau'])]
#[ORM\Index(name: 'idx_municipale_resultat_commune', columns: ['code_commune', 'tour'])]
class MunicipaleResultat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $tour = null;

    #[ORM\Column(length: 10)]
    private ?string $codeCommune = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $numeroPanneau = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelleListe = null;

    #[ORM\Column(nullable: true)]
    private ?int $voix = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    private ?string $rapportInscrits = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    private ?string $rapportExprimes = null;

    /** Code de nuance du ministère : « LUG », « LRN »… */
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $codeNuance = null;

    /** Sexe de la tête de liste. */
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $sexe = null;

    /** Nom de la tête de liste, en capitales comme le publie le ministère. */
    #[ORM\Column(length: 120, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $prenom = null;

    /** Sièges au conseil municipal ; renseigné à l'issue du tour décisif. */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $sieges = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $qualifiee = null;

    /** `maintien`, `fusion` ou `desistement` ; premier tour seulement. */
    #[ORM\Column(length: 12, nullable: true)]
    private ?string $situation = null;

    /** `donneuse` (la liste a rejoint une autre) ou `receveuse`. */
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $fusionRole = null;

    /** Tête de la liste partenaire de la fusion. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fusionTete = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
