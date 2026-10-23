<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Compte de connexion : membre de la rédaction, député, ou simple lecteur.
 *
 * Côté rédaction, deux rôles suffisent, repris de l'application d'origine qui
 * distinguait le `type` « admin » des autres comptes : un rédacteur écrit et
 * publie ses décryptages, un administrateur peut en outre reprendre un
 * décryptage déjà publié et en supprimer.
 *
 * Un député, lui, n'administre rien : il rédige ses explications de vote dans
 * son propre espace. L'application d'origine le marquait du `type` « mp » et
 * gardait l'identifiant du député à côté, dans la session
 * (`DashboardMP::__construct`).
 *
 * Un lecteur (le `type` vide de l'origine) n'a accès qu'à son compte : ni
 * rédaction, ni espace député. Il porte ROLE_LECTEUR **explicitement** —
 * getRoles() en fait la garde, car sans elle un compte sans député ni rôle
 * retomberait sur ROLE_REDACTEUR et ouvrirait la rédaction à un lecteur.
 */
#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[ORM\UniqueConstraint(name: 'uniq_identifiant', columns: ['identifiant'])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_REDACTEUR = 'ROLE_REDACTEUR';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_DEPUTE = 'ROLE_DEPUTE';
    public const ROLE_LECTEUR = 'ROLE_LECTEUR';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Identifiant de connexion (le `username` de l'application d'origine). */
    #[ORM\Column(length: 180)]
    private ?string $identifiant = null;

    /** Nom affiché à côté des décryptages. */
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    /**
     * Code postal du lecteur, repris du champ `zipcode` de la table `users` de
     * l'origine (collecté à l'inscription, modifiable dans « mon compte »). Nul
     * pour la rédaction et les députés, qui ne le renseignent pas.
     */
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $codePostal = null;

    /**
     * Député dont ce compte est l'espace personnel.
     *
     * Renseigné, il fait basculer le compte dans l'espace député et lui retire
     * tout accès à la rédaction : c'est le lien lui-même qui porte le rôle,
     * voir getRoles().
     *
     * D'où l'absence de `onDelete: SET NULL` : effacer le député rendrait à son
     * compte les droits de la rédaction, sans que rien ne le signale. La clé
     * étrangère refuse donc la suppression tant que le compte existe.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Depute $depute = null;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $motDePasse = null;

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

    public function getIdentifiant(): ?string
    {
        return $this->identifiant;
    }

    public function setIdentifiant(string $identifiant): static
    {
        $this->identifiant = $identifiant;

        return $this;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->identifiant;
    }

    /**
     * Rôles du compte, les trois familles étant exclusives l'une de l'autre.
     *
     * Un compte rattaché à un député ne reçoit que ROLE_DEPUTE, quoi que porte
     * sa colonne `roles` : l'application d'origine n'avait qu'un `type`, et un
     * député n'y était jamais « admin » ou « writer ». Écrire l'exclusion ici
     * plutôt que de faire confiance aux données évite qu'une ligne mal saisie
     * n'ouvre la rédaction à un élu.
     *
     * Un lecteur porte ROLE_LECTEUR et **rien d'autre** : sans cette garde, un
     * compte sans député et à `roles` vide — exactement ce qu'est un lecteur —
     * se verrait ajouter ROLE_REDACTEUR par le repli ci-dessous et entrerait
     * dans la rédaction. C'est le piège que la mission des comptes lecteurs
     * signalait ; l'exclusion est écrite ici, pas confiée aux données.
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        if ($this->depute !== null) {
            return [self::ROLE_DEPUTE];
        }

        if (\in_array(self::ROLE_LECTEUR, $this->roles, true)) {
            return [self::ROLE_LECTEUR];
        }

        $roles = $this->roles;
        $roles[] = self::ROLE_REDACTEUR;

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function estAdmin(): bool
    {
        return \in_array(self::ROLE_ADMIN, $this->getRoles(), true);
    }

    public function estDepute(): bool
    {
        return $this->depute !== null;
    }

    public function estLecteur(): bool
    {
        return $this->depute === null && \in_array(self::ROLE_LECTEUR, $this->roles, true);
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getDepute(): ?Depute
    {
        return $this->depute;
    }

    public function setDepute(?Depute $depute): static
    {
        $this->depute = $depute;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }

    public function setPassword(string $motDePasse): static
    {
        $this->motDePasse = $motDePasse;

        return $this;
    }

    public function getCreeLe(): \DateTimeImmutable
    {
        return $this->creeLe;
    }

    public function eraseCredentials(): void
    {
    }

    public function __toString(): string
    {
        return (string) $this->nom;
    }
}
