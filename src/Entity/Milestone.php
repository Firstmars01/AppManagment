<?php

namespace App\Entity;

use AllowDynamicProperties;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\MilestoneRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[AllowDynamicProperties]
#[ORM\Entity(repositoryClass: MilestoneRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['milestone:read', 'milestone:detail']]),
        new GetCollection(normalizationContext: ['groups' => ['milestone:read']])
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['project' => 'exact'])]
class Milestone
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['milestone:read', 'project:detail', 'task:read'])]
    private ?Uuid $id;

    #[ORM\ManyToOne(inversedBy: 'milestones')]
    #[Groups(['milestone:read'])]
    private ?Project $project = null;

    #[ORM\Column(length: 255)]
    #[Groups(['milestone:read'])]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'milestones')]
    #[Groups(['milestone:read', 'milestone:detail'])]
    private ?User $manager = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['milestone:read'])]
    private ?\DateTimeInterface $plannedStartDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['milestone:read'])]
    private ?\DateTimeInterface $actualStartDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['milestone:read'])]
    private ?\DateTimeInterface $plannedEndDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['milestone:read'])]
    private ?\DateTimeInterface $actualEndDate = null;


    #[ORM\OneToMany(
        targetEntity: Task::class,
        mappedBy: 'milestone',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[Groups(['milestone:detail'])]
    private Collection $tasks;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->tasks = new ArrayCollection();

        $this->createdAt = new DateTimeImmutable();
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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getManager(): ?user
    {
        return $this->manager;
    }

    public function setManager(?user $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    public function getPlannedStartDate(): ?\DateTimeInterface
    {
        return $this->plannedStartDate;
    }

    public function setPlannedStartDate(?\DateTimeInterface $plannedStartDate): static
    {
        $this->plannedStartDate = $plannedStartDate;

        return $this;
    }

    public function getActualStartDate(): ?\DateTimeInterface
    {
        return $this->actualStartDate;
    }

    public function setActualStartDate(?\DateTimeInterface $actualStartDate): static
    {
        $this->actualStartDate = $actualStartDate;

        return $this;
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setMilestone($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getMilestone() === $this) {
                $task->setMilestone(null);
            }
        }

        return $this;
    }

    public function setId(\Symfony\Component\Uid\UuidV4 $v4): static
    {
        $this->id = $v4;
        return $this;
    }

    public function setCreatedAt(DateTimeImmutable $param): DateTimeImmutable
    {
        return $param;
    }

    public function getPlannedEndDate(): ?\DateTimeInterface
    {
        return $this->plannedEndDate;
    }

    public function setPlannedEndDate(?\DateTimeInterface $date): self
    {
        $this->plannedEndDate = $date;
        return $this;
    }

    public function getActualEndDate(): ?\DateTimeInterface
    {
        return $this->actualEndDate;
    }

    public function setActualEndDate(?\DateTimeInterface $date): self
    {
        $this->actualEndDate = $date;
        return $this;
    }


    #[Groups(['milestone:read'])]
    public function getProgress(): float
    {
        $tasks = $this->getTasks();
        $total = count($tasks);

        if ($total === 0) {
            return 0;
        }

        $sum = 0;

        foreach ($tasks as $task) {
            $taskTypeLabel = $task->getTaskType()?->getLabel();

            if ($taskTypeLabel === 'Not started') {
                $sum += 0;
            } elseif ($taskTypeLabel === 'Started but not finished') {
                $sum += 50;
            } elseif ($taskTypeLabel === 'Finished') {
                $sum += 100;
            } else {
                $sum += 0;
            }
        }

        return round($sum / $total, 2);
    }

    #[Groups(['milestone:read'])]
    public function getDelayInDays(): int
    {
        if (!$this->plannedEndDate || !$this->actualEndDate) {
            return 0;
        }

        $interval = $this->plannedEndDate->diff($this->actualEndDate);
        $days = (int)$interval->format('%r%a');

        return $days;
    }

    #[Groups(['milestone:detail'])]
    public function getTheoreticalEndDate(): ?\DateTimeInterface
    {
        $tasks = $this->getTasks();

        if ($tasks->isEmpty()) {
            return null;
        }

        $latestEndDate = null;

        foreach ($tasks as $task) {
            $startDate = $task->getActualStartDate() ?? $task->getPlannedStartDate();
            if (!$startDate) {
                continue;
            }

            $duration = $task->getDaysEstimate() ?? 0;

            $taskEndDate = (clone $startDate)->modify("+$duration days");

            if ($latestEndDate === null || $taskEndDate > $latestEndDate) {
                $latestEndDate = $taskEndDate;
            }
        }

        return $latestEndDate;
    }



}
