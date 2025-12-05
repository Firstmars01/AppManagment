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
        $projects = [];
        for ($i = 0; $i < 5; $i++) {
            $projects[] = $this->getReference('project_' . $i, Project::class);
        }

        $typeDonnees = $this->getReference('requirement_type_data', RequirementType::class);
        $typePerformances = $this->getReference('requirement_type_performances', RequirementType::class);

        foreach ($projects as $i => $project) {
            for ($j = 1; $j <= 3; $j++) {
                $req = new Requirement();
                $req->setId(Uuid::v4())
                    ->setDescription("Requirement {$j} for project " . $project->getName())
                    ->setProject($project)
                    ->setRequirementType($j % 2 === 0 ? $typePerformances : $typeDonnees)
                    ->setIsFunctional($j % 2 === 1)
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setUpdatedAt(new DateTimeImmutable());

                $manager->persist($req);

                $this->addReference('requirement_' . $i . '_' . $j, $req);
            }
        }

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
