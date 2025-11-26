<?php

namespace App\Entity;

use App\Repository\RequirementTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RequirementTypeRepository::class)]
class RequirementType
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    /**
     * @var Collection<int, Requirement>
     */
    #[ORM\OneToMany(targetEntity: Requirement::class, mappedBy: 'requirementType')]
    private Collection $requirements;

    public function __construct()
    {
        $this->requirements = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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
            // set the owning side to null (unless already changed)
            if ($requirement->getRequirementType() === $this) {
                $requirement->setRequirementType(null);
            }
        }

        return $this;
    }
}
