<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\DeputeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Député (acteur exerçant un mandat à l'Assemblée nationale).
 */
#[ORM\Entity(repositoryClass: DeputeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['depute:read']],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'mpId' => 'exact',
    'lastname' => 'partial',
    'firstname' => 'partial',
    'groupe.libelleAbrev' => 'partial',
    'groupe.uid' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['lastname', 'firstname'])]
#[ApiFilter(PropertyFilter::class)]
class Depute
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['depute:read'])]
    private ?int $id = null;

    /** Identifiant Assemblée nationale de l'acteur (ex. « PA1001 »). */
    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?string $mpId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['depute:read', 'depute:write', 'scrutin:read'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['depute:read', 'depute:write', 'scrutin:read'])]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?string $slug = null;

    /**
     * Segment de département dans l'URL publique, sous la forme « nord-59 ».
     * Stocké plutôt que recalculé, car il sert au routage.
     */
    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['depute:read'])]
    private ?string $dptSlug = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?string $departementNom = null;

    #[ORM\Column(length: 5, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?string $departementCode = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?int $circonscription = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?string $region = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?int $placeHemicycle = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?string $profession = null;

    /** Commission permanente de rattachement (libellé abrégé). */
    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?string $commission = null;

    /**
     * Adresse électronique officielle, en `@assemblee-nationale.fr`.
     *
     * L'open data publie sous le même type d'adresse (`15`) des adresses
     * personnelles ou municipales — 22 députés n'en ont que de celles-là. Seule
     * l'adresse institutionnelle est retenue : c'est celle qu'un citoyen peut
     * légitimement écrire, et la seule qui survive à un changement de mandat.
     */
    #[ORM\Column(length: 120, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?string $mailAn = null;

    /** Civilité telle que publiée par l'Assemblée : « M. » ou « Mme ». */
    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?string $civilite = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?int $age = null;

    /**
     * État civil publié par l'Assemblée (`etatCivil.infoNaissance` des acteurs) :
     * la bio de la fiche ouvre sur « né le 25 septembre 1989 à Arras ».
     */
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['depute:read'])]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['depute:read'])]
    private ?string $villeNaissance = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['depute:read', 'depute:write'])]
    private ?string $causeFin = null;

    #[ORM\ManyToOne(inversedBy: 'deputes')]
    #[Groups(['depute:read', 'depute:write'])]
    private ?Groupe $groupe = null;

    #[ORM\ManyToOne(inversedBy: 'deputes')]
    #[Groups(['depute:read', 'depute:write'])]
    private ?Parti $parti = null;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'depute')]
    private Collection $votes;

    /**
     * @var Collection<int, Mandat>
     */
    #[ORM\OneToMany(targetEntity: Mandat::class, mappedBy: 'depute')]
    #[ORM\OrderBy(['legislature' => 'DESC'])]
    #[Groups(['depute:read'])]
    private Collection $mandats;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
        $this->mandats = new ArrayCollection();
    }

    /**
     * @return Collection<int, Mandat>
     */
    public function getMandats(): Collection
    {
        return $this->mandats;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMpId(): ?string
    {
        return $this->mpId;
    }

    public function setMpId(string $mpId): static
    {
        $this->mpId = $mpId;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDptSlug(): ?string
    {
        return $this->dptSlug;
    }

    public function setDptSlug(?string $dptSlug): static
    {
        $this->dptSlug = $dptSlug;

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

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getPlaceHemicycle(): ?int
    {
        return $this->placeHemicycle;
    }

    public function setPlaceHemicycle(?int $placeHemicycle): static
    {
        $this->placeHemicycle = $placeHemicycle;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): static
    {
        $this->profession = $profession;

        return $this;
    }

    public function getCommission(): ?string
    {
        return $this->commission;
    }

    public function setCommission(?string $commission): static
    {
        $this->commission = $commission;

        return $this;
    }

    public function getMailAn(): ?string
    {
        return $this->mailAn;
    }

    public function setMailAn(?string $mailAn): static
    {
        $this->mailAn = $mailAn;

        return $this;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(?string $civilite): static
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getVilleNaissance(): ?string
    {
        return $this->villeNaissance;
    }

    public function setVilleNaissance(?string $villeNaissance): static
    {
        $this->villeNaissance = $villeNaissance;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getCauseFin(): ?string
    {
        return $this->causeFin;
    }

    public function setCauseFin(?string $causeFin): static
    {
        $this->causeFin = $causeFin;

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

    public function getParti(): ?Parti
    {
        return $this->parti;
    }

    public function setParti(?Parti $parti): static
    {
        $this->parti = $parti;

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
            $vote->setDepute($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            if ($vote->getDepute() === $this) {
                $vote->setDepute(null);
            }
        }

        return $this;
    }
}
