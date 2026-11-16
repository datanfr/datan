<?php

namespace App\Entity;

use App\Repository\OrganeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Organe de l'Assemblée autre que ceux déjà portés par une table dédiée.
 *
 * Les groupes politiques vivent dans {@see Groupe}, les partis dans {@see Parti},
 * les commissions permanentes dans {@see Commission} : cette table reçoit les
 * organes restants que le site expose, à ce jour les seules **délégations du
 * Bureau** (`codeType = DELEGBUREAU`). Le legacy ne retient d'ailleurs, dans sa
 * table `mandat_secondaire`, que trois types d'organe — COMPER, DELEGBUREAU et
 * PARPOL (`daily.php:473`) ; les deux premiers ont déjà leur table chez nous, le
 * dernier vit sur `depute.parti_id`. DELEGBUREAU était le seul sans foyer.
 *
 * **Clé métier : l'uid** (« PO849048 »), jamais le libellé. Deux organes peuvent
 * porter le même nom à des législatures différentes (CLAUDE.md, piège des
 * homonymes) ; s'appuyer sur l'uid, toujours unique, écarte d'emblée l'écrasement
 * silencieux d'un organe par son homonyme. Pas de slug, donc : aucune adresse
 * publique ne dérive de cette table, c'est un référentiel interne de libellés.
 *
 * Pas de `#[ApiResource]` : l'API publique est en lecture seule et n'a pas
 * besoin de ce référentiel.
 */
#[ORM\Entity(repositoryClass: OrganeRepository::class)]
#[ORM\Table(name: 'organe')]
class Organe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Identifiant Assemblée nationale de l'organe (« PO849048 »). */
    #[ORM\Column(length: 50, unique: true)]
    private ?string $uid = null;

    /** Nomenclature de l'Assemblée : « DELEGBUREAU », « GA », « MISINFO »… */
    #[ORM\Column(length: 25)]
    private ?string $codeType = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /** Forme courte, quand la source en fournit une. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelleAbrege = null;

    /** Acronyme (« FIN », « CION-LOIS »), tel que le site l'affiche à l'occasion. */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $libelleAbrev = null;

    /** Certains organes sont propres à une législature (DELEGBUREAU), d'autres non. */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $legislature = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateDebut = null;

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

    public function getCodeType(): ?string
    {
        return $this->codeType;
    }

    public function setCodeType(string $codeType): static
    {
        $this->codeType = $codeType;

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

    public function getLibelleAbrev(): ?string
    {
        return $this->libelleAbrev;
    }

    public function setLibelleAbrev(?string $libelleAbrev): static
    {
        $this->libelleAbrev = $libelleAbrev;

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
}
