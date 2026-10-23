<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Inscription à la newsletter (table « newsletter » de l'application
 * d'origine).
 *
 * Deux listes coexistent, héritées de Mailjet : la « générale » (nouvelles du
 * projet, une fois par mois) et « votes » (les derniers scrutins). Le legacy
 * abonne toute nouvelle adresse aux deux ; la désinscription fine se fait sur
 * la page d'édition, non portée à ce jour.
 *
 * `depute` et `departement` sont des vestiges du legacy, jamais écrits par le
 * formulaire ; ils sont conservés pour que la récupération des abonnés réels,
 * au déploiement, ne perde rien.
 */
#[ORM\Entity]
#[ORM\Table(name: 'newsletter')]
#[ORM\UniqueConstraint(name: 'uniq_newsletter_email', columns: ['email'])]
class Newsletter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    /** 0/1 — SMALLINT et non booléen : le pilote mysqli lie un `false` en chaîne vide. */
    #[ORM\Column(type: Types::SMALLINT)]
    private int $listeGenerale = 1;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $listeVotes = 1;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $depute = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $departement = null;

    /** Identifiant du compte lié dans la table des utilisateurs du legacy, sans contrainte. */
    #[ORM\Column(nullable: true)]
    private ?int $utilisateurId = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $inscritLe = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $modifieLe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getListeGenerale(): int
    {
        return $this->listeGenerale;
    }

    public function setListeGenerale(int $listeGenerale): static
    {
        $this->listeGenerale = $listeGenerale;

        return $this;
    }

    public function getListeVotes(): int
    {
        return $this->listeVotes;
    }

    public function setListeVotes(int $listeVotes): static
    {
        $this->listeVotes = $listeVotes;

        return $this;
    }

    public function getDepute(): ?string
    {
        return $this->depute;
    }

    public function setDepute(?string $depute): static
    {
        $this->depute = $depute;

        return $this;
    }

    public function getDepartement(): ?string
    {
        return $this->departement;
    }

    public function setDepartement(?string $departement): static
    {
        $this->departement = $departement;

        return $this;
    }

    public function getUtilisateurId(): ?int
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId(?int $utilisateurId): static
    {
        $this->utilisateurId = $utilisateurId;

        return $this;
    }

    public function getInscritLe(): ?\DateTimeImmutable
    {
        return $this->inscritLe;
    }

    public function setInscritLe(\DateTimeImmutable $inscritLe): static
    {
        $this->inscritLe = $inscritLe;

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
