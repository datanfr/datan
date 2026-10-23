<?php

namespace App\Entity;

use App\Repository\ResultatElectoralRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Score d'une liste aux élections européennes dans une commune, pour 2019 et
 * 2024.
 *
 * **C'est une part, pas un nombre de voix.** La source ne publie que le
 * pourcentage des suffrages exprimés, et rien ne permet de remonter aux voix :
 * ni les inscrits, ni les votants, ni les exprimés n'y figurent. La page ne peut
 * donc afficher que des parts — ce qu'elle fait.
 *
 * C'est aussi ce qui rend les 2,5 millions de lignes moins lourdes qu'il n'y
 * paraît : ce sont des agrégats déjà calculés, une trentaine de listes par
 * commune et par scrutin, pas un dépouillement.
 *
 * Le numéro de liste renvoie à {@see ListeEuropeenne} par le couple année +
 * numéro. Le rapprochement n'est pas une clé étrangère : les résultats
 * s'importent par blocs de plusieurs centaines de milliers de lignes, et une
 * contrainte référentielle sur chacune coûterait plus que ce qu'elle protège.
 */
#[ORM\Entity(repositoryClass: ResultatElectoralRepository::class)]
#[ORM\Table(name: 'resultat_europeenne')]
#[ORM\UniqueConstraint(name: 'uniq_europeenne', columns: ['annee', 'code_insee', 'numero_liste'])]
#[ORM\Index(name: 'idx_europeenne_commune', columns: ['code_insee', 'annee'])]
class ResultatEuropeenne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $annee = null;

    #[ORM\Column(length: 6)]
    private ?string $codeInsee = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $numeroListe = null;

    /** Part des suffrages exprimés, en points de pourcentage. */
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $part = null;

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

    public function getNumeroListe(): ?int
    {
        return $this->numeroListe;
    }

    public function setNumeroListe(int $numeroListe): static
    {
        $this->numeroListe = $numeroListe;

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
}
