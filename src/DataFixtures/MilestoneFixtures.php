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
        // Récupérer le propriétaire des milestones
        $managerUser = $this->getReference('user_main', User::class);

        // Boucle sur tous les projets créés
        for ($i = 0; $i < 5; $i++) {
            $project = $this->getReference('project_' . $i, Project::class);

            // Créer 3 jalons par projet
            for ($j = 0; $j < 3; $j++) {
                $milestone = (new Milestone())
                    ->setId(Uuid::v4())
                    ->setLabel("Milestone " . ($j + 1) . " - Projet " . ($i + 1))
                    ->setProject($project)
                    ->setManager($managerUser)
                    ->setPlannedStartDate(new DateTimeImmutable('+'.($j*5).' days'))
                    ->setActualStartDate(new DateTimeImmutable('+'.($j*5 + 1).' days')) // juste un exemple
                ;

                $manager->persist($milestone);

                // Ajouter une référence unique pour chaque jalon si nécessaire
                $this->addReference('milestone_' . $i . '_' . $j, $milestone);

                // Garder une référence principale pour compatibilité
                if ($i === 0 && $j === 0) {
                    $this->addReference('milestone_main', $milestone);
                }
            }
        }

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
