<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\MandatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Mandat parlementaire d'un député pour une législature donnée.
 *
 * Sert l'historique des mandats affiché sur la fiche d'un député, et fournit
 * les bornes temporelles de chaque mandat — que {@see Depute} ne porte pas,
 * puisqu'il ne décrit que la situation la plus récente.
 */
#[ORM\Entity(repositoryClass: MandatRepository::class)]
#[ORM\Table(name: 'mandat')]
#[ORM\UniqueConstraint(name: 'uniq_depute_legislature_debut', columns: ['depute_id', 'legislature', 'date_debut'])]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['mandat:read']],
)]
#[ApiFilter(SearchFilter::class, properties: ['depute' => 'exact', 'legislature' => 'exact'])]
class Mandat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['mandat:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'mandats')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['mandat:read', 'mandat:write'])]
    private ?Depute $depute = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['mandat:read', 'mandat:write', 'depute:read'])]
    private ?int $legislature = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['mandat:read', 'mandat:write', 'depute:read'])]
    private ?\DateTimeImmutable $dateDebut = null;

    /**
     * Date de prise de fonction à l'Assemblée (`mandature.datePriseFonction`),
     * postérieure à {@see $dateDebut} qui porte la date d'élection : c'est celle
     * que la fiche affiche (« entré en fonction en juillet 2024 ») et qui sert au
     * calcul de l'ancienneté.
     */
    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['mandat:read', 'mandat:write', 'depute:read'])]
    private ?\DateTimeImmutable $datePriseFonction = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['mandat:read', 'mandat:write', 'depute:read'])]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['mandat:read', 'mandat:write', 'depute:read'])]
    private ?string $departementNom = null;

    #[ORM\Column(length: 5, nullable: true)]
    #[Groups(['mandat:read', 'mandat:write', 'depute:read'])]
    private ?string $departementCode = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['mandat:read', 'mandat:write', 'depute:read'])]
    private ?int $circonscription = null;

    /** Motif d'entrée en fonction : élections générales, remplacement, élection partielle… */
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['mandat:read', 'mandat:write', 'depute:read'])]
    private ?string $causeMandat = null;

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

    public function getLegislature(): ?int
    {
        return $this->legislature;
    }

    public function setLegislature(int $legislature): static
    {
        $this->legislature = $legislature;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDatePriseFonction(): ?\DateTimeImmutable
    {
        return $this->datePriseFonction;
    }

    public function setDatePriseFonction(?\DateTimeImmutable $datePriseFonction): static
    {
        $this->datePriseFonction = $datePriseFonction;

        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getDepartementNom(): ?string
    {
        return $this->departementNom;
    }

    public function setDepartementNom(?string $departementNom): static
    {
        $this->departementNom = $departementNom;

        return $this;
    }

    public function getDepartementCode(): ?string
    {
        return $this->departementCode;
    }

    public function setDepartementCode(?string $departementCode): static
    {
        $this->departementCode = $departementCode;

        return $this;
    }

    public function getCirconscription(): ?int
    {
        return $this->circonscription;
    }

    public function setCirconscription(?int $circonscription): static
    {
        $this->circonscription = $circonscription;

        return $this;
    }

    public function getCauseMandat(): ?string
    {
        return $this->causeMandat;
    }

    public function setCauseMandat(?string $causeMandat): static
    {
        $this->causeMandat = $causeMandat;

        return $this;
    }
}
