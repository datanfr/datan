<?php

namespace App\Entity;

use App\Repository\DemandeCompteDeputeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Demande d'ouverture d'un compte par un député (`/demande-compte-depute`).
 *
 * L'application d'origine ne stockait qu'un jeton éphémère (`users_mp_link`) :
 * le député saisissait son adresse institutionnelle, recevait par courriel un
 * lien d'activation valable 24 h, et créait lui-même son compte via
 * `/register/{token}`. La possession de l'adresse `@assemblee-nationale.fr`
 * tenait donc lieu de contrôle — seul le vrai député recevait le lien.
 *
 * Ce portage n'envoie le courriel qu'au déploiement (MAILER_DSN, `null://` en
 * local) : la demande devient d'abord une ligne **en attente** qu'un
 * administrateur relit. À l'approbation seulement, on restitue le jeton du
 * legacy — un `token` posé ici même — et le lien `/register/{token}` par lequel
 * le député crée lui-même son compte et choisit son mot de passe. La relecture
 * humaine reste donc en amont du jeton, et le contrôle par l'adresse
 * institutionnelle tient toujours : le lien y est transmis.
 *
 * Divergence assumée avec le legacy : son jeton `users_mp_link` expirait 24 h
 * après **l'envoi automatique** du courriel de demande. Ici le jeton naît à
 * l'approbation et un humain le transmet à une échéance inconnue ; une fenêtre
 * de 24 h enfermerait dehors la moitié des députés. Le jeton est donc à usage
 * unique (annulé dès le compte créé) sans butoir horaire — le nettoyer à la
 * main reste possible via un refus.
 */
#[ORM\Entity(repositoryClass: DemandeCompteDeputeRepository::class)]
#[ORM\Table(name: 'demande_compte_depute')]
#[ORM\Index(name: 'idx_demande_depute', columns: ['depute_id'])]
#[ORM\Index(name: 'idx_demande_etat', columns: ['etat'])]
#[ORM\UniqueConstraint(name: 'uniq_demande_token', columns: ['token'])]
class DemandeCompteDepute
{
    public const EN_ATTENTE = 'en_attente';
    public const APPROUVEE = 'approuvee';
    public const REFUSEE = 'refusee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Depute $depute = null;

    /** L'adresse institutionnelle telle que saisie, conservée pour la relire. */
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    private string $etat = self::EN_ATTENTE;

    /**
     * Jeton d'activation, posé à l'approbation et porté par le lien
     * `/register/{token}`. Nul tant que la demande n'est pas approuvée, et
     * annulé (remis à nul) une fois le compte créé : un jeton à usage unique.
     * L'unicité tolère plusieurs nuls — MariaDB l'admet sur un index unique.
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $demandeeLe;

    /** Renseignée quand un administrateur a approuvé ou refusé la demande. */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $traiteeLe = null;

    public function __construct()
    {
        $this->demandeeLe = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepute(): ?Depute
    {
        return $this->depute;
    }

    public function setDepute(Depute $depute): static
    {
        $this->depute = $depute;

        return $this;
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

    public function getEtat(): string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function estEnAttente(): bool
    {
        return $this->etat === self::EN_ATTENTE;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getDemandeeLe(): \DateTimeImmutable
    {
        return $this->demandeeLe;
    }

    public function getTraiteeLe(): ?\DateTimeImmutable
    {
        return $this->traiteeLe;
    }

    public function setTraiteeLe(?\DateTimeImmutable $traiteeLe): static
    {
        $this->traiteeLe = $traiteeLe;

        return $this;
    }
}
