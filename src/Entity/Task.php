<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use App\State\TaskFinishProcessor;
use App\State\TaskStartProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['task:read', 'task:detail']]),
        new GetCollection(normalizationContext: ['groups' => ['task:read']]),
        new Patch(
            uriTemplate: '/tasks/{id}/start',
            normalizationContext: ['groups' => ['task:read']],
            processor: TaskStartProcessor::class
        ),
        new Patch(
            uriTemplate: '/tasks/{id}/finish',
            normalizationContext: ['groups' => ['task:read']],
            processor: TaskFinishProcessor::class
        )
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['milestone' => 'exact'])]
class Task
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['task:read', 'milestone:detail', 'requirement:detail'])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Groups(['task:read'])]
    private ?Milestone $milestone = null;

    #[ORM\Column]
    #[Groups(['task:read'])]
    private ?bool $isFunctional = null;

    #[ORM\Column(length: 255)]
    #[Groups(['task:read', 'milestone:detail', 'requirement:detail'])]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['task:detail'])]
    private ?User $manager = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['task:read'])]
    private ?\DateTimeImmutable $plannedStartDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['task:read'])]
    private ?\DateTimeImmutable $actualStartDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['task:read'])]
    private ?int $daysEstimate = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['task:detail'])]
    private ?self $previousTask = null;

    /**
     * @var Collection<int, Requirement>
     */
    #[ORM\ManyToMany(targetEntity: Requirement::class, inversedBy: 'tasks')]
    #[ORM\JoinTable(name: 'task_requirement')]
    #[Groups(['task:detail'])]
    private Collection $requirements;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['task:read', 'milestone:detail'])]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: TaskType::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: 'task_type_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups(['task:read'])]
    private ?TaskType $taskType = null;

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

    public function getMilestone(): ?Milestone
    {
        return $this->milestone;
    }

    public function setMilestone(?Milestone $milestone): static
    {
        $this->milestone = $milestone;
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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): static
    {
        $this->manager = $manager;
        return $this;
    }

    public function getPlannedStartDate(): ?\DateTimeImmutable
    {
        return $this->plannedStartDate;
    }

    public function setPlannedStartDate(?\DateTimeImmutable $plannedStartDate): static
    {
        $this->plannedStartDate = $plannedStartDate;
        return $this;
    }

    public function getActualStartDate(): ?\DateTimeImmutable
    {
        return $this->actualStartDate;
    }

    public function setActualStartDate(?\DateTimeImmutable $actualStartDate): static
    {
        $this->actualStartDate = $actualStartDate;
        return $this;
    }

    public function getDaysEstimate(): ?int
    {
        return $this->daysEstimate;
    }

    public function setDaysEstimate(?int $daysEstimate): static
    {
        $this->daysEstimate = $daysEstimate;
        return $this;
    }

    public function getPreviousTask(): ?self
    {
        return $this->previousTask;
    }

    public function setPreviousTask(?self $previousTask): static
    {
        $this->previousTask = $previousTask;
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
        }
        return $this;
    }

    public function removeRequirement(Requirement $requirement): static
    {
        $this->requirements->removeElement($requirement);
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

    public function getTaskType(): ?TaskType
    {
        return $this->taskType;
    }

    public function setTaskType(?TaskType $taskType): static
    {
        $this->taskType = $taskType;

        return $this;
    }



}