<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\VoteGroupeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Ventilation d'un scrutin pour un groupe parlementaire.
 *
 * Cette table reprend la ventilation telle qu'elle existait au moment du scrutin
 * (table « votes_groupes » de l'application d'origine). Elle ne peut pas être
 * recalculée depuis {@see Vote} : le rattachement d'un député à un groupe évolue
 * dans le temps, alors que {@see Depute::$groupe} ne porte que l'appartenance
 * courante. C'est aussi elle qui fournit l'effectif du groupe à la date du vote,
 * indispensable au taux de participation affiché.
 */
#[ORM\Entity(repositoryClass: VoteGroupeRepository::class)]
#[ORM\Table(name: 'vote_groupe')]
#[ORM\UniqueConstraint(name: 'uniq_scrutin_groupe', columns: ['scrutin_id', 'groupe_id'])]
// Index couvrant : les moyennes de cohésion et de participation d'un groupe
// balaient toutes ses ventilations. En y plaçant les décomptes, l'agrégat se
// calcule dans l'index sans lire les lignes (9,4 ms → 4,3 ms sur LFI-NFP).
#[ORM\Index(name: 'idx_groupe_decompte', columns: ['groupe_id', 'nombre_pours', 'nombre_contres', 'nombre_abstentions', 'nombre_membres_groupe'])]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['vote_groupe:read']],
)]
#[ApiFilter(SearchFilter::class, properties: ['scrutin' => 'exact', 'groupe' => 'exact'])]
class VoteGroupe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vote_groupe:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vote_groupe:read', 'vote_groupe:write'])]
    private ?Scrutin $scrutin = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vote_groupe:read', 'vote_groupe:write', 'scrutin:read'])]
    private ?Groupe $groupe = null;

    /** Effectif du groupe à la date du scrutin (dénominateur du taux de participation). */
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['vote_groupe:read', 'vote_groupe:write', 'scrutin:read'])]
    private int $nombreMembresGroupe = 0;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['vote_groupe:read', 'vote_groupe:write', 'scrutin:read'])]
    private ?string $positionMajoritaire = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['vote_groupe:read', 'vote_groupe:write', 'scrutin:read'])]
    private int $nombrePours = 0;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['vote_groupe:read', 'vote_groupe:write', 'scrutin:read'])]
    private int $nombreContres = 0;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['vote_groupe:read', 'vote_groupe:write', 'scrutin:read'])]
    private int $nombreAbstentions = 0;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['vote_groupe:read', 'vote_groupe:write', 'scrutin:read'])]
    private int $nonVotants = 0;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['vote_groupe:read', 'vote_groupe:write'])]
    private int $nonVotantsVolontaires = 0;

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

    public function getGroupe(): ?Groupe
    {
        return $this->groupe;
    }

    public function setGroupe(?Groupe $groupe): static
    {
        $this->groupe = $groupe;

        return $this;
    }

    public function getNombreMembresGroupe(): int
    {
        return $this->nombreMembresGroupe;
    }

    public function setNombreMembresGroupe(int $nombreMembresGroupe): static
    {
        $this->nombreMembresGroupe = $nombreMembresGroupe;

        return $this;
    }

    public function getPositionMajoritaire(): ?string
    {
        return $this->positionMajoritaire;
    }

    public function setPositionMajoritaire(?string $positionMajoritaire): static
    {
        $this->positionMajoritaire = $positionMajoritaire;

        return $this;
    }

    public function getNombrePours(): int
    {
        return $this->nombrePours;
    }

    public function setNombrePours(int $nombrePours): static
    {
        $this->nombrePours = $nombrePours;

        return $this;
    }

    public function getNombreContres(): int
    {
        return $this->nombreContres;
    }

    public function setNombreContres(int $nombreContres): static
    {
        $this->nombreContres = $nombreContres;

        return $this;
    }

    public function getNombreAbstentions(): int
    {
        return $this->nombreAbstentions;
    }

    public function setNombreAbstentions(int $nombreAbstentions): static
    {
        $this->nombreAbstentions = $nombreAbstentions;

        return $this;
    }

    public function getNonVotants(): int
    {
        return $this->nonVotants;
    }

    public function setNonVotants(int $nonVotants): static
    {
        $this->nonVotants = $nonVotants;

        return $this;
    }

    public function getNonVotantsVolontaires(): int
    {
        return $this->nonVotantsVolontaires;
    }

    public function setNonVotantsVolontaires(int $nonVotantsVolontaires): static
    {
        $this->nonVotantsVolontaires = $nonVotantsVolontaires;

        return $this;
    }

    /**
     * Indice d'accord (cohésion) du groupe sur ce scrutin, repris à l'identique
     * de l'application d'origine : (max − 0,5 × (exprimés − max)) / exprimés.
     */
    #[Groups(['vote_groupe:read', 'scrutin:read'])]
    public function getCohesion(): float
    {
        $exprimes = $this->nombrePours + $this->nombreContres + $this->nombreAbstentions;
        if ($exprimes === 0) {
            return 0.0;
        }

        $max = max($this->nombrePours, $this->nombreContres, $this->nombreAbstentions);

        return round(($max - 0.5 * ($exprimes - $max)) / $exprimes, 3);
    }

    /** Part des membres du groupe ayant exprimé un vote. */
    #[Groups(['vote_groupe:read', 'scrutin:read'])]
    public function getPourcentageVotants(): int
    {
        if ($this->nombreMembresGroupe === 0) {
            return 0;
        }

        $exprimes = $this->nombrePours + $this->nombreContres + $this->nombreAbstentions;

        return (int) round($exprimes / $this->nombreMembresGroupe * 100);
    }
}
