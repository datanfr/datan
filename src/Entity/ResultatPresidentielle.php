<?php

namespace App\Entity;

use App\Repository\ResultatElectoralRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Résultat d'un candidat à l'élection présidentielle dans une commune.
 *
 * **Second tour seulement**, pour 2017 et 2022 : la table d'origine s'appelle
 * `elect_pres_2` et ne porte que le duel final. Le premier tour n'a jamais été
 * repris, et la page ville affiche bien « Résultat du 2ᵈ tour » en sous-titre.
 *
 * Le code INSEE n'est pas dans la source : elle range ses lignes par département
 * et par numéro de commune, qu'il faut recomposer. Voir
 * {@see \App\Command\ImportResultatsElectorauxCommand::insee()}, où la règle et
 * ses exceptions sont écrites.
 */
#[ORM\Entity(repositoryClass: ResultatElectoralRepository::class)]
#[ORM\Table(name: 'resultat_presidentielle')]
#[ORM\UniqueConstraint(name: 'uniq_presidentielle', columns: ['annee', 'code_insee', 'candidat'])]
#[ORM\Index(name: 'idx_presidentielle_commune', columns: ['code_insee', 'annee'])]
class ResultatPresidentielle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $annee = null;

    #[ORM\Column(length: 6)]
    private ?string $codeInsee = null;

    /** Nom du candidat, tel que la source l'écrit : « Macron », « Le Pen ». */
    #[ORM\Column(length: 25)]
    private ?string $candidat = null;

    #[ORM\Column(nullable: true)]
    private ?int $voix = null;

    /** Part des suffrages exprimés, en points de pourcentage. */
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $part = null;

    #[ORM\Column(nullable: true)]
    private ?int $votants = null;

    /** Part d'abstention dans la commune, en points de pourcentage. */
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $abstentionPart = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCodeInsee(): ?string
    {
        return $this->codeInsee;
    }

    public function setCodeInsee(string $codeInsee): static
    {
        $this->codeInsee = $codeInsee;

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

    public function getVoix(): ?int
    {
        return $this->voix;
    }

    public function setVoix(?int $voix): static
    {
        $this->voix = $voix;

        return $this;
    }

    public function getPart(): ?string
    {
        return $this->part;
    }

    public function setPart(?string $part): static
    {
        $this->part = $part;

        return $this;
    }

    public function getVotants(): ?int
    {
        return $this->votants;
    }

    public function setVotants(?int $votants): static
    {
        $this->votants = $votants;

        return $this;
    }

    public function getAbstentionPart(): ?string
    {
        return $this->abstentionPart;
    }

    public function setAbstentionPart(?string $abstentionPart): static
    {
        $this->abstentionPart = $abstentionPart;

        return $this;
    }
}
