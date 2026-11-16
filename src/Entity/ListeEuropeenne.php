<?php

namespace App\Entity;

use App\Repository\ListeEuropeenneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Une liste candidate aux élections européennes : son nom, sa tête de liste et
 * le parti qui la porte.
 *
 * Sans elle, {@see ResultatEuropeenne} n'est qu'une suite de numéros — le
 * scrutin européen étant à la proportionnelle de liste, c'est ici que se lit ce
 * pour quoi une commune a voté.
 *
 * La clé est le couple année + numéro, et non la colonne `id_election` de la
 * source : celle-ci vaut 0 pour 2019 et 5 pour 2024, sans rien désigner de
 * cohérent.
 */
#[ORM\Entity(repositoryClass: ListeEuropeenneRepository::class)]
#[ORM\Table(name: 'liste_europeenne')]
#[ORM\UniqueConstraint(name: 'uniq_liste_europeenne', columns: ['annee', 'numero'])]
class ListeEuropeenne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $annee = null;

    /** Numéro de la liste dans le scrutin, qui la relie à ses résultats. */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $numero = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $teteDeListe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $parti = null;

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

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

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

    public function getTeteDeListe(): ?string
    {
        return $this->teteDeListe;
    }

    public function setTeteDeListe(?string $teteDeListe): static
    {
        $this->teteDeListe = $teteDeListe;

        return $this;
    }

    public function getParti(): ?string
    {
        return $this->parti;
    }

    public function setParti(?string $parti): static
    {
        $this->parti = $parti;

        return $this;
    }
}
