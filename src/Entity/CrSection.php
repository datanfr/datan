<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Section d'un compte rendu de séance : un « point » de l'ordre du jour, avec
 * sa hiérarchie (`nivpoint`) — discussion d'un texte, discussion générale,
 * explications de vote, vote…
 *
 * La hiérarchie sert à retrouver la discussion qui précède un vote : la parole
 * « Voici le résultat du scrutin » désigne une section, et le débat vit dans
 * cette section et dans sa sœur immédiatement précédente sous le même parent.
 *
 * Colonnes entières nues plutôt que relations ORM : la table se remplit par
 * lots en DBAL (plusieurs centaines de sections par séance) et se lit de même.
 * Le lien de parenté passe par `ordre_absolu_seance` du parent, pas par un id
 * auto-incrémenté : les lots s'insèrent alors d'un bloc, sans aller-retour
 * pour récupérer les identifiants générés.
 */
#[ORM\Entity]
#[ORM\Table(name: 'cr_section')]
#[ORM\Index(name: 'idx_cr_section_cr_ordre', columns: ['compte_rendu_id', 'ordre_absolu_seance'])]
class CrSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $compteRenduId = null;

    /** `ordre_absolu_seance` de la section parente, NULL à la racine. */
    #[ORM\Column(nullable: true)]
    private ?int $parentOrdre = null;

    /** Position dans la séance, tous types d'éléments confondus. */
    #[ORM\Column]
    private ?int $ordreAbsoluSeance = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $nivpoint = null;

    #[ORM\Column(nullable: true)]
    private ?int $valeurPtsodj = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $codeGrammaire = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $titre = null;

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

    public function getParentOrdre(): ?int
    {
        return $this->parentOrdre;
    }

    public function setParentOrdre(?int $parentOrdre): static
    {
        $this->parentOrdre = $parentOrdre;

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

    public function getNivpoint(): ?int
    {
        return $this->nivpoint;
    }

    public function setNivpoint(?int $nivpoint): static
    {
        $this->nivpoint = $nivpoint;

        return $this;
    }

    public function getValeurPtsodj(): ?int
    {
        return $this->valeurPtsodj;
    }

    public function setValeurPtsodj(?int $valeurPtsodj): static
    {
        $this->valeurPtsodj = $valeurPtsodj;

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

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }
}
