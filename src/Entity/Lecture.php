<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\LectureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Lecture (étape de la navette parlementaire) d'un décryptage (table « readings » de l'app d'origine).
 */
#[ORM\Entity(repositoryClass: LectureRepository::class)]
#[ORM\Table(name: 'lecture')]
#[ApiResource(
    // API publique en lecture seule. Sans cette liste, API Platform expose
    // aussi POST, PATCH et DELETE, sans exiger la moindre authentification :
    // un anonyme pouvait écrire en base. Les écritures passent par l'espace
    // de rédaction et par les commandes d'import, jamais par l'API.
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['lecture:read']],
)]
class Lecture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['lecture:read', 'decryptage:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['lecture:read', 'lecture:write', 'decryptage:read'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Decryptage>
     */
    #[ORM\OneToMany(targetEntity: Decryptage::class, mappedBy: 'lecture')]
    private Collection $decryptages;

    public function __construct()
    {
        $this->decryptages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Decryptage>
     */
    public function getDecryptages(): Collection
    {
        return $this->decryptages;
    }

    public function addDecryptage(Decryptage $decryptage): static
    {
        if (!$this->decryptages->contains($decryptage)) {
            $this->decryptages->add($decryptage);
            $decryptage->setLecture($this);
        }

        return $this;
    }

    public function removeDecryptage(Decryptage $decryptage): static
    {
        if ($this->decryptages->removeElement($decryptage)) {
            if ($decryptage->getLecture() === $this) {
                $decryptage->setLecture(null);
            }
        }

        return $this;
    }
}
