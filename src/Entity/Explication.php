<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\ExplicationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Explication de vote rédigée par un député sur un scrutin donné
 * (table « explications_mp » d'origine).
 *
 * Comme les décryptages, c'est du contenu éditorial : il ne se recalcule pas et
 * doit être préservé tel quel.
 */
#[ORM\Entity(repositoryClass: ExplicationRepository::class)]
#[ORM\Table(name: 'explication')]
#[ORM\UniqueConstraint(name: 'uniq_scrutin_depute', columns: ['scrutin_id', 'depute_id'])]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['explication:read']],
)]
#[ApiFilter(SearchFilter::class, properties: ['scrutin' => 'exact', 'depute' => 'exact'])]
class Explication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['explication:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['explication:read', 'explication:write'])]
    private ?Scrutin $scrutin = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['explication:read', 'explication:write', 'scrutin:read'])]
    private ?Depute $depute = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['explication:read', 'explication:write', 'scrutin:read'])]
    private ?string $texte = null;

    /** Seules les explications publiées (state = 1 à l'origine) sont affichées. */
    #[ORM\Column]
    #[Groups(['explication:read', 'explication:write'])]
    private bool $publiee = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['explication:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['explication:read'])]
    private ?\DateTimeImmutable $modifiedAt = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDepute(): ?Depute
    {
        return $this->depute;
    }

    public function setDepute(?Depute $depute): static
    {
        $this->depute = $depute;

        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): static
    {
        $this->texte = $texte;

        return $this;
    }

    public function isPubliee(): bool
    {
        return $this->publiee;
    }

    public function setPubliee(bool $publiee): static
    {
        $this->publiee = $publiee;

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
}
