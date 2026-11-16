<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Taux de proximité précalculé entre un député et un groupe parlementaire pour
 * une législature : part des scrutins où le député a voté dans le sens de la
 * position majoritaire du groupe (`deputes_accord_cleaned` de l'application
 * d'origine).
 *
 * Une ligne par couple (député, groupe). Alimente la carte « Proximité avec les
 * groupes politiques » de la fiche — les barres des groupes les plus et les
 * moins proches, et le classement complet. Précalcul obligatoire : l'agrégation
 * sur les 1,27 M de votes croisés aux positions de groupe prend une quinzaine de
 * secondes, hors de portée d'un affichage en cache.
 *
 * `votes_n` compte les scrutins comparables (le député s'est exprimé ET le
 * groupe a une position majoritaire) ; la fiche écarte les couples sous onze
 * votes, comme le legacy (`votesN > 10`), un accord sur trois scrutins ne voulant
 * rien dire.
 */
#[ORM\Entity]
#[ORM\Table(name: 'accord_groupe')]
#[ORM\UniqueConstraint(name: 'uniq_accord_groupe', columns: ['depute_id', 'groupe_id', 'legislature'])]
#[ORM\Index(name: 'idx_accord_depute', columns: ['depute_id', 'legislature'])]
class AccordGroupe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $deputeId = null;

    #[ORM\Column]
    private ?int $groupeId = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $legislature = null;

    /** Taux de proximité, en %. */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $accord = null;

    #[ORM\Column]
    private ?int $votesN = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeputeId(): ?int
    {
        return $this->deputeId;
    }

    public function setDeputeId(int $deputeId): static
    {
        $this->deputeId = $deputeId;

        return $this;
    }

    public function getGroupeId(): ?int
    {
        return $this->groupeId;
    }

    public function setGroupeId(int $groupeId): static
    {
        $this->groupeId = $groupeId;

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

    public function getAccord(): ?int
    {
        return $this->accord;
    }

    public function setAccord(int $accord): static
    {
        $this->accord = $accord;

        return $this;
    }

    public function getVotesN(): ?int
    {
        return $this->votesN;
    }

    public function setVotesN(int $votesN): static
    {
        $this->votesN = $votesN;

        return $this;
    }
}
