<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?Milestone $milestone = null;

    #[ORM\Column]
    private ?bool $isFunctional = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?User $manager = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $invitationDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $plannedStartDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $actualStartDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $daysEstimate = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $previousTask = null;

    /**
     * @var Collection<int, Requirement>
     */
    #[ORM\ManyToMany(targetEntity: Requirement::class, inversedBy: 'tasks')]
    private Collection $requirements;

    public function __construct()
    {
        $this->requirements = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getInvitationDate(): ?\DateTimeInterface
    {
        return $this->invitationDate;
    }

    public function setInvitationDate(?\DateTimeInterface $invitationDate): static
    {
        $this->invitationDate = $invitationDate;

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
}
