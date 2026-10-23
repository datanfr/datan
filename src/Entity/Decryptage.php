<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Enum\DecryptageState;
use App\Repository\DecryptageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Décryptage éditorial d'un vote (table « votes_datan » de l'app d'origine).
 *
 * C'est la principale valeur ajoutée de Datan : une mise en récit accessible d'un
 * scrutin (titre, description, catégorie, lecture, état de publication) rédigée par
 * la rédaction. Les décryptages déjà produits doivent être préservés à l'identique.
 */
#[ORM\Entity(repositoryClass: DecryptageRepository::class)]
#[ORM\Table(name: 'decryptage')]
#[ORM\UniqueConstraint(name: 'uniq_legislature_vote_numero', columns: ['legislature', 'vote_numero'])]
#[ORM\Index(name: 'idx_state', columns: ['state'])]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['decryptage:read']],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'legislature' => 'exact',
    'voteNumero' => 'exact',
    'slug' => 'exact',
    'state' => 'exact',
    'title' => 'partial',
    'categorie' => 'exact',
    'categorie.slug' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'modifiedAt', 'voteNumero', 'legislature', 'title'])]
#[ApiFilter(PropertyFilter::class)]
class Decryptage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['decryptage:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['decryptage:read', 'decryptage:write', 'scrutin:read'])]
    private ?int $legislature = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['decryptage:read', 'decryptage:write', 'scrutin:read'])]
    private ?int $voteNumero = null;

    /** Identifiant du scrutin dans l'app d'origine (votes_datan.vote_id). */
    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['decryptage:read', 'decryptage:write'])]
    private ?string $voteId = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['decryptage:read', 'decryptage:write', 'scrutin:read'])]
    private ?string $title = null;

    #[ORM\Column(length: 160)]
    #[Groups(['decryptage:read', 'decryptage:write', 'scrutin:read'])]
    private ?string $slug = null;

    /**
     * Le texte du décryptage. Nullable : la rédaction crée souvent l'entrée
     * pour réserver le scrutin, puis écrit ensuite — un brouillon vide est un
     * état légitime.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['decryptage:read', 'decryptage:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 20, enumType: DecryptageState::class, options: ['default' => 'draft'])]
    #[Groups(['decryptage:read', 'decryptage:write'])]
    private DecryptageState $state = DecryptageState::Draft;

    /**
     * Identifiant de l'auteur dans l'application d'origine (votes_datan.created_by).
     *
     * Conservé tel quel : la sauvegarde de production est anonymisée, ces
     * identifiants ne désignent plus aucun compte et ne peuvent donc pas être
     * rapprochés d'un {@see Utilisateur}. C'est la seule trace d'attribution
     * qu'il reste sur les décryptages antérieurs à cette application.
     */
    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(['decryptage:read'])]
    private ?string $createdBy = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(['decryptage:read'])]
    private ?string $modifiedBy = null;

    /** Rédacteur, pour les décryptages écrits depuis cette application. */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['decryptage:read'])]
    private ?Utilisateur $auteur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['decryptage:read'])]
    private ?Utilisateur $modifiePar = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['decryptage:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['decryptage:read'])]
    private ?\DateTimeImmutable $modifiedAt = null;

    #[ORM\ManyToOne(inversedBy: 'decryptages')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['decryptage:read', 'decryptage:write'])]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'decryptages')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['decryptage:read', 'decryptage:write'])]
    private ?Lecture $lecture = null;

    /**
     * Scrutin décrypté. Nullable car un décryptage peut être importé avant que
     * le scrutin correspondant ({@see Scrutin}) ne soit chargé ; le rapprochement
     * se fait sur (legislature, voteNumero).
     */
    #[ORM\ManyToOne(inversedBy: 'decryptages')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['decryptage:read', 'decryptage:write'])]
    private ?Scrutin $scrutin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLegislature(): ?int
    {
        return $this->legislature;
    }

    public function setLegislature(int $legislature): static
    {
        $this->legislature = $legislature;

        return $this;
    }

    public function getVoteNumero(): ?int
    {
        return $this->voteNumero;
    }

    public function setVoteNumero(int $voteNumero): static
    {
        $this->voteNumero = $voteNumero;

        return $this;
    }

    public function getVoteId(): ?string
    {
        return $this->voteId;
    }

    public function setVoteId(?string $voteId): static
    {
        $this->voteId = $voteId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getState(): DecryptageState
    {
        return $this->state;
    }

    public function setState(DecryptageState $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getAuteur(): ?Utilisateur
    {
        return $this->auteur;
    }

    public function setAuteur(?Utilisateur $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getModifiePar(): ?Utilisateur
    {
        return $this->modifiePar;
    }

    public function setModifiePar(?Utilisateur $modifiePar): static
    {
        $this->modifiePar = $modifiePar;

        return $this;
    }

    /** Nom du rédacteur, ou l'identifiant hérité quand le compte n'existe plus. */
    public function getSignature(): ?string
    {
        return $this->auteur?->getNom()
            ?? ($this->createdBy !== null ? 'compte hérité #' . $this->createdBy : null);
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getModifiedBy(): ?string
    {
        return $this->modifiedBy;
    }

    public function setModifiedBy(?string $modifiedBy): static
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(?\DateTimeImmutable $modifiedAt): static
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getLecture(): ?Lecture
    {
        return $this->lecture;
    }

    public function setLecture(?Lecture $lecture): static
    {
        $this->lecture = $lecture;

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
}
