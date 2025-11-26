<?php

namespace App\Entity;

use App\Repository\RequirementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RequirementRepository::class)]
class Requirement
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id;

    #[ORM\ManyToOne(inversedBy: 'requirements')]
    private ?Project $project = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isFunctional = null;

    #[ORM\ManyToOne(inversedBy: 'requirements')]
    private ?RequirementType $requirementType = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\ManyToMany(targetEntity: Task::class, mappedBy: 'requirements')]
    private Collection $tasks;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->tasks = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
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

    public function isFunctional(): ?bool
    {
        return $this->isFunctional;
    }

    public function setIsFunctional(bool $isFunctional): static
    {
        $this->isFunctional = $isFunctional;

        return $this;
    }

    public function getRequirementType(): ?RequirementType
    {
        return $this->requirementType;
    }

    public function setRequirementType(?RequirementType $requirementType): static
    {
        $this->requirementType = $requirementType;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->addRequirement($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            $task->removeRequirement($this);
        }

        return $this;
    }

    public function setId(\Symfony\Component\Uid\UuidV4 $v4): static
    {
        $this->id = $v4;
        return $this;
    }
}
