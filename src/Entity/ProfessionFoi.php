<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Profession de foi d'un député candidat à une élection (table `profession_foi`
 * de l'application d'origine) : un document PDF par tour, servi depuis
 * `assets/data/professions/election_<id>/<fichier>` — les mêmes chemins que
 * datan.fr, pour que les adresses des documents ne bougent pas.
 *
 * **Le backup public livre cette table VIDE** (jeu réduit, comme `users_mp`) :
 * `app:import:professions-foi` est à rejouer contre la vraie base de production
 * au déploiement, avec la copie du répertoire d'assets correspondant (TODO §2).
 * D'ici là, le bloc « Ses professions de foi » de la fiche reste masqué.
 *
 * Pas de `#[ApiResource]` : lecture interne de la fiche député uniquement.
 * `$mpId` reste l'uid d'acteur (`PA…`), la clé du legacy — inutile de résoudre
 * `depute_id` pour une table jointe une fois par fiche.
 */
#[ORM\Entity]
#[ORM\Table(name: 'profession_foi')]
#[ORM\UniqueConstraint(name: 'uniq_profession_foi', columns: ['mp_id', 'election_id', 'tour'])]
#[ORM\Index(name: 'idx_profession_foi_mp', columns: ['mp_id'])]
class ProfessionFoi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Uid d'acteur de l'Assemblée (« PA720430 »), comme `parrainage.mp_id`. */
    #[ORM\Column(length: 32)]
    private ?string $mpId = null;

    /** Élection du catalogue (`election.id`, identifiants repris du legacy). */
    #[ORM\Column]
    private ?int $electionId = null;

    /** Nom du fichier PDF tel que publié (« bernalicis-t1.pdf »). */
    #[ORM\Column(length: 255)]
    private ?string $fichier = null;

    /** 1er ou 2nd tour. */
    #[ORM\Column(type: 'smallint')]
    private ?int $tour = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMpId(): ?string
    {
        return $this->mpId;
    }

    public function setMpId(string $mpId): static
    {
        $this->mpId = $mpId;

        return $this;
    }

    public function getElectionId(): ?int
    {
        return $this->electionId;
    }

    public function setElectionId(int $electionId): static
    {
        $this->electionId = $electionId;

        return $this;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(string $fichier): static
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getTour(): ?int
    {
        return $this->tour;
    }

    public function setTour(int $tour): static
    {
        $this->tour = $tour;

        return $this;
    }
}
