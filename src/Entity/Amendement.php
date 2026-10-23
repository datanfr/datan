<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\AmendementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Amendement soumis au vote lors d'un scrutin.
 *
 * Regroupe l'amendement lui-même (exposé des motifs) et, quand il existe, le
 * résumé généré automatiquement que le site affiche sous « Résumé de l'amendement »
 * (table « amendements_ia » d'origine).
 */
#[ORM\Entity(repositoryClass: AmendementRepository::class)]
#[ORM\Table(name: 'amendement')]
// Les deux clés d'appariement d'un scrutin à son amendement : la séance de
// discussion d'abord, la date de mise aux voix en repli.
#[ORM\Index(name: 'idx_seance_numero', columns: ['seance_ref', 'numero_ordre'])]
#[ORM\Index(name: 'idx_sort_numero', columns: ['date_sort', 'numero_ordre'])]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['amendement:read']],
)]
#[ApiFilter(SearchFilter::class, properties: ['amendementId' => 'exact', 'legislature' => 'exact'])]
class Amendement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['amendement:read', 'scrutin:read'])]
    private ?int $id = null;

    /** Identifiant Assemblée nationale de l'amendement. */
    #[ORM\Column(length: 60, unique: true)]
    #[Groups(['amendement:read', 'amendement:write', 'scrutin:read'])]
    private ?string $amendementId = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['amendement:read', 'amendement:write'])]
    private ?int $legislature = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['amendement:read', 'amendement:write', 'scrutin:read'])]
    private ?string $href = null;

    /** Exposé des motifs, tel que déposé. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['amendement:read', 'amendement:write', 'scrutin:read'])]
    private ?string $expose = null;

    /** Résumé de l'exposé des motifs, affiché quand il a été relu. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['amendement:read', 'amendement:write', 'scrutin:read'])]
    private ?string $resumeIa = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['amendement:read', 'amendement:write'])]
    private ?string $titreIa = null;

    /**
     * Simplicité de compréhension du vote, de 1 (très technique) à 5 (très
     * accessible), estimée par le générateur de résumés (`amendements_ia
     * .simplicite_ia` d'origine, que PoliticAnalysis renseignait).
     */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['amendement:read', 'amendement:write'])]
    private ?int $simpliciteIa = null;

    /** Le résumé n'est affiché que s'il a été relu (amendements_ia.reviewed). */
    #[ORM\Column]
    #[Groups(['amendement:read', 'amendement:write', 'scrutin:read'])]
    private bool $resumeRelu = false;

    /**
     * Séance où l'amendement a été discuté (`seanceDiscussionRef`).
     *
     * C'est la clé qui rattache un scrutin à l'amendement qu'il met aux voix :
     * le scrutin porte la même référence de séance, et le numéro annoncé dans
     * son objet départage les amendements discutés ce jour-là. Sans ces
     * colonnes, l'appariement doit relire les 123 000 fichiers du dépôt à
     * chaque exécution.
     */
    #[ORM\Column(length: 60, nullable: true)]
    #[Groups(['amendement:read', 'amendement:write'])]
    private ?string $seanceRef = null;

    /**
     * Numéro d'ordre de dépôt, sans son rembourrage de zéros : c'est sous cette
     * forme que l'objet d'un scrutin le cite (« l'amendement n° 24 »).
     */
    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['amendement:read', 'amendement:write'])]
    private ?string $numeroOrdre = null;

    /** Date à laquelle l'amendement a été mis aux voix (`cycleDeVie.dateSort`). */
    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['amendement:read', 'amendement:write'])]
    private ?\DateTimeImmutable $dateSort = null;

    /**
     * Signataires tels que publiés (« M. Guitton, M. Odoul et les membres du
     * groupe… »). Sert de contrôle : quand l'objet du scrutin nomme un auteur
     * absent de cette liste, le rattachement est refusé plutôt que deviné.
     *
     * Stockée entière, en TEXT : 40 357 listes dépassaient les 500 caractères
     * que la colonne faisait d'abord, jusqu'à 2 629 pour un amendement cosigné
     * par tout un groupe. La troncature avantageait le premier signataire —
     * celui que l'objet d'un scrutin nomme — et ce biais est peut-être le bon,
     * mais il doit être choisi par la règle d'appariement, où il se lit et se
     * mesure, pas par une largeur de colonne.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['amendement:read', 'amendement:write'])]
    private ?string $signataires = null;

    /**
     * Type de l'auteur principal (`signataires.auteur.typeAuteur` du dépôt des
     * Tricoteuses) : « Député », « Rapporteur » ou « Gouvernement ». Sert la
     * carte « L'auteur de l'amendement » de la page de vote, avec
     * {@see $auteurRef}. Alimenté par `app:import:auteurs-amendements`.
     */
    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['amendement:read'])]
    private ?string $auteurType = null;

    /**
     * Référence de l'auteur principal : un acteur (`PA…`) pour un député ou un
     * rapporteur, un organe gouvernemental (`PO…`) pour le Gouvernement — le
     * fichier source les porte dans deux champs distincts (`acteurRef`,
     * `gouvernementRef`), exclusifs l'un de l'autre.
     */
    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['amendement:read'])]
    private ?string $auteurRef = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmendementId(): ?string
    {
        return $this->amendementId;
    }

    public function setAmendementId(string $amendementId): static
    {
        $this->amendementId = $amendementId;

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

    public function getHref(): ?string
    {
        return $this->href;
    }

    public function setHref(?string $href): static
    {
        $this->href = $href;

        return $this;
    }

    public function getExpose(): ?string
    {
        return $this->expose;
    }

    public function setExpose(?string $expose): static
    {
        $this->expose = $expose;

        return $this;
    }

    public function getResumeIa(): ?string
    {
        return $this->resumeIa;
    }

    public function setResumeIa(?string $resumeIa): static
    {
        $this->resumeIa = $resumeIa;

        return $this;
    }

    public function getTitreIa(): ?string
    {
        return $this->titreIa;
    }

    public function setTitreIa(?string $titreIa): static
    {
        $this->titreIa = $titreIa;

        return $this;
    }

    public function getSimpliciteIa(): ?int
    {
        return $this->simpliciteIa;
    }

    public function setSimpliciteIa(?int $simpliciteIa): static
    {
        $this->simpliciteIa = $simpliciteIa;

        return $this;
    }

    public function isResumeRelu(): bool
    {
        return $this->resumeRelu;
    }

    public function setResumeRelu(bool $resumeRelu): static
    {
        $this->resumeRelu = $resumeRelu;

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

    public function getNumeroOrdre(): ?string
    {
        return $this->numeroOrdre;
    }

    public function setNumeroOrdre(?string $numeroOrdre): static
    {
        $this->numeroOrdre = $numeroOrdre;

        return $this;
    }

    public function getDateSort(): ?\DateTimeImmutable
    {
        return $this->dateSort;
    }

    public function setDateSort(?\DateTimeImmutable $dateSort): static
    {
        $this->dateSort = $dateSort;

        return $this;
    }

    public function getSignataires(): ?string
    {
        return $this->signataires;
    }

    public function setSignataires(?string $signataires): static
    {
        $this->signataires = $signataires;

        return $this;
    }

    public function getAuteurType(): ?string
    {
        return $this->auteurType;
    }

    public function setAuteurType(?string $auteurType): static
    {
        $this->auteurType = $auteurType;

        return $this;
    }

    public function getAuteurRef(): ?string
    {
        return $this->auteurRef;
    }

    public function setAuteurRef(?string $auteurRef): static
    {
        $this->auteurRef = $auteurRef;

        return $this;
    }
}
