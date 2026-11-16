<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Lot soumis à l'API de lots d'Anthropic (`anthropic_batches` du legacy).
 *
 * Un lot vit 24 heures au plus chez Anthropic et ses résultats 29 jours : la
 * ligne garde l'identifiant pour aller les chercher, et le statut dit si on
 * l'a déjà fait. `en_cours` tant que les résultats ne sont pas intégrés,
 * `termine` une fois écrits, `echoue` si le lot lui-même n'a pas abouti (refus
 * à la soumission, lot annulé, résultats illisibles).
 *
 * Les compteurs de jetons additionnent l'`usage` de chaque réponse : c'est là
 * qu'on vérifie que le cache de prompt prend réellement (`jetons_cache_lus`
 * doit dominer `jetons_cache_ecrits`), ce qu'aucune relecture du code ne
 * garantit — les hits de cache sont « au mieux » dans un lot.
 */
#[ORM\Entity]
#[ORM\Table(name: 'lot_ia')]
#[ORM\UniqueConstraint(name: 'uniq_lot_ia_identifiant', columns: ['identifiant'])]
class LotIa
{
    public const EN_COURS = 'en_cours';
    public const TERMINE = 'termine';
    public const ECHOUE = 'echoue';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Identifiant Anthropic du lot (`msgbatch_…`). */
    #[ORM\Column(length: 100)]
    private ?string $identifiant = null;

    #[ORM\Column(length: 60)]
    private ?string $modele = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::EN_COURS;

    #[ORM\Column]
    private int $nombreRequetes = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $soumisLe = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $termineLe = null;

    #[ORM\Column(nullable: true)]
    private ?int $jetonsEntree = null;

    #[ORM\Column(nullable: true)]
    private ?int $jetonsSortie = null;

    #[ORM\Column(nullable: true)]
    private ?int $jetonsCacheLus = null;

    #[ORM\Column(nullable: true)]
    private ?int $jetonsCacheEcrits = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $erreur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifiant(): ?string
    {
        return $this->identifiant;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }
}
