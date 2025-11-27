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
        $users = [
            $this->getReference('user_0', User::class),
            $this->getReference('user_1', User::class),
            $this->getReference('user_2', User::class),
            $this->getReference('user_3', User::class),
            $this->getReference('user_4', User::class),
        ];

        // Noms de milestones variés par type de projet
        $milestoneTemplates = [
            // Projet Alpha - Développement web
            ['Conception & Design', 'Développement Backend', 'Frontend & Tests'],
            // Projet Beta - Application mobile
            ['Research & Planning', 'MVP Development', 'Launch & Marketing'],
            // Projet Gamma - Infrastructure
            ['Infrastructure Setup', 'Migration & Testing', 'Go-Live & Monitoring'],
            // Projet Delta - Data Analytics
            ['Data Collection', 'Analysis & Modeling', 'Reporting & Insights'],
            // Projet Epsilon - E-commerce
            ['Catalog Setup', 'Payment Integration', 'User Experience & SEO'],
        ];

        // Créer 3 milestones pour chacun des 5 projets
        for ($i = 0; $i < 5; $i++) {
            $project = $this->getReference('project_' . $i, Project::class);

            for ($j = 0; $j < 3; $j++) {
                // Chaque milestone a un manager différent
                $managerUser = $users[($i * 3 + $j) % count($users)];
                $dayOffset = $i * 90 + $j * 30;

                $milestone = (new Milestone())
                    ->setId(Uuid::v4())
                    ->setLabel($milestoneTemplates[$i][$j])
                    ->setProject($project)
                    ->setManager($managerUser)
                    ->setPlannedStartDate(new DateTimeImmutable('+'.$dayOffset.' days'))
                    ->setActualStartDate(new DateTimeImmutable('+'.($dayOffset + rand(1, 5)).' days'));

                $manager->persist($milestone);
                $this->addReference('milestone_' . $i . '_' . $j, $milestone);

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