<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Participation aux municipales 2026, par commune et par tour (table
 * `elect_results_cities_ministry_infos` de l'application d'origine).
 *
 * `pctSaisis` et `pourvu` viennent du fichier d'index du département, les
 * autres colonnes du fichier de résultats : le ministère ne publie pas l'état
 * du dépouillement au même endroit que les chiffres. `codeCommune` suit la même
 * règle que {@see MunicipaleResultat::$codeCommune}.
 */
#[ORM\Entity]
#[ORM\Table(name: 'municipale_participation')]
#[ORM\UniqueConstraint(name: 'uniq_municipale_participation', columns: ['tour', 'code_commune'])]
class MunicipaleParticipation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $tour = null;

    #[ORM\Column(length: 10)]
    private ?string $codeCommune = null;

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

    /** Part des bureaux de vote dépouillés, 100 une fois le tour clos. */
    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    private ?string $pctSaisis = null;

    /** `T1`, `T2` ou `NON`, selon l'index du ministère. */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $pourvu = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
