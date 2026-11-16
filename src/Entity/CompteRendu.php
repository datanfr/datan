<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Compte rendu d'une séance publique de l'Assemblée nationale.
 *
 * Source : dépôt Tricoteuses Comptes_Rendus_Seances_XVII_nettoye, un fichier
 * JSON par séance (uid « CRSANR5L17… »). Le compte rendu est le corpus des
 * débats : c'est en lui que vivent les morceaux de discours ({@see CrParole})
 * sur lesquels s'appuie le brouillon de décryptage généré par IA.
 *
 * Le lien avec un vote passe par la séance : `scrutin.seance_ref` et
 * `compte_rendu.seance_ref` portent la même référence de réunion
 * (« RUANR5L17S2026IDS30819 »).
 *
 * Pas d'#[ApiResource] : ce corpus sert la rédaction, pas l'API publique.
 */
#[ORM\Entity]
#[ORM\Table(name: 'compte_rendu')]
#[ORM\Index(name: 'idx_compte_rendu_seance_ref', columns: ['seance_ref'])]
class CompteRendu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60, unique: true)]
    private ?string $uid = null;

    /** Référence de la réunion (séance), clé de jointure avec les scrutins. */
    #[ORM\Column(length: 60)]
    private ?string $seanceRef = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $sessionRef = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $legislature = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateSeance = null;

    /** Titre lisible (« Première séance du mercredi 01 juillet 2026 »). */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titreJournee = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $session = null;

    /**
     * `avant_JO` puis `JO` : l'Assemblée republie le compte rendu corrigé au
     * Journal officiel ; l'import réécrit alors la séance entière.
     */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $version = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $creeLe = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $modifieLe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getSeanceRef(): ?string
    {
        return $this->seanceRef;
    }

    public function setSeanceRef(string $seanceRef): static
    {
        $this->seanceRef = $seanceRef;

        return $this;
    }

    public function getSessionRef(): ?string
    {
        return $this->sessionRef;
    }

    public function setSessionRef(?string $sessionRef): static
    {
        $this->sessionRef = $sessionRef;

        return $this;
    }

    public function getLegislature(): ?int
    {
        return $this->legislature;
    }

    public function setLegislature(?int $legislature): static
    {
        $this->legislature = $legislature;

        return $this;
    }

    public function getDateSeance(): ?\DateTimeImmutable
    {
        return $this->dateSeance;
    }

    public function setDateSeance(?\DateTimeImmutable $dateSeance): static
    {
        $this->dateSeance = $dateSeance;

        return $this;
    }

    public function getTitreJournee(): ?string
    {
        return $this->titreJournee;
    }

    public function setTitreJournee(?string $titreJournee): static
    {
        $this->titreJournee = $titreJournee;

        return $this;
    }

    public function getSession(): ?string
    {
        return $this->session;
    }

    public function setSession(?string $session): static
    {
        $this->session = $session;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): static
    {
        $this->version = $version;

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
