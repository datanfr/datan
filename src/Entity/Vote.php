<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\Trait\TimestampableTrait;
use App\Enum\VotePosition;
use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Position individuelle d'un député lors d'un scrutin (décompte nominatif).
 */
#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uniq_depute_scrutin_type', columns: ['depute_id', 'scrutin_id', 'vote_type'])]
// Index couvrant : les statistiques de participation d'un député (nombre de votes,
// de non-votes…) se lisent alors entièrement dans l'index, sans accès aux lignes.
#[ORM\Index(name: 'idx_depute_type_position', columns: ['depute_id', 'vote_type', 'position'])]
// Ventilation d'un scrutin : filtre sur le type puis regroupement par position.
#[ORM\Index(name: 'idx_scrutin_type_position', columns: ['scrutin_id', 'vote_type', 'position'])]
// Tri chronologique des votes d'un député directement dans l'index (voir $scrutinDate).
#[ORM\Index(name: 'idx_depute_date', columns: ['depute_id', 'scrutin_date'])]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['vote:read']],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'depute' => 'exact',
    'scrutin' => 'exact',
    'position' => 'exact',
])]
class Vote
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vote:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vote:read', 'vote:write', 'scrutin:read'])]
    private ?Depute $depute = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vote:read', 'vote:write', 'depute:read'])]
    private ?Scrutin $scrutin = null;

    /**
     * Position exprimée. Nullable : les « mises au point » déposées après coup
     * peuvent ne porter aucune position dans les données d'origine.
     */
    #[ORM\Column(length: 20, enumType: VotePosition::class, nullable: true)]
    #[Groups(['vote:read', 'vote:write', 'depute:read', 'scrutin:read'])]
    private ?VotePosition $position = null;

    /**
     * Nature de la ligne : « decompteNominatif » (vote officiel),
     * « miseAuPoint » (rectification demandée par le député après le scrutin,
     * sans effet sur le résultat) ou « dysfonctionnement ».
     */
    #[ORM\Column(length: 32, options: ['default' => 'decompteNominatif'])]
    #[Groups(['vote:read', 'vote:write', 'depute:read', 'scrutin:read'])]
    private string $voteType = 'decompteNominatif';

    /**
     * Motif d'absence de vote : MG (membre du Gouvernement),
     * PAN (président de l'Assemblée), PSE (président de séance).
     */
    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['vote:read', 'vote:write', 'depute:read'])]
    private ?string $causePosition = null;

    /** Vote exprimé par délégation (procuration). */
    #[ORM\Column]
    #[Groups(['vote:read', 'vote:write'])]
    private bool $parDelegation = false;

    /**
     * Date du scrutin, recopiée depuis {@see Scrutin::$dateScrutin}.
     *
     * Dénormalisation assumée : lister les votes d'un député par ordre
     * chronologique est l'une des requêtes les plus fréquentes du site, et sans
     * cette colonne le tri impose de charger puis trier tous ses votes (plusieurs
     * milliers). Avec l'index (depute_id, scrutin_date), la base lit directement
     * les N dernières lignes. La date d'un scrutin est un fait historique figé :
     * il n'y a pas de risque de désynchronisation.
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['vote:read'])]
    private ?\DateTimeImmutable $scrutinDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepute(): ?Depute
    {
        return $this->depute;
    }

    public function setDepute(?Depute $depute): static
    {
        $this->depute = $depute;

        return $this;
    }

    public function getScrutin(): ?Scrutin
    {
        return $this->scrutin;
    }

    public function setScrutin(?Scrutin $scrutin): static
    {
        $this->scrutin = $scrutin;

        return $this;
    }

    public function getPosition(): ?VotePosition
    {
        return $this->position;
    }

    public function setPosition(?VotePosition $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getVoteType(): string
    {
        return $this->voteType;
    }

    public function setVoteType(string $voteType): static
    {
        $this->voteType = $voteType;

        return $this;
    }

    public function getCausePosition(): ?string
    {
        return $this->causePosition;
    }

    public function setCausePosition(?string $causePosition): static
    {
        $this->causePosition = $causePosition;

        return $this;
    }

    public function getScrutinDate(): ?\DateTimeImmutable
    {
        return $this->scrutinDate;
    }

    public function setScrutinDate(?\DateTimeImmutable $scrutinDate): static
    {
        $this->scrutinDate = $scrutinDate;

        return $this;
    }

    public function isParDelegation(): bool
    {
        return $this->parDelegation;
    }

    public function setParDelegation(bool $parDelegation): static
    {
        $this->parDelegation = $parDelegation;

        return $this;
    }
}
