<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Journal des récupérations depuis la base du legacy (`app:recuperation:legacy`).
 *
 * Une ligne par exécution, avec son bilan chiffré : c'est la trace de ce qui a
 * été réaligné sur la production, et quand.
 *
 * Une ligne `bascule` clôt le journal. Passé la bascule, l'application est
 * devenue la source — un rédacteur publie, un député écrit, un lecteur
 * s'inscrit ou se désabonne — et rejouer la récupération écraserait ces
 * écritures avec l'état figé du legacy. La commande refuse alors de tourner.
 * Le garde-fou vit en base et non dans un fichier : un fichier resterait dans
 * le répertoire d'une version, et le déploiement suivant l'oublierait.
 */
#[ORM\Entity]
#[ORM\Table(name: 'recuperation_legacy')]
class RecuperationLegacy
{
    public const EN_COURS = 'en_cours';
    public const REUSSIE = 'reussie';
    public const ECHEC = 'echec';
    public const BASCULE = 'bascule';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::EN_COURS;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $lanceeLe;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $termineeLe = null;

    /** Dossier des exports TSV lus. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dossier = null;

    /**
     * Bilan par étape : lignes lues, écrites, écartées et pourquoi.
     *
     * @var list<array<string, mixed>>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $bilan = null;

    public function __construct()
    {
        $this->lanceeLe = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getLanceeLe(): \DateTimeImmutable
    {
        return $this->lanceeLe;
    }

    public function getTermineeLe(): ?\DateTimeImmutable
    {
        return $this->termineeLe;
    }

    public function getDossier(): ?string
    {
        return $this->dossier;
    }

    /** @return list<array<string, mixed>>|null */
    public function getBilan(): ?array
    {
        return $this->bilan;
    }
}
