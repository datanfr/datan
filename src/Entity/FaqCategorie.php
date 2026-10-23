<?php

namespace App\Entity;

use App\Repository\FaqCategorieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Catégorie de la foire aux questions (table « faq_categories » de
 * l'application d'origine).
 *
 * Contenu éditorial, à préserver comme les décryptages et le blog : rien de
 * cela n'est dans l'open data.
 *
 * La source n'a pas de colonne d'ordre — le legacy affiche les catégories dans
 * l'ordre de leur identifiant. On le conserve dans {@see $ordre}, repris de cet
 * identifiant, pour ne pas dépendre de l'ordre d'insertion.
 */
#[ORM\Entity(repositoryClass: FaqCategorieRepository::class)]
#[ORM\Table(name: 'faq_categorie')]
#[ORM\UniqueConstraint(name: 'uniq_faq_categorie_slug', columns: ['slug'])]
class FaqCategorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $ordre = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }
}
