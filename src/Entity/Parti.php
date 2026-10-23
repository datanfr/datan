<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\PartiRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Parti politique (organe de type « PARPOL » dans le modèle de l'Assemblée nationale).
 * Distinct du groupe parlementaire ({@see Groupe}).
 */
#[ORM\Entity(repositoryClass: PartiRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['parti:read']],
)]
#[ApiFilter(SearchFilter::class, properties: ['uid' => 'exact', 'libelleAbrev' => 'partial'])]
class Parti
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['parti:read', 'depute:read'])]
    private ?int $id = null;

    /** Identifiant Assemblée nationale de l'organe (ex. « PO730964 »). */
    #[ORM\Column(length: 255, unique: true, nullable: true)]
    #[Groups(['parti:read', 'parti:write', 'depute:read'])]
    private ?string $uid = null;

    #[ORM\Column(length: 255)]
    #[Groups(['parti:read', 'parti:write', 'depute:read'])]
    private ?string $libelle = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['parti:read', 'parti:write', 'depute:read'])]
    private ?string $libelleAbrev = null;

    #[ORM\Column(length: 32, nullable: true)]
    #[Groups(['parti:read', 'parti:write', 'depute:read'])]
    private ?string $couleur = null;

    /**
     * Date de dissolution de l'organe, quand l'Assemblée en publie une.
     * Elle seule distingue un ancien parti d'un parti en activité sans député
     * rattaché : les deux ont un effectif nul.
     */
    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['parti:read', 'parti:write'])]
    private ?\DateTimeImmutable $dateFin = null;

    /**
     * @var Collection<int, Depute>
     */
    #[ORM\OneToMany(targetEntity: Depute::class, mappedBy: 'parti')]
    private Collection $deputes;

    public function __construct()
    {
        $this->deputes = new ArrayCollection();
    }

    #[Groups(['parti:read'])]
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

    public function setUid(?string $uid): static
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

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): static
    {
        $this->couleur = $couleur;

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
            $depute->setParti($this);
        }

        return $this;
    }

    public function removeDepute(Depute $depute): static
    {
        if ($this->deputes->removeElement($depute)) {
            if ($depute->getParti() === $this) {
                $depute->setParti(null);
            }
        }

        return $this;
    }
}
