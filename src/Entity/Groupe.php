<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\GroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Groupe parlementaire (organe de type « GP » dans le modèle de l'Assemblée nationale).
 */
#[ORM\Entity(repositoryClass: GroupeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['groupe:read']],
)]
#[ApiFilter(SearchFilter::class, properties: ['uid' => 'exact', 'libelleAbrev' => 'partial', 'legislature' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['libelle', 'legislature'])]
class Groupe
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['groupe:read', 'depute:read'])]
    private ?int $id = null;

    /** Identifiant Assemblée nationale de l'organe (ex. « PO845401 »). */
    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['groupe:read', 'groupe:write', 'depute:read'])]
    private ?string $uid = null;

    #[ORM\Column(length: 255)]
    #[Groups(['groupe:read', 'groupe:write', 'depute:read', 'scrutin:read'])]
    private ?string $libelle = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['groupe:read', 'groupe:write', 'depute:read', 'scrutin:read'])]
    private ?string $libelleAbrev = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['groupe:read', 'groupe:write'])]
    private ?string $libelleAbrege = null;

    /** Couleur associée au groupe (couleurAssociee). */
    #[ORM\Column(length: 32, nullable: true)]
    #[Groups(['groupe:read', 'groupe:write', 'depute:read'])]
    private ?string $couleur = null;

    /** Position politique : « Majoritaire », « Opposition », « Minoritaire »… */
    #[ORM\Column(length: 64, nullable: true)]
    #[Groups(['groupe:read', 'groupe:write', 'depute:read'])]
    private ?string $positionPolitique = null;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Groups(['groupe:read', 'groupe:write'])]
    private ?int $legislature = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['groupe:read', 'groupe:write'])]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['groupe:read', 'groupe:write'])]
    private ?\DateTimeImmutable $dateFin = null;

    /**
     * @var Collection<int, Depute>
     */
    #[ORM\OneToMany(targetEntity: Depute::class, mappedBy: 'groupe')]
    private Collection $deputes;

    public function __construct()
    {
        $this->deputes = new ArrayCollection();
    }

    #[Groups(['groupe:read'])]
    public function getEffectif(): int
    {
        return $this->deputes->count();
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

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getLibelleAbrev(): ?string
    {
        return $this->libelleAbrev;
    }

    public function setLibelleAbrev(?string $libelleAbrev): static
    {
        $this->libelleAbrev = $libelleAbrev;

        return $this;
    }

    public function getLibelleAbrege(): ?string
    {
        return $this->libelleAbrege;
    }

    public function setLibelleAbrege(?string $libelleAbrege): static
    {
        $this->libelleAbrege = $libelleAbrege;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): static
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getPositionPolitique(): ?string
    {
        return $this->positionPolitique;
    }

    public function setPositionPolitique(?string $positionPolitique): static
    {
        $this->positionPolitique = $positionPolitique;

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

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

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

    /**
     * @return Collection<int, Depute>
     */
    public function getDeputes(): Collection
    {
        return $this->deputes;
    }

    public function addDepute(Depute $depute): static
    {
        if (!$this->deputes->contains($depute)) {
            $this->deputes->add($depute);
            $depute->setGroupe($this);
        }

        return $this;
    }

    public function removeDepute(Depute $depute): static
    {
        if ($this->deputes->removeElement($depute)) {
            if ($depute->getGroupe() === $this) {
                $depute->setGroupe(null);
            }
        }

        return $this;
    }
}
