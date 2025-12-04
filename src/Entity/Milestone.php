<?php

namespace App\Entity;

use AllowDynamicProperties;
use App\Repository\MilestoneRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[AllowDynamicProperties]
#[ORM\Entity(repositoryClass: MilestoneRepository::class)]
class Milestone
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id;

    #[ORM\ManyToOne(inversedBy: 'milestones')]
    private ?Project $project = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'milestones')]
    private ?User $manager = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $plannedStartDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $actualStartDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $plannedEndDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $actualEndDate = null;


    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(
        targetEntity: Task::class,
        mappedBy: 'milestone',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
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
                $sum += 0; // sécurité si label inconnu
            }
        }

        // Retourne la moyenne arrondie
        return round($sum / $total, 2);
    }

    public function getDelayInDays(): int
    {
        if (!$this->plannedEndDate || !$this->actualEndDate) {
            return 0; // pas de décalage possible
        }

        $interval = $this->plannedEndDate->diff($this->actualEndDate);
        $days = (int)$interval->format('%r%a');  // %r = signe (+/-)

        return $days;
    }



}
