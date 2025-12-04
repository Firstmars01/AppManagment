<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;


#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('slug')]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['project:read', 'project:detail']]),
        new GetCollection(normalizationContext: ['groups' => ['project:read']])
    ]
)]
class Project
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['project:read', 'milestone:read', 'requirement:read'])]
    private ?Uuid $id;

    #[ORM\Column(length: 255)]
    #[Groups(['project:read', 'milestone:read', 'requirement:read'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'ownedProjects')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['project:detail'])]
    private ?User $owner = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['project:read'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['project:read'])]
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
    #[Groups(['project:detail'])]
    private Collection $requirements;

    /**
     * @var Collection<int, Milestone>
     */
    #[ORM\OneToMany(targetEntity: Milestone::class,mappedBy: 'project',cascade: ['persist', 'remove'],orphanRemoval: true)]
    #[Groups(['project:detail'])]
    private Collection $milestones;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(['project:read'])]
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

    #[Groups(['project:read'])]
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

    #[Groups(['project:read'])]
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

    #[Groups(['project:detail'])]
    public function getTheoreticalEndDate(): ?\DateTimeInterface
    {
        $milestones = $this->getMilestones();

        if ($milestones->isEmpty()) {
            return null;
        }

        $latestEndDate = null;

        foreach ($milestones as $milestone) {
            $milestoneEnd = $milestone->getTheoreticalEndDate();

            if ($milestoneEnd === null) {
                continue;
            }

            if ($latestEndDate === null || $milestoneEnd > $latestEndDate) {
                $latestEndDate = $milestoneEnd;
            }
        }

        return $latestEndDate;
    }




}