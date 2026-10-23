<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Une intervention dans un compte rendu de séance : un morceau de discours.
 *
 * C'est la matière première du brouillon de décryptage par IA : le texte des
 * interventions autour d'un vote fournit les arguments et les citations — et
 * la citation générée se vérifie contre `texte` avant d'être présentée.
 *
 * Les didascalies (« Applaudissements sur les bancs… ») sont des paroles sans
 * orateur : elles se gardent, le compte rendu perdrait son fil sans elles.
 *
 * `acteur_ref` porte la référence Assemblée (« PA721908 ») ; `depute_id` la
 * résolution vers notre table quand l'orateur est un député — un ministre ou
 * un fonctionnaire de séance n'en a pas.
 */
#[ORM\Entity]
#[ORM\Table(name: 'cr_parole')]
#[ORM\Index(name: 'idx_cr_parole_cr_ordre', columns: ['compte_rendu_id', 'ordre_absolu_seance'])]
#[ORM\Index(name: 'idx_cr_parole_section', columns: ['compte_rendu_id', 'section_ordre'])]
class CrParole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $compteRenduId = null;

    /**
     * `ordre_absolu_seance` de la section ({@see CrSection}) qui contient la
     * parole — le lien par ordre plutôt que par id permet l'insertion par lots.
     */
    #[ORM\Column(nullable: true)]
    private ?int $sectionOrdre = null;

    #[ORM\Column]
    private ?int $ordreAbsoluSeance = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $codeGrammaire = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $codeStyle = null;

    /** « president » quand la présidence de séance parle, NULL sinon. */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $roleDebat = null;

    /** Nom tel qu'imprimé (« Mme la présidente », « M. Gabriel Attal »). */
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $orateurNom = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $acteurRef = null;

    #[ORM\Column(nullable: true)]
    private ?int $deputeId = null;

    /**
     * MEDIUMTEXT : une présentation de ministre dépasse parfois les 64 Ko
     * d'un TEXT une fois l'HTML du compte rendu conservé.
     */
    #[ORM\Column(type: Types::TEXT, length: 16777215)]
    private ?string $texte = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompteRenduId(): ?int
    {
        return $this->compteRenduId;
    }

    public function setCompteRenduId(int $compteRenduId): static
    {
        $this->compteRenduId = $compteRenduId;

        return $this;
    }

    public function getSectionOrdre(): ?int
    {
        return $this->sectionOrdre;
    }

    public function setSectionOrdre(?int $sectionOrdre): static
    {
        $this->sectionOrdre = $sectionOrdre;

        return $this;
    }

    public function getOrdreAbsoluSeance(): ?int
    {
        return $this->ordreAbsoluSeance;
    }

    public function setOrdreAbsoluSeance(int $ordreAbsoluSeance): static
    {
        $this->ordreAbsoluSeance = $ordreAbsoluSeance;

        return $this;
    }

    public function getCodeGrammaire(): ?string
    {
        return $this->codeGrammaire;
    }

    public function setCodeGrammaire(?string $codeGrammaire): static
    {
        $this->codeGrammaire = $codeGrammaire;

        return $this;
    }

    public function getCodeStyle(): ?string
    {
        return $this->codeStyle;
    }

    public function setCodeStyle(?string $codeStyle): static
    {
        $this->codeStyle = $codeStyle;

        return $this;
    }

    public function getRoleDebat(): ?string
    {
        return $this->roleDebat;
    }

    public function setRoleDebat(?string $roleDebat): static
    {
        $this->roleDebat = $roleDebat;

        return $this;
    }

    public function getOrateurNom(): ?string
    {
        return $this->orateurNom;
    }

    public function setOrateurNom(?string $orateurNom): static
    {
        $this->orateurNom = $orateurNom;

        return $this;
    }

    public function getActeurRef(): ?string
    {
        return $this->acteurRef;
    }

    public function setActeurRef(?string $acteurRef): static
    {
        $this->acteurRef = $acteurRef;

        return $this;
    }

    public function getDeputeId(): ?int
    {
        return $this->deputeId;
    }

    public function setDeputeId(?int $deputeId): static
    {
        $this->deputeId = $deputeId;

        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): static
    {
        $this->texte = $texte;

        return $this;
    }
}
