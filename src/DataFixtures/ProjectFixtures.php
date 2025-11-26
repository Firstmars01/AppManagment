<?php

namespace App\DataFixtures;

use App\Entity\Project;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class ProjectFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $project = (new Project())
            ->setId(Uuid::v4())
            ->setName('Projet Test')
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());

        $manager->persist($project);

        $this->addReference('project_main', $project);

        $manager->flush();
    }
}
