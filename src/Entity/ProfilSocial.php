<?php

namespace App\Entity;

use App\Repository\ProfilSocialRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Profil socio-professionnel d'un député : sa date de naissance et le métier
 * qu'il exerçait avant son élection, rangé dans la nomenclature de l'INSEE.
 *
 * Ces données alimentent les classements par âge et par origine sociale.
 * {@see Depute} porte déjà `age` et `profession`, mais l'un est un entier figé
 * à la date de l'import — insuffisant pour départager deux députés du même âge —
 * et l'autre un libellé brut de l'open data, sans catégorie ni famille. Le
 * profil est donc tenu à part plutôt que d'élargir `Depute`, dont d'autres
 * pages dépendent.
 */
#[ORM\Entity(repositoryClass: ProfilSocialRepository::class)]
#[ORM\Table(name: 'profil_social')]
#[ORM\Index(name: 'idx_fam_soc_pro', columns: ['fam_soc_pro'])]
class ProfilSocial
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private ?Depute $depute = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateNaissance = null;

    /** Métier déclaré, tel que publié : « Avocate », « Professeur des écoles »… */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $metier = null;

    /** Catégorie socio-professionnelle de l'INSEE, la maille fine. */
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $catSocPro = null;

    /**
     * Famille socio-professionnelle, la maille large : l'un des huit libellés
     * de {@see \App\FamilleSocioPro}, ou `null` si la profession déclarée ne
     * permet pas de classer le député.
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $famSocPro = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepute(): ?Depute
    {
        return $this->depute;
    }

    public function setDepute(Depute $depute): static
    {
        $this->depute = $depute;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeImmutable
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeImmutable $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getMetier(): ?string
    {
        return $this->metier;
    }

    public function setMetier(?string $metier): static
    {
        $this->metier = $metier;

        return $this;
    }

    public function getCatSocPro(): ?string
    {
        return $this->catSocPro;
    }

    public function setCatSocPro(?string $catSocPro): static
    {
        $this->catSocPro = $catSocPro;

        return $this;
    }

    public function getFamSocPro(): ?string
    {
        return $this->famSocPro;
    }

    public function setFamSocPro(?string $famSocPro): static
    {
        $this->famSocPro = $famSocPro;

        return $this;
    }

    /** Âge en années révolues à la date donnée, comme l'affiche le site. */
    public function age(\DateTimeImmutable $a): ?int
    {
        return $this->dateNaissance?->diff($a)->y;
    }
}
