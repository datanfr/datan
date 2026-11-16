<?php

namespace App\Entity;

use App\Repository\ContactDeputeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Coordonnées et réseaux sociaux d'un député : site personnel, courriels et
 * comptes X / Facebook / Bluesky.
 *
 * Ces informations ne sont dans aucun dépôt des Tricoteuses — l'open data de
 * l'Assemblée ne publie pas les réseaux sociaux — et Datan les tient **à la
 * main** (cf. CLAUDE.md, « à la charge de Datan »). Elles vivent donc à part
 * de {@see Depute}, sur le modèle de {@see ProfilSocial} : une fiche annexe,
 * 1:1 avec le député, qu'un import de récupération charge depuis la base de
 * production (`deputes_contacts`) puis que la rédaction entretient.
 *
 * **À ne pas confondre avec `profil_social`**, dont le nom prête à confusion :
 * celle-ci porte le profil *socio-professionnel* (naissance, métier, CSP), pas
 * les réseaux sociaux. Les deux tables sont des annexes distinctes du député.
 *
 * Pas de `#[ApiResource]` : donnée éditoriale, jamais exposée en écriture par
 * l'API.
 */
#[ORM\Entity(repositoryClass: ContactDeputeRepository::class)]
#[ORM\Table(name: 'contact_depute')]
class ContactDepute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private ?Depute $depute = null;

    /** Site internet personnel, sans le protocole : « jeandupont.fr ». */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siteWeb = null;

    /** Courriel officiel à l'Assemblée (`@assemblee-nationale.fr`). */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailAn = null;

    /** Courriel personnel publié par le député dans l'open data. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailPerso = null;

    /** Pseudo X (Twitter), sans l'arobase : « jeandupont ». */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $twitter = null;

    /** Identifiant de page Facebook, tel qu'il suit facebook.com/. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebook = null;

    /** Poignée Bluesky complète : « jeandupont.bsky.social ». */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bluesky = null;

    /**
     * Date de dernière mise à jour de la fiche (`dateMaj` de l'origine).
     *
     * Nom de colonne épinglé : la stratégie de nommage de Doctrine collerait
     * « AJ » en `mis_ajour_le`, là où la migration et les imports SQL écrivent
     * `mis_a_jour_le`. On fixe le nom pour que l'ORM lise la bonne colonne.
     */
    #[ORM\Column(name: 'mis_a_jour_le', type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $misAJourLe = null;

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

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function setSiteWeb(?string $siteWeb): static
    {
        $this->siteWeb = $siteWeb;

        return $this;
    }

    public function getMailAn(): ?string
    {
        return $this->mailAn;
    }

    public function setMailAn(?string $mailAn): static
    {
        $this->mailAn = $mailAn;

        return $this;
    }

    public function getMailPerso(): ?string
    {
        return $this->mailPerso;
    }

    public function setMailPerso(?string $mailPerso): static
    {
        $this->mailPerso = $mailPerso;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): static
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): static
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getBluesky(): ?string
    {
        return $this->bluesky;
    }

    public function setBluesky(?string $bluesky): static
    {
        $this->bluesky = $bluesky;

        return $this;
    }

    public function getMisAJourLe(): ?\DateTimeImmutable
    {
        return $this->misAJourLe;
    }

    public function setMisAJourLe(?\DateTimeImmutable $misAJourLe): static
    {
        $this->misAJourLe = $misAJourLe;

        return $this;
    }
}
