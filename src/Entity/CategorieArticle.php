<?php

namespace App\Entity;

use App\Repository\CategorieArticleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Rubrique d'un article de blog (table « categories » de l'application
 * d'origine).
 *
 * Distincte de {@see Categorie}, qui classe les décryptages (table « fields ») :
 * les deux vocabulaires ne se recouvrent pas, d'où une table dédiée
 * `categorie_article`.
 */
#[ORM\Entity(repositoryClass: CategorieArticleRepository::class)]
#[ORM\Table(name: 'categorie_article')]
#[ORM\UniqueConstraint(name: 'uniq_categorie_article_slug', columns: ['slug'])]
class CategorieArticle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

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
}
