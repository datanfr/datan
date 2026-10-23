<?php

namespace App\Entity;

use App\Repository\ParrainageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Parrainage d'un candidat à l'élection présidentielle par un élu (table
 * « parrainages » de l'application d'origine).
 *
 * 13 427 lignes pour 2022 : chaque élu habilité — député, sénateur, maire,
 * conseiller… — qui a accordé sa signature à un candidat, telles que le Conseil
 * constitutionnel les a publiées. La page `/parrainages-2022` n'en montre que le
 * volet des députés (530), mais le décompte des « plus de 500 signatures » se
 * fait sur l'ensemble : la table est donc reprise en entier.
 *
 * **Source régénérable, mais préservée ici.** Contrairement aux décryptages ou
 * aux explications, ces parrainages sont republiés par le Conseil
 * constitutionnel (open data) ; ils ne sont pas irremplaçables. On les récupère
 * néanmoins depuis la base de production, faute d'y avoir un import dédié, et
 * pour tenir la page sans dépendre d'une source externe. Aucune écriture n'est
 * exposée : pas de `#[ApiResource]` — le legacy ne les publie pas non plus par
 * API.
 *
 * {@see $sourceId} reprend l'identifiant du legacy, seule clé stable de l'import
 * (ces lignes n'ont pas de slug).
 */
#[ORM\Entity(repositoryClass: ParrainageRepository::class)]
#[ORM\Table(name: 'parrainage')]
#[ORM\UniqueConstraint(name: 'uniq_parrainage_source', columns: ['source_id'])]
#[ORM\Index(name: 'idx_parrainage_mp', columns: ['mp_id'])]
#[ORM\Index(name: 'idx_parrainage_annee', columns: ['annee'])]
class Parrainage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Identifiant d'origine (`parrainages.id`), gardé comme clé de l'import. */
    #[ORM\Column]
    private ?int $sourceId = null;

    /** Civilité de l'élu telle que publiée (« M. », « Mme »). */
    #[ORM\Column(length: 10)]
    private ?string $civilite = null;

    /**
     * Nom et prénom de l'élu tels que le Conseil constitutionnel les a saisis —
     * accents parfois absents (« Eric »), majuscules capricieuses
     * (« Bono-vandorme »). Conservés à l'identique : c'est la donnée source, et
     * la page affichera ces formes comme le fait datan.fr. La casse propre vit,
     * elle, dans `depute`, pour les 530 qui ont une fiche.
     */
    #[ORM\Column(length: 75)]
    private ?string $nom = null;

    #[ORM\Column(length: 75)]
    private ?string $prenom = null;

    /**
     * Mandat au titre duquel l'élu parraine (« député », « maire », « sénateur »…),
     * en minuscules dans la source. C'est lui qui isole les députés de la page.
     */
    #[ORM\Column(length: 100)]
    private ?string $mandat = null;

    /** Circonscription ou territoire de l'élu, texte libre de la source. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $circonscription = null;

    /** Département de l'élu, nom seul (sans code) tel que publié. */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $departement = null;

    /** Candidat parrainé, sous la forme « NOM Prénom » de la source. */
    #[ORM\Column(length: 100)]
    private ?string $candidat = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $datePublication = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $annee = null;

    /**
     * Identifiant d'acteur de l'Assemblée (`PA…`), renseigné pour les seuls
     * députés (530) : c'est la clé de jointure vers `depute`. Nul pour tout autre
     * élu.
     */
    #[ORM\Column(length: 35, nullable: true)]
    private ?string $mpId = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateMaj = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceId(): ?int
    {
        return $this->sourceId;
    }

    public function setSourceId(int $sourceId): static
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(?string $civilite): static
    {
        $this->civilite = $civilite;

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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getMandat(): ?string
    {
        return $this->mandat;
    }

    public function setMandat(string $mandat): static
    {
        $this->mandat = $mandat;

        return $this;
    }

    public function getCirconscription(): ?string
    {
        return $this->circonscription;
    }

    public function setCirconscription(?string $circonscription): static
    {
        $this->circonscription = $circonscription;

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

    public function getCandidat(): ?string
    {
        return $this->candidat;
    }

    public function setCandidat(string $candidat): static
    {
        $this->candidat = $candidat;

        return $this;
    }

    public function getDatePublication(): ?\DateTimeImmutable
    {
        return $this->datePublication;
    }

    public function setDatePublication(?\DateTimeImmutable $datePublication): static
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function getMpId(): ?string
    {
        return $this->mpId;
    }

    public function setMpId(?string $mpId): static
    {
        $this->mpId = $mpId;

        return $this;
    }

    public function getDateMaj(): ?\DateTimeImmutable
    {
        return $this->dateMaj;
    }

    public function setDateMaj(?\DateTimeImmutable $dateMaj): static
    {
        $this->dateMaj = $dateMaj;

        return $this;
    }
}
