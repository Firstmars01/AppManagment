<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\RequirementRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;

#[ORM\Entity(repositoryClass: RequirementRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['requirement:read', 'requirement:detail']]),
        new GetCollection(normalizationContext: ['groups' => ['requirement:read']])
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['project' => 'exact'])]
class Requirement
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['requirement:read', 'task:detail'])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'requirements')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Groups(['requirement:read'])]
    private ?Project $project = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['requirement:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['requirement:read'])]
    private ?bool $isFunctional = null;

    #[ORM\ManyToOne(inversedBy: 'requirements')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['requirement:read', 'requirement:detail'])]
    private ?RequirementType $requirementType = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['requirement:read'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['requirement:read'])]
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\ManyToMany(targetEntity: Task::class, mappedBy: 'requirements')]
    #[Groups(['requirement:detail'])]
    private Collection $tasks;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->tasks = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
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

    public function getIsFunctional(): ?bool
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

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
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

    #[ORM\PrePersist]
    public function setCreationDate(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdateDate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[Groups(['requirement:read'])]
    public function isCompleted(): bool
    {
        $tasks = $this->getTasks();

        if ($tasks->isEmpty()) {
            return false;
        }

        foreach ($tasks as $task) {
            if ($task->getTaskType()?->getLabel() !== 'Finished') {
                return false;
            }
        }

        return true;
    }

}