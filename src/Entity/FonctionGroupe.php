<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\FonctionGroupeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Rattachement d'un député à un groupe parlementaire, avec la qualité exercée
 * (président, membre, membre apparenté…) et ses bornes de date.
 *
 * Complète {@see Depute::$groupe}, qui ne porte que l'appartenance courante :
 * on trouve ici la présidence en cours d'un groupe comme la succession de ses
 * présidents, et l'historique des rattachements.
 */
#[ORM\Entity(repositoryClass: FonctionGroupeRepository::class)]
#[ORM\Table(name: 'fonction_groupe')]
#[ORM\UniqueConstraint(name: 'uniq_depute_groupe_qualite_debut', columns: ['depute_id', 'groupe_id', 'code_qualite', 'date_debut'])]
#[ORM\Index(name: 'idx_groupe_qualite', columns: ['groupe_id', 'code_qualite'])]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['fonction_groupe:read']],
)]
#[ApiFilter(SearchFilter::class, properties: ['groupe' => 'exact', 'depute' => 'exact', 'codeQualite' => 'exact'])]
class FonctionGroupe
{
    /** Qualité des présidents de groupe, telle que publiée par l'Assemblée. */
    public const QUALITE_PRESIDENT = 'Président';

    /** Qualité par défaut, quand l'open data n'en publie aucune. */
    public const QUALITE_MEMBRE = 'Membre';

    /** Un député rattaché à un groupe sans en être membre de plein droit. */
    public const QUALITE_APPARENTE = 'Membre apparenté';

    /** Qualité des députés qui ne sont membres d'aucun groupe. */
    public const QUALITE_NON_INSCRIT = 'Député non-inscrit';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['fonction_groupe:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['fonction_groupe:read', 'fonction_groupe:write', 'groupe:read'])]
    private ?Depute $depute = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['fonction_groupe:read', 'fonction_groupe:write', 'depute:read'])]
    private ?Groupe $groupe = null;

    /** Code de qualité : « Président », « Membre », « Membre apparenté »… */
    #[ORM\Column(length: 50)]
    #[Groups(['fonction_groupe:read', 'fonction_groupe:write', 'groupe:read', 'depute:read'])]
    private ?string $codeQualite = null;

    /** Libellé affichable : « Président du », « Membre du », « Apparenté au »… */
    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['fonction_groupe:read', 'fonction_groupe:write'])]
    private ?string $libelleQualite = null;

    /**
     * Nomination principale, au sens de l'Assemblée.
     *
     * Un député peut porter plusieurs rattachements ouverts au même moment ;
     * un seul est principal, et c'est celui-là qui compte dans l'effectif d'un
     * groupe. Sur la 17e législature, les mandats de groupe ouverts sont 588,
     * dont 577 principaux — c'est-à-dire exactement le nombre de sièges. Sans
     * ce filtre, onze députés apparaîtraient dans deux groupes.
     */
    #[ORM\Column(options: ['default' => true])]
    #[Groups(['fonction_groupe:read', 'fonction_groupe:write'])]
    private bool $nominPrincipale = true;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['fonction_groupe:read', 'fonction_groupe:write', 'groupe:read'])]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['fonction_groupe:read', 'fonction_groupe:write', 'groupe:read'])]
    private ?\DateTimeImmutable $dateFin = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGroupe(): ?Groupe
    {
        return $this->groupe;
    }

    public function setGroupe(?Groupe $groupe): static
    {
        $this->groupe = $groupe;

        return $this;
    }

    public function getCodeQualite(): ?string
    {
        return $this->codeQualite;
    }

    public function setCodeQualite(string $codeQualite): static
    {
        $this->codeQualite = $codeQualite;

        return $this;
    }

    public function getLibelleQualite(): ?string
    {
        return $this->libelleQualite;
    }

    public function setLibelleQualite(?string $libelleQualite): static
    {
        $this->libelleQualite = $libelleQualite;

        return $this;
    }

    public function isNominPrincipale(): bool
    {
        return $this->nominPrincipale;
    }

    public function setNominPrincipale(bool $nominPrincipale): static
    {
        $this->nominPrincipale = $nominPrincipale;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /** Une fonction sans date de fin est celle exercée actuellement. */
    #[Groups(['fonction_groupe:read'])]
    public function isEnCours(): bool
    {
        return $this->dateFin === null;
    }
}
