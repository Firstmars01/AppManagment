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
        $typeDonnees = $this->getReference('requirement_type_donnees', RequirementType::class);
        $typePerformances = $this->getReference('requirement_type_performances', RequirementType::class);

        // Requirement 1 - Données
        $req1 = (new Requirement())
            ->setId(Uuid::v4())
            ->setDescription("Le système doit gérer les données utilisateurs de manière sécurisée")
            ->setProject($project)
            ->setRequirementType($typeDonnees)
            ->setIsFunctional(true)
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());

        $manager->persist($req1);
        $this->addReference('requirement_1', $req1);

        // Requirement 2 - Performances
        $req2 = (new Requirement())
            ->setId(Uuid::v4())
            ->setDescription("Le système doit répondre en moins de 2 secondes pour 95% des requêtes")
            ->setProject($project)
            ->setRequirementType($typePerformances)
            ->setIsFunctional(false)
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());

        $manager->persist($req2);
        $this->addReference('requirement_2', $req2);

        // Requirement 3 - Données
        $req3 = (new Requirement())
            ->setId(Uuid::v4())
            ->setDescription("Le système doit permettre l'export des données au format CSV et JSON")
            ->setProject($project)
            ->setRequirementType($typeDonnees)
            ->setIsFunctional(true)
            ->setCreatedAt(new DateTimeImmutable())
            ->setUpdatedAt(new DateTimeImmutable());

        $manager->persist($req3);
        $this->addReference('requirement_3', $req3);

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