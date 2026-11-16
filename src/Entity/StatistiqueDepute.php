<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Statistiques précalculées d'un député pour une législature : participation aux
 * scrutins solennels, proximité (loyauté) avec son groupe et proximité avec la
 * majorité gouvernementale. Alimente les cartes « Son comportement politique » de
 * la fiche (`class_participation_solennels`, `class_loyaute` et `class_majorite`
 * de l'application d'origine).
 *
 * **Précalcul, comme le legacy** (`daily.php`) : ces cartes comparent le député à
 * la moyenne de tous les députés et à celle de son groupe. Recalculer ces
 * moyennes à chaque affichage supposerait de rejouer 1,27 M de votes par requête
 * — impossible en ~20 ms. La commande `app:calcul:statistiques-deputes` remplit
 * cette table ; la fiche s'y lit en DBAL.
 *
 * La table porte une ligne par (député, législature) : les votes nominatifs des
 * législatures 14 à 16 sont importés des dépôts `Scrutins_XIV/XV/XVI_nettoye`,
 * et leurs lignes, calculées une fois pour toutes, survivent au recalcul
 * quotidien de la législature courante (la commande ne réécrit que la sienne).
 */
#[ORM\Entity]
#[ORM\Table(name: 'statistique_depute')]
#[ORM\UniqueConstraint(name: 'uniq_statistique_depute', columns: ['depute_id', 'legislature'])]
#[ORM\Index(name: 'idx_statistique_groupe', columns: ['groupe_id', 'legislature'])]
class StatistiqueDepute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $deputeId = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $legislature = null;

    /** Taux de participation aux scrutins solennels, en %, ou null si aucun encore tenu. */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $participationScore = null;

    #[ORM\Column]
    private int $participationVotes = 0;

    /** Taux de proximité avec le groupe, en %, ou null si trop peu de votes. */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $loyauteScore = null;

    #[ORM\Column]
    private int $loyauteVotes = 0;

    /**
     * Taux de proximité avec la majorité gouvernementale, en % (`class_majorite`).
     * Null quand la législature ne déclare aucun groupe majoritaire : c'est le cas
     * de la 17e depuis la dissolution de 2024 (cf. CLAUDE.md), et sa fiche n'a donc
     * pas cette carte — jamais un groupe choisi au jugé pour la remplacer.
     */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $majoriteScore = null;

    // `options: ['default' => 0]` n'est pas décoratif : la migration qui a créé
    // la colonne écrit `INT DEFAULT 0 NOT NULL`, et un `= 0` PHP n'est qu'un
    // défaut d'objet, invisible du schéma. Sans cette option, chaque
    // `doctrine:schema:validate` déclarait la base désynchronisée et proposait
    // de retirer le DEFAULT — un diff fantôme, sur une colonne pourtant juste.
    #[ORM\Column(options: ['default' => 0])]
    private int $majoriteVotes = 0;

    /** 1 si le député siège encore (mandat sans date de fin), 0 sinon. */
    #[ORM\Column(type: Types::SMALLINT)]
    private int $actif = 0;

    /**
     * Groupe du député pour cette législature (rattachement le plus récent,
     * principal). `depute.groupe_id` ne porte que l'appartenance courante : sur
     * une législature close, c'est cette colonne qui permet la moyenne de
     * groupe de la fiche.
     */
    #[ORM\Column(nullable: true)]
    private ?int $groupeId = null;

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

    public function getLegislature(): ?int
    {
        return $this->legislature;
    }

    public function setLegislature(int $legislature): static
    {
        $this->legislature = $legislature;

        return $this;
    }

    public function getParticipationScore(): ?int
    {
        return $this->participationScore;
    }

    public function setParticipationScore(?int $participationScore): static
    {
        $this->participationScore = $participationScore;

        return $this;
    }

    public function getParticipationVotes(): int
    {
        return $this->participationVotes;
    }

    public function setParticipationVotes(int $participationVotes): static
    {
        $this->participationVotes = $participationVotes;

        return $this;
    }

    public function getLoyauteScore(): ?int
    {
        return $this->loyauteScore;
    }

    public function setLoyauteScore(?int $loyauteScore): static
    {
        $this->loyauteScore = $loyauteScore;

        return $this;
    }

    public function getLoyauteVotes(): int
    {
        return $this->loyauteVotes;
    }

    public function setLoyauteVotes(int $loyauteVotes): static
    {
        $this->loyauteVotes = $loyauteVotes;

        return $this;
    }

    public function getMajoriteScore(): ?int
    {
        return $this->majoriteScore;
    }

    public function setMajoriteScore(?int $majoriteScore): static
    {
        $this->majoriteScore = $majoriteScore;

        return $this;
    }

    public function getMajoriteVotes(): int
    {
        return $this->majoriteVotes;
    }

    public function setMajoriteVotes(int $majoriteVotes): static
    {
        $this->majoriteVotes = $majoriteVotes;

        return $this;
    }

    public function getActif(): int
    {
        return $this->actif;
    }

    public function setActif(int $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getGroupeId(): ?int
    {
        return $this->groupeId;
    }

    public function setGroupeId(?int $groupeId): static
    {
        $this->groupeId = $groupeId;

        return $this;
    }
}
