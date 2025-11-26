<?php

namespace App\DataFixtures;

use App\Entity\Milestone;
use App\Entity\Project;
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


        $task = (new Task())
            ->setId(Uuid::v4())
            ->setLabel("Analyser les besoins")
            ->setProject($project)
            ->setMilestone($milestone)
            ->setManager($managerUser)
            ->setIsFunctional(true);

        $manager->persist($task);

        $this->addReference('task_main', $task);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class,
            MilestoneFixtures::class,
            UserFixtures::class,
        ];
    }
}
