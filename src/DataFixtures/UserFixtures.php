<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $project = $this->getReference('project_main', Project::class);

        $user = (new User())
            ->setId(Uuid::v4())
            ->setProject($project)
            ->setName("Dupont")
            ->setSecondName("Jean")
            ->setEmail("jean.dupont@example.com")
            ->setPassword("password");

        $manager->persist($user);

        $this->addReference('user_main', $user);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class,
        ];
    }
}
