<?php

namespace App\Entity;

use App\Enum\TypeClassement;
use App\Repository\ClassementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Une ligne de classement précalculée : la place d'un député ou d'un groupe
 * dans l'un des palmarès du site, pour une législature donnée.
 *
 * Ces valeurs ne sont jamais calculées à l'affichage. Établir la participation
 * et la loyauté des 577 députés suppose de parcourir le million de lignes de
 * `vote` ; l'application d'origine s'en gardait déjà en alimentant chaque nuit
 * des tables dédiées (`class_participation_solennels`, `class_loyaute`,
 * `class_groups`, `groupes_stats`) depuis `scripts/daily.php`. La commande
 * {@see \App\Command\CalculClassementsCommand} tient ici ce rôle, et les pages
 * de classement se réduisent à une lecture triée de cette table.
 *
 * `depute` et `groupe` s'excluent : chaque classement porte sur les uns ou sur
 * les autres, selon {@see TypeClassement::porteSurUnGroupe()}.
 */
#[ORM\Entity(repositoryClass: ClassementRepository::class)]
#[ORM\Table(name: 'classement')]
#[ORM\Index(name: 'idx_type_legislature_rang', columns: ['type', 'legislature', 'rang'])]
class Classement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, enumType: TypeClassement::class)]
    private ?TypeClassement $type = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $legislature = null;

    /**
     * Rang au sens de `RANK()` : deux scores égaux partagent le même rang et le
     * suivant est décalé d'autant, comme dans l'application d'origine.
     */
    #[ORM\Column]
    private ?int $rang = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Depute $depute = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Groupe $groupe = null;

    /**
     * Valeur classée, dans l'unité du critère : un taux entre 0 et 1 pour la
     * participation, la loyauté, la cohésion, la féminisation et l'indice de
     * Rose ; un nombre d'années pour l'âge.
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 3)]
    private ?string $score = null;

    /**
     * Numérateur du score, quand il en a un : votes conformes, scrutins
     * auxquels le député a pris part, nombre de femmes du groupe.
     */
    #[ORM\Column(nullable: true)]
    private ?int $numerateur = null;

    /** Dénominateur correspondant : scrutins retenus, effectif du groupe. */
    #[ORM\Column(nullable: true)]
    private ?int $denominateur = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $calculeLe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?TypeClassement
    {
        return $this->type;
    }

    public function setType(TypeClassement $type): static
    {
        $this->type = $type;

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

    public function getRang(): ?int
    {
        return $this->rang;
    }

    public function setRang(int $rang): static
    {
        $this->rang = $rang;

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

    public function getGroupe(): ?Groupe
    {
        return $this->groupe;
    }

    public function setGroupe(?Groupe $groupe): static
    {
        $this->groupe = $groupe;

        return $this;
    }

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(string $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getNumerateur(): ?int
    {
        return $this->numerateur;
    }

    public function setNumerateur(?int $numerateur): static
    {
        $this->numerateur = $numerateur;

        return $this;
    }

    public function getDenominateur(): ?int
    {
        return $this->denominateur;
    }

    public function setDenominateur(?int $denominateur): static
    {
        $this->denominateur = $denominateur;

        return $this;
    }

    public function getCalculeLe(): ?\DateTimeImmutable
    {
        return $this->calculeLe;
    }

    public function setCalculeLe(\DateTimeImmutable $calculeLe): static
    {
        $this->calculeLe = $calculeLe;

        return $this;
    }
}
