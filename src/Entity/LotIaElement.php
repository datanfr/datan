<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Une requête d'un lot : un amendement à résumer (`anthropic_batch_items` du
 * legacy).
 *
 * Statuts : `en_attente` tant que le lot n'est pas traité — l'amendement n'est
 * alors pas resoumis ailleurs —, `traite` quand le résumé est écrit, `ecarte`
 * quand un résumé existait déjà au moment d'écrire (la rédaction ou un import
 * est passé entre-temps : on ne l'écrase pas), `echec` sinon, motif dans
 * `erreur`. Un amendement en échec est resoumis, trois fois au plus : au-delà,
 * c'est l'amendement qui pose problème (refus du modèle, exposé illisible), pas
 * le hasard.
 *
 * `reponse` garde le JSON brut du modèle, pour relire après coup ce qui a été
 * écrit et pourquoi.
 */
#[ORM\Entity]
#[ORM\Table(name: 'lot_ia_element')]
#[ORM\UniqueConstraint(name: 'uniq_lot_ia_element', columns: ['lot_id', 'custom_id'])]
class LotIaElement
{
    public const EN_ATTENTE = 'en_attente';
    public const TRAITE = 'traite';
    public const ECARTE = 'ecarte';
    public const ECHEC = 'echec';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: LotIa::class)]
    #[ORM\JoinColumn(name: 'lot_id', nullable: false, onDelete: 'CASCADE')]
    private ?LotIa $lot = null;

    #[ORM\ManyToOne(targetEntity: Amendement::class)]
    #[ORM\JoinColumn(name: 'amendement_id', nullable: false, onDelete: 'CASCADE')]
    private ?Amendement $amendement = null;

    /** `custom_id` de la requête : `^[a-zA-Z0-9_-]{1,64}$` côté Anthropic. */
    #[ORM\Column(length: 64)]
    private ?string $customId = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::EN_ATTENTE;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reponse = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $erreur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }
}
