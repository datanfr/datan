<?php

namespace App\Entity;

use App\Repository\CommuneCirconscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Rattachement d'une commune à une circonscription législative.
 *
 * Table à part et non colonne de {@see Commune} : le découpage n'épouse pas les
 * limites communales dans les grandes villes, et la relation se lit dans les
 * deux sens — les circonscriptions d'une commune, mais aussi les communes d'une
 * circonscription.
 */
#[ORM\Entity(repositoryClass: CommuneCirconscriptionRepository::class)]
#[ORM\Table(name: 'commune_circonscription')]
#[ORM\UniqueConstraint(name: 'uniq_commune_circonscription', columns: ['commune_id', 'circonscription'])]
class CommuneCirconscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'circonscriptions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Commune $commune = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $circonscription = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommune(): ?Commune
    {
        return $this->commune;
    }

    public function setCommune(?Commune $commune): static
    {
        $this->commune = $commune;

        return $this;
    }

    public function getCirconscription(): ?int
    {
        return $this->circonscription;
    }

    public function setCirconscription(int $circonscription): static
    {
        $this->circonscription = $circonscription;

        return $this;
    }
}
