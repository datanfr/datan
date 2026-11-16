<?php

namespace App\Entity;

use App\Repository\FaqPostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Question/réponse de la foire aux questions (table « faq_posts » de
 * l'application d'origine).
 *
 * Contenu éditorial, à préserver tel quel. La réponse est du HTML rédigé au
 * back-office ; le brouillon (`etat = 'draft'`) est conservé mais ne s'affiche
 * pas sur la page publique, qui ne montre que le « published ».
 *
 * Ni l'auteur ni la date ne sont montrés sur la page ; l'ordre d'affichage,
 * faute de colonne dédiée dans la source, est celui de l'identifiant, repris
 * dans {@see $ordre}. Le rattachement à une catégorie est facultatif : le
 * legacy le joint sans contrainte (MyISAM).
 */
#[ORM\Entity(repositoryClass: FaqPostRepository::class)]
#[ORM\Table(name: 'faq_post')]
#[ORM\UniqueConstraint(name: 'uniq_faq_post_slug', columns: ['slug'])]
#[ORM\Index(name: 'idx_faq_post_categorie', columns: ['categorie_id'])]
class FaqPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?FaqCategorie $categorie = null;

    #[ORM\Column(length: 255)]
    private ?string $question = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $reponse = null;

    /** État de publication, repris tel quel du legacy (« published », « draft »). */
    #[ORM\Column(length: 15, nullable: true)]
    private ?string $etat = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $ordre = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $creeLe = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $modifieLe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategorie(): ?FaqCategorie
    {
        return $this->categorie;
    }

    public function setCategorie(?FaqCategorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

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

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(string $reponse): static
    {
        $this->reponse = $reponse;

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

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

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
