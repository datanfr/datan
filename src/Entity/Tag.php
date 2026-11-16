<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mot-clé thématique d'un amendement (« retraites », « IVG », « méga-bassines »),
 * plus précis que sa catégorie. Remplace la colonne libre
 * `amendements_tags.tag` du legacy, où « retraite », « Retraites » et
 * « retraites » faisaient trois mots-clés.
 *
 * La clé est le **slug** ({@see \App\Ia\Tag\NormaliseurTag}) : casse, accents et
 * pluriel simple s'y effacent, de sorte que ces variantes se retrouvent sans
 * IA. Le libellé n'est que la forme affichée, celle de la première rencontre.
 *
 * Une ligne dont `synonyme_de` est renseigné n'est pas un mot-clé mais un
 * **alias** : la mémoire d'une décision déjà prise (« interruption volontaire
 * de grossesse » → « IVG »), pour que la variante ne coûte plus rien à sa
 * prochaine apparition. Un alias pointe toujours vers un mot-clé canonique,
 * jamais vers un autre alias, et aucun amendement ne s'y rattache — la table
 * pivot ne porte que des canoniques.
 *
 * Hors de l'API : le vocabulaire est un outil interne de rangement, pas une
 * donnée publiée.
 */
#[ORM\Entity]
#[ORM\Table(name: 'tag')]
#[ORM\UniqueConstraint(name: 'uniq_tag_slug', columns: ['slug'])]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $libelle = null;

    #[ORM\Column(length: 100)]
    private ?string $slug = null;

    /** Mot-clé canonique dont cette ligne est un alias ; null pour un canonique. */
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'synonyme_de_id', nullable: true, onDelete: 'SET NULL')]
    private ?Tag $synonymeDe = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $creeLe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getSynonymeDe(): ?self
    {
        return $this->synonymeDe;
    }

    public function getCreeLe(): ?\DateTimeImmutable
    {
        return $this->creeLe;
    }
}
