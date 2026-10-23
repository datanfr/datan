<?php

namespace App\Entity;

use App\Repository\ReinitialisationMotDePasseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Jeton de réinitialisation de mot de passe, porté de la table `password_resets`
 * de l'application d'origine (`User_model::create_token_password_lost`).
 *
 * Le legacy y rangeait un couple (email, token, created_at) et n'acceptait le
 * jeton que dans l'heure suivant sa création
 * (`password_lost_change` : `created_at > NOW() - 1 heure`). On garde cette
 * fenêtre d'une heure — un lien de réinitialisation envoyé par courriel n'a pas
 * à vivre plus longtemps. Le lien est ici rattaché au compte plutôt qu'à une
 * adresse : la clé étrangère garantit qu'il désigne un compte réel, là où le
 * legacy rejouait une recherche par e-mail au moment du changement.
 */
#[ORM\Entity(repositoryClass: ReinitialisationMotDePasseRepository::class)]
#[ORM\Table(name: 'reinitialisation_mot_de_passe')]
#[ORM\UniqueConstraint(name: 'uniq_reinitialisation_token', columns: ['token'])]
#[ORM\Index(name: 'IDX_reinitialisation_utilisateur', columns: ['utilisateur_id'])]
class ReinitialisationMotDePasse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Le compte à réinitialiser. `CASCADE` : un compte supprimé emporte ses
     * jetons pendants, qui n'ont plus de cible.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(length: 100)]
    private ?string $token = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $creeLe;

    public function __construct()
    {
        $this->creeLe = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getCreeLe(): \DateTimeImmutable
    {
        return $this->creeLe;
    }

    /**
     * Le jeton n'est valable qu'une heure après sa création, comme le legacy.
     */
    public function estValide(): bool
    {
        return $this->creeLe > new \DateTimeImmutable('-1 hour');
    }
}
