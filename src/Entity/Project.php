<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('slug')]
class Project
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'ownedProjects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Requirement>
     */
    #[ORM\OneToMany(
        targetEntity: Requirement::class,
        mappedBy: 'project',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $requirements;

    /**
     * @var Collection<int, Milestone>
     */
    #[ORM\OneToMany(targetEntity: Milestone::class,mappedBy: 'project',cascade: ['persist', 'remove'],orphanRemoval: true)]
    private Collection $milestones;


    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $slug;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->requirements = new ArrayCollection();
        $this->milestones = new ArrayCollection();
        $this->slug = '';
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;
        return $this;
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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
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
            $requirement->setProject($this);
        }
        return $this;
    }

    public function removeRequirement(Requirement $requirement): static
    {
        if ($this->requirements->removeElement($requirement)) {
            if ($requirement->getProject() === $this) {
                $requirement->setProject(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Milestone>
     */
    public function getMilestones(): Collection
    {
        return $this->milestones;
    }

    public function addMilestone(Milestone $milestone): static
    {
        if (!$this->milestones->contains($milestone)) {
            $this->milestones->add($milestone);
            $milestone->setProject($this);
        }
        return $this;
    }

    public function removeMilestone(Milestone $milestone): static
    {
        if ($this->milestones->removeElement($milestone)) {
            if ($milestone->getProject() === $this) {
                $milestone->setProject(null);
            }
        }
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function computeSlug(SluggerInterface $slugger)
    {
        if (!$this->slug || '-' === $this->slug) {
            $this->slug = strtolower($slugger->slug($this->name));
        }
    }

    /**
     * Get all tasks from all milestones of this project
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        $tasks = new ArrayCollection();

        foreach ($this->milestones as $milestone) {
            foreach ($milestone->getTasks() as $task) {
                if (!$tasks->contains($task)) {
                    $tasks->add($task);
                }
            }
        }

        return $tasks;
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

    public function getProgress(): int
    {
        $milestones = $this->getMilestones();

        if ($milestones->isEmpty()) {
            return 0;
        }

        $totalProgress = 0;

        foreach ($milestones as $milestone) {
            $totalProgress += $milestone->getProgress();
        }

        return (int) round($totalProgress / $milestones->count());
    }

    public function getRequirementsCoverage(): int
    {
        $requirements = $this->getRequirements();
        $total = count($requirements);

        if ($total === 0) {
            return 0;
        }

        $completed = 0;

        foreach ($requirements as $req) {
            if ($req->isCompleted()) {
                $completed++;
            }
        }

        return (int) floor(($completed / $total) * 100);
    }

    public function getTheoreticalEndDate(): ?\DateTimeInterface
    {
        $milestones = $this->getMilestones();

        if ($milestones->isEmpty()) {
            return null;
        }

        // 1) Trouver le jalon qui a la dernière date de fin prévue
        $lastMilestone = null;

        foreach ($milestones as $m) {
            if (!$m->getPlannedEndDate()) {
                continue;
            }

            if ($lastMilestone === null ||
                $m->getPlannedEndDate() > $lastMilestone->getPlannedEndDate())
            {
                $lastMilestone = $m;
            }
        }

        if (!$lastMilestone) {
            return null;
        }

        // Base = date du dernier jalon
        $finalDate = (clone $lastMilestone->getPlannedEndDate());

        // 2) Ajouter/soustraire la somme des décalages des jalons terminés
        $totalDelay = 0;

        foreach ($milestones as $milestone) {
            if ($milestone->getProgress() === 100) {
                $totalDelay += $milestone->getDelayInDays();
            }
        }

        if ($totalDelay !== 0) {
            $finalDate->modify(($totalDelay > 0 ? '+' : '') . $totalDelay . ' days');
        }

        return $finalDate;
    }



}