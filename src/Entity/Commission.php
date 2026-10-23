<?php

namespace App\Entity;

use App\Repository\CommissionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Commission permanente de l'Assemblée (organe de type « COMPER »).
 *
 * À la différence des groupes parlementaires, ces organes ne portent pas de
 * législature : les huit commissions permanentes traversent les législatures,
 * et l'open data n'en ouvre un nouveau qu'en cas de réorganisation — d'où les
 * deux organes clos en 2009, quand la commission des affaires culturelles et
 * celle des affaires économiques ont été redécoupées.
 */
#[ORM\Entity(repositoryClass: CommissionRepository::class)]
#[ORM\Table(name: 'commission')]
class Commission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Identifiant Assemblée nationale de l'organe (ex. « PO59051 »). */
    #[ORM\Column(length: 50, unique: true)]
    private ?string $uid = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /** Forme courte, seule affichée par le site (« Lois », « Finances »). */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $libelleAbrege = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateFin = null;

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

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getLibelleAbrege(): ?string
    {
        return $this->libelleAbrege;
    }

    public function setLibelleAbrege(?string $libelleAbrege): static
    {
        $this->libelleAbrege = $libelleAbrege;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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
}
