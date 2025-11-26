<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\Requirement;
use App\Entity\RequirementType;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\Uid\Uuid;


class RequirementFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $project = $this->getReference('project_main', Project::class);

        $type = $this->getReference('requirement_type_donnees', RequirementType::class);

        $req = (new Requirement())
            ->setId(Uuid::v4())
            ->setDescription("Le système doit gérer les données utilisateurs")
            ->setProject($project)
            ->setRequirementType($type)
            ->setIsFunctional(true)
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());

        $manager->persist($req);

        $this->addReference('requirement_main', $req);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RequirementTypeFixtures::class,
            ProjectFixtures::class,
        ];
    }
}
