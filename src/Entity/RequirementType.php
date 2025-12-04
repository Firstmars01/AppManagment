<?php

namespace App\Entity;

use App\Repository\RequirementTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RequirementTypeRepository::class)]
class RequirementType
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['requirement:detail'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['requirement:detail'])]
    private ?string $label = null;

    /**
     * @var Collection<int, Requirement>
     */
    #[ORM\OneToMany(targetEntity: Requirement::class, mappedBy: 'requirementType')]
    private Collection $requirements;
    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->requirements = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return Collection<int, Requirement>
     */
    public function getRequirements(): Collection
    {
        return $this->requirements;
    }

    public function addRequirement(Requirement $requirement): static
    {
        if (!$this->requirements->contains($requirement)) {
            $this->requirements->add($requirement);
            $requirement->setRequirementType($this);
        }
        return $this;
    }

    public function removeRequirement(Requirement $requirement): static
    {
        if ($this->requirements->removeElement($requirement)) {
            if ($requirement->getRequirementType() === $this) {
                $requirement->setRequirementType(null);
            }
        }
        return $this;
    }
}