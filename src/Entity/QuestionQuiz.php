<?php

namespace App\Entity;

use App\Repository\QuestionQuizRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Question du questionnaire (table « quizz » de l'application d'origine).
 *
 * Trente questions rédigées à la main : chacune présente une mesure soumise au
 * vote, avec sa description et trois arguments pour et trois contre, pour que le
 * visiteur se positionne puis se compare aux votes des députés. Contenu
 * éditorial, à préserver — rien de tout cela n'est dans l'open data.
 *
 * Seule la donnée est portée ici : la page `/questionnaire` viendra plus tard.
 * Le scrutin visé est repéré, comme dans la source, par le couple
 * ({@see $scrutinNumero}, {@see $legislature}) — à joindre à `scrutin` le jour
 * où la page sera faite. La catégorie est gardée par son slug
 * ({@see $categorieSlug}), stable, plutôt que par un identifiant.
 *
 * {@see $sourceId} reprend l'identifiant du legacy : c'est la clé de
 * déduplication de l'import, ces questions n'ayant pas de slug.
 */
#[ORM\Entity(repositoryClass: QuestionQuizRepository::class)]
#[ORM\Table(name: 'question_quiz')]
#[ORM\UniqueConstraint(name: 'uniq_question_quiz_source', columns: ['source_id'])]
class QuestionQuiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Identifiant d'origine (`quizz.id`), gardé comme clé stable de l'import. */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $sourceId = null;

    /** Numéro du quiz qui regroupe les questions (`quizz.quizz`) ; un seul existe. */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $numeroQuiz = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $legislature = null;

    /** Numéro du scrutin visé, à rapprocher de `scrutin` par (numero, législature). */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $scrutinNumero = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $explication = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $pour1 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $pour2 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $pour3 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contre1 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contre2 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contre3 = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $categorieSlug = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $categorieNom = null;

    /**
     * Orientation du score de la page interactive (`quizz.swap`) : dans quel sens
     * « pour la mesure » s'aligne sur l'adoption ou le rejet du scrutin. Conservé
     * pour la future page, jamais interprété ici.
     */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $inverse = null;

    /** État de publication, repris tel quel du legacy (« published », « draft »). */
    #[ORM\Column(length: 15, nullable: true)]
    private ?string $etat = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $creeLe = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $modifieLe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceId(): ?int
    {
        return $this->sourceId;
    }

    public function setSourceId(int $sourceId): static
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    public function getNumeroQuiz(): ?int
    {
        return $this->numeroQuiz;
    }

    public function setNumeroQuiz(int $numeroQuiz): static
    {
        $this->numeroQuiz = $numeroQuiz;

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

    public function getScrutinNumero(): ?int
    {
        return $this->scrutinNumero;
    }

    public function setScrutinNumero(int $scrutinNumero): static
    {
        $this->scrutinNumero = $scrutinNumero;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getExplication(): ?string
    {
        return $this->explication;
    }

    public function setExplication(?string $explication): static
    {
        $this->explication = $explication;

        return $this;
    }

    public function getPour1(): ?string
    {
        return $this->pour1;
    }

    public function setPour1(?string $pour1): static
    {
        $this->pour1 = $pour1;

        return $this;
    }

    public function getPour2(): ?string
    {
        return $this->pour2;
    }

    public function setPour2(?string $pour2): static
    {
        $this->pour2 = $pour2;

        return $this;
    }

    public function getPour3(): ?string
    {
        return $this->pour3;
    }

    public function setPour3(?string $pour3): static
    {
        $this->pour3 = $pour3;

        return $this;
    }

    public function getContre1(): ?string
    {
        return $this->contre1;
    }

    public function setContre1(?string $contre1): static
    {
        $this->contre1 = $contre1;

        return $this;
    }

    public function getContre2(): ?string
    {
        return $this->contre2;
    }

    public function setContre2(?string $contre2): static
    {
        $this->contre2 = $contre2;

        return $this;
    }

    public function getContre3(): ?string
    {
        return $this->contre3;
    }

    public function setContre3(?string $contre3): static
    {
        $this->contre3 = $contre3;

        return $this;
    }

    public function getCategorieSlug(): ?string
    {
        return $this->categorieSlug;
    }

    public function setCategorieSlug(?string $categorieSlug): static
    {
        $this->categorieSlug = $categorieSlug;

        return $this;
    }

    public function getCategorieNom(): ?string
    {
        return $this->categorieNom;
    }

    public function setCategorieNom(?string $categorieNom): static
    {
        $this->categorieNom = $categorieNom;

        return $this;
    }

    public function getInverse(): ?int
    {
        return $this->inverse;
    }

    public function setInverse(int $inverse): static
    {
        $this->inverse = $inverse;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getCreeLe(): ?\DateTimeImmutable
    {
        return $this->creeLe;
    }

    public function setCreeLe(?\DateTimeImmutable $creeLe): static
    {
        $this->creeLe = $creeLe;

        return $this;
    }

    public function getModifieLe(): ?\DateTimeImmutable
    {
        return $this->modifieLe;
    }

    public function setModifieLe(?\DateTimeImmutable $modifieLe): static
    {
        $this->modifieLe = $modifieLe;

        return $this;
    }
}
