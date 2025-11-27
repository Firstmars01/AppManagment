<?php

namespace App\DataFixtures;

use App\Entity\Milestone;
use App\Entity\Project;
use App\Entity\Requirement;
use App\Entity\Task;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\Uid\Uuid;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $project = $this->getReference('project_main', Project::class);
        $milestone = $this->getReference('milestone_main', Milestone::class);
        $managerUser = $this->getReference('user_main', User::class);
        $user1 = $this->getReference('user_1', User::class);

        $requirement1 = $this->getReference('requirement_1', Requirement::class);
        $requirement2 = $this->getReference('requirement_2', Requirement::class);
        $requirement3 = $this->getReference('requirement_3', Requirement::class);

        $task1 = (new Task())
            ->setId(Uuid::v4())
            ->setLabel("Analyser les besoins")
            ->setProject($project)
            ->setMilestone($milestone)
            ->setManager($managerUser)
            ->setIsFunctional(true)
            ->setPlannedStartDate(new DateTimeImmutable('2025-01-15'))
            ->setActualStartDate(new DateTimeImmutable('2025-01-15'))
            ->setDaysEstimate(5);

        $task1->addRequirement($requirement1);
        $task1->addRequirement($requirement2);

        $manager->persist($task1);
        $this->addReference('task_1', $task1);

        $task2 = (new Task())
            ->setId(Uuid::v4())
            ->setLabel("Concevoir l'architecture")
            ->setProject($project)
            ->setMilestone($milestone)
            ->setManager($user1)
            ->setIsFunctional(false)
            ->setPlannedStartDate(new DateTimeImmutable('2025-01-22'))
            ->setDaysEstimate(8)
            ->setPreviousTask($task1);

        $task2->addRequirement($requirement2);

        $manager->persist($task2);
        $this->addReference('task_2', $task2);

        $task3 = (new Task())
            ->setId(Uuid::v4())
            ->setLabel("Développer les fonctionnalités d'export")
            ->setProject($project)
            ->setMilestone($milestone)
            ->setManager($managerUser)
            ->setIsFunctional(true)
            ->setPlannedStartDate(new DateTimeImmutable('2025-02-03'))
            ->setDaysEstimate(10)
            ->setPreviousTask($task2);

        $task3->addRequirement($requirement1);
        $task3->addRequirement($requirement3);

        $manager->persist($task3);
        $this->addReference('task_3', $task3);

        $task4 = (new Task())
            ->setId(Uuid::v4())
            ->setLabel("Tests de performance")
            ->setProject($project)
            ->setMilestone($milestone)
            ->setManager($user1)
            ->setIsFunctional(false)
            ->setPlannedStartDate(new DateTimeImmutable('2025-02-17'))
            ->setDaysEstimate(3)
            ->setPreviousTask($task3);

        $task4->addRequirement($requirement2);

        $manager->persist($task4);
        $this->addReference('task_4', $task4);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class,
            MilestoneFixtures::class,
            UserFixtures::class,
            RequirementFixtures::class,
        ];
    }
}