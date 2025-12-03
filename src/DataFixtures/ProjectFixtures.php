<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\Uid\Uuid;

class ProjectFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $projectNames = [
            'Projet Alpha',
            'Projet Beta',
            'Projet Gamma',
            'Projet Delta',
            'Projet Epsilon',
        ];

        foreach ($projectNames as $index => $name) {
            /** @var User $owner */
            $owner = $this->getReference('user_' . $index, User::class);

            $project = new Project();
            $project->setId(Uuid::v4())
                ->setName($name)
                ->setOwner($owner);

            $manager->persist($project);

            $this->addReference('project_' . $index, $project);

            if ($index === 0) {
                $this->addReference('project_main', $project);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
