<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $id = null;


    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Milestone $milestone = null;

    #[ORM\Column]
    private ?bool $isFunctional = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $manager = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $plannedStartDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $actualStartDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $daysEstimate = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?self $previousTask = null;

    /**
     * @var Collection<int, Requirement>
     */
    #[ORM\ManyToMany(targetEntity: Requirement::class, inversedBy: 'tasks')]
    #[ORM\JoinTable(name: 'task_requirement')]
    private Collection $requirements;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

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
}