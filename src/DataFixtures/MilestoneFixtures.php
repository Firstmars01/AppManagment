<?php

namespace App\DataFixtures;

use App\Entity\Milestone;
use App\Entity\Project;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\Uid\Uuid;


class MilestoneFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $project = $this->getReference('project_main', Project::class);
        $managerUser = $this->getReference('user_main', User::class);

        $milestone = (new Milestone())
            ->setId(Uuid::v4())
            ->setLabel("Milestone 1")
            ->setProject($project)
            ->setManager($managerUser);

        $manager->persist($milestone);

        $this->addReference('milestone_main', $milestone);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class,
            UserFixtures::class,
        ];
    }
}
