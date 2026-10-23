<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\ScrutinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Scrutin public à l'Assemblée nationale (vote sur un texte, un amendement, une motion…).
 * Modèle aligné sur les scrutins open-data de l'Assemblée nationale.
 */
#[ORM\Entity(repositoryClass: ScrutinRepository::class)]
#[ORM\HasLifecycleCallbacks]
// Les listes de scrutins et de décryptages sont presque toujours triées par date.
#[ORM\Index(name: 'idx_date_scrutin', columns: ['date_scrutin'])]
#[ORM\Index(name: 'idx_legislature_numero', columns: ['legislature', 'numero'])]
#[ORM\Index(name: 'idx_nature_vote', columns: ['nature_vote'])]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['scrutin:read']],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'uid' => 'exact',
    'legislature' => 'exact',
    'sortCode' => 'exact',
    'titre' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: ['dateScrutin', 'numero', 'legislature'])]
#[ApiFilter(PropertyFilter::class)]
class Scrutin
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['scrutin:read'])]
    private ?int $id = null;

    /** Identifiant Assemblée nationale du scrutin (ex. « VTANR5L17V123 »). */
    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'vote:read', 'decryptage:read'])]
    private ?string $uid = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'decryptage:read'])]
    private ?int $numero = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'decryptage:read'])]
    private ?int $legislature = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'vote:read', 'decryptage:read'])]
    private ?\DateTimeImmutable $dateScrutin = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'vote:read', 'decryptage:read'])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write'])]
    private ?string $objet = null;

    /** Code du sort du scrutin : « adopté », « rejeté »… */
    #[ORM\Column(length: 64, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'vote:read', 'decryptage:read'])]
    private ?string $sortCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'decryptage:read'])]
    private ?string $sortLibelle = null;

    /** Type de vote : « Scrutin public ordinaire », « Scrutin solennel »… */
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write'])]
    private ?string $typeVote = null;

    #[ORM\Column(length: 32, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write'])]
    private ?string $codeTypeVote = null;

    /**
     * Nature de l'objet soumis au vote (« final », « amendement », « article »…),
     * déduite du libellé du scrutin par {@see \App\NatureVote}. Elle est stockée
     * plutôt que recalculée à la volée pour que les statistiques puissent la
     * filtrer par index.
     */
    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write'])]
    private ?string $natureVote = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write'])]
    private ?string $demandeur = null;

    /**
     * Référence de la séance où le vote a eu lieu (« RUANR5L17S2026IDS30819 »).
     * C'est la clé qui relie le scrutin à son compte rendu de séance
     * ({@see CompteRendu}) — donc aux débats qui l'ont précédé.
     */
    #[ORM\Column(length: 60, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write'])]
    private ?string $seanceRef = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'decryptage:read'])]
    private ?int $nombreVotants = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'decryptage:read'])]
    private ?int $suffragesExprimes = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'decryptage:read'])]
    private ?int $nombrePour = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'decryptage:read'])]
    private ?int $nombreContre = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'decryptage:read'])]
    private ?int $nombreAbstentions = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['scrutin:read', 'scrutin:write', 'decryptage:read'])]
    private ?int $nombreNonVotants = null;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'scrutin')]
    private Collection $votes;

    /**
     * @var Collection<int, Decryptage>
     */
    #[ORM\OneToMany(targetEntity: Decryptage::class, mappedBy: 'scrutin')]
    #[Groups(['scrutin:read'])]
    private Collection $decryptages;

    #[ORM\ManyToOne(inversedBy: 'scrutins')]
    #[Groups(['scrutin:read', 'scrutin:write'])]
    private ?Dossier $dossier = null;

    #[ORM\ManyToOne]
    #[Groups(['scrutin:read', 'scrutin:write'])]
    private ?Amendement $amendement = null;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
        $this->decryptages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getLegislature(): ?int
    {
        return $this->legislature;
    }

    public function setLegislature(?int $legislature): static
    {
        $this->legislature = $legislature;

        return $this;
    }

    public function getDateScrutin(): ?\DateTimeImmutable
    {
        return $this->dateScrutin;
    }

    public function setDateScrutin(?\DateTimeImmutable $dateScrutin): static
    {
        $this->dateScrutin = $dateScrutin;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(?string $objet): static
    {
        $this->objet = $objet;

        return $this;
    }

    public function getSortCode(): ?string
    {
        return $this->sortCode;
    }

    public function setSortCode(?string $sortCode): static
    {
        $this->sortCode = $sortCode;

        return $this;
    }

    public function getSortLibelle(): ?string
    {
        return $this->sortLibelle;
    }

    public function setSortLibelle(?string $sortLibelle): static
    {
        $this->sortLibelle = $sortLibelle;

        return $this;
    }

    public function getTypeVote(): ?string
    {
        return $this->typeVote;
    }

    public function setTypeVote(?string $typeVote): static
    {
        $this->typeVote = $typeVote;

        return $this;
    }

    public function getCodeTypeVote(): ?string
    {
        return $this->codeTypeVote;
    }

    public function setCodeTypeVote(?string $codeTypeVote): static
    {
        $this->codeTypeVote = $codeTypeVote;

        return $this;
    }

    public function getNatureVote(): ?string
    {
        return $this->natureVote;
    }

    public function setNatureVote(?string $natureVote): static
    {
        $this->natureVote = $natureVote;

        return $this;
    }

    public function getDemandeur(): ?string
    {
        return $this->demandeur;
    }

    public function setDemandeur(?string $demandeur): static
    {
        $this->demandeur = $demandeur;

        return $this;
    }

    public function getSeanceRef(): ?string
    {
        return $this->seanceRef;
    }

    public function setSeanceRef(?string $seanceRef): static
    {
        $this->seanceRef = $seanceRef;

        return $this;
    }

    public function getNombreVotants(): ?int
    {
        return $this->nombreVotants;
    }

    public function setNombreVotants(?int $nombreVotants): static
    {
        $this->nombreVotants = $nombreVotants;

        return $this;
    }

    public function getSuffragesExprimes(): ?int
    {
        return $this->suffragesExprimes;
    }

    public function setSuffragesExprimes(?int $suffragesExprimes): static
    {
        $this->suffragesExprimes = $suffragesExprimes;

        return $this;
    }

    public function getNombrePour(): ?int
    {
        return $this->nombrePour;
    }

    public function setNombrePour(?int $nombrePour): static
    {
        $this->nombrePour = $nombrePour;

        return $this;
    }

    public function getNombreContre(): ?int
    {
        return $this->nombreContre;
    }

    public function setNombreContre(?int $nombreContre): static
    {
        $this->nombreContre = $nombreContre;

        return $this;
    }

    public function getNombreAbstentions(): ?int
    {
        return $this->nombreAbstentions;
    }

    public function setNombreAbstentions(?int $nombreAbstentions): static
    {
        $this->nombreAbstentions = $nombreAbstentions;

        return $this;
    }

    public function getNombreNonVotants(): ?int
    {
        return $this->nombreNonVotants;
    }

    public function setNombreNonVotants(?int $nombreNonVotants): static
    {
        $this->nombreNonVotants = $nombreNonVotants;

        return $this;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setScrutin($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            if ($vote->getScrutin() === $this) {
                $vote->setScrutin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Decryptage>
     */
    public function getDecryptages(): Collection
    {
        return $this->decryptages;
    }

    public function addDecryptage(Decryptage $decryptage): static
    {
        if (!$this->decryptages->contains($decryptage)) {
            $this->decryptages->add($decryptage);
            $decryptage->setScrutin($this);
        }

        return $this;
    }

    public function removeDecryptage(Decryptage $decryptage): static
    {
        if ($this->decryptages->removeElement($decryptage)) {
            if ($decryptage->getScrutin() === $this) {
                $decryptage->setScrutin(null);
            }
        }

        return $this;
    }

    public function getDossier(): ?Dossier
    {
        return $this->dossier;
    }

    public function setDossier(?Dossier $dossier): static
    {
        $this->dossier = $dossier;

        return $this;
    }

    public function getAmendement(): ?Amendement
    {
        return $this->amendement;
    }

    public function setAmendement(?Amendement $amendement): static
    {
        $this->amendement = $amendement;

        return $this;
    }
}
