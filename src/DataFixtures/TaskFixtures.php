<?php

namespace App\DataFixtures;

use App\Entity\Milestone;
use App\Entity\Project;
use App\Entity\Requirement;
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
        $users = [
            $this->getReference('user_main', User::class),
            $this->getReference('user_1', User::class),
            $this->getReference('user_2', User::class),
            $this->getReference('user_3', User::class),
            $this->getReference('user_4', User::class),
        ];

        $requirement1 = $this->getReference('requirement_1', Requirement::class);
        $requirement2 = $this->getReference('requirement_2', Requirement::class);
        $requirement3 = $this->getReference('requirement_3', Requirement::class);

        // Tâches spécifiques par type de projet et milestone
        $tasksByProject = [
            // Projet Alpha - Développement web
            [
                ['Wireframing', 'UI/UX Design', 'Design System'],
                ['API Development', 'Database Schema', 'Authentication'],
                ['React Components', 'Integration Tests', 'Performance Optimization']
            ],
            // Projet Beta - Application mobile
            [
                ['Market Research', 'User Personas', 'Feature Planning'],
                ['Core Functionality', 'User Interface', 'Beta Testing'],
                ['App Store Setup', 'Marketing Campaign', 'Launch Event']
            ],
            // Projet Gamma - Infrastructure
            [
                ['Server Provisioning', 'Network Configuration', 'Security Setup'],
                ['Data Migration', 'Load Testing', 'Disaster Recovery'],
                ['Production Deployment', 'Monitoring Setup', 'Documentation']
            ],
            // Projet Delta - Data Analytics
            [
                ['Data Source Integration', 'ETL Pipeline', 'Data Cleaning'],
                ['Statistical Analysis', 'Machine Learning Models', 'Model Validation'],
                ['Dashboard Creation', 'KPI Tracking', 'Stakeholder Reports']
            ],
            // Projet Epsilon - E-commerce
            [
                ['Product Import', 'Category Structure', 'Inventory Management'],
                ['Stripe Integration', 'Checkout Flow', 'Order Management'],
                ['SEO Optimization', 'Mobile Responsiveness', 'Analytics Setup']
            ]
        ];

        $taskCounter = 0;

        // Pour chaque projet
        for ($i = 0; $i < 5; $i++) {
            $project = $this->getReference('project_' . $i, Project::class);

            // Pour chaque milestone du projet
            for ($j = 0; $j < 3; $j++) {
                $milestone = $this->getReference('milestone_' . $i . '_' . $j, Milestone::class);

                $previousTask = null;

                // Créer 3 tâches par milestone
                for ($k = 0; $k < 3; $k++) {
                    $managerChoice = $users[($i + $j + $k) % count($users)];
                    $dayOffset = $i * 90 + $j * 30 + $k * 10;
                    $daysEstimate = rand(3, 15);

                    $task = (new Task())
                        ->setId(Uuid::v4())
                        ->setLabel($tasksByProject[$i][$j][$k])
                        ->setProject($project)
                        ->setMilestone($milestone)
                        ->setManager($managerChoice)
                        ->setIsFunctional(($i + $k) % 2 === 0)
                        ->setPlannedStartDate(new DateTimeImmutable('+'.$dayOffset.' days'))
                        ->setDaysEstimate($daysEstimate);

                    // Ajouter des requirements de manière variée
                    $reqPattern = ($i + $j + $k) % 3;
                    if ($reqPattern === 0) {
                        $task->addRequirement($requirement1);
                        $task->addRequirement($requirement2);
                    } elseif ($reqPattern === 1) {
                        $task->addRequirement($requirement2);
                        $task->addRequirement($requirement3);
                    } else {
                        $task->addRequirement($requirement1);
                        $task->addRequirement($requirement3);
                    }

                    // Lier à la tâche précédente si elle existe
                    if ($previousTask !== null) {
                        $task->setPreviousTask($previousTask);
                    }

                    $manager->persist($task);

                    // Référence unique pour chaque tâche
                    $this->addReference('task_' . $i . '_' . $j . '_' . $k, $task);

                    // Garder les 4 premières références pour compatibilité (task_1 à task_4)
                    if ($taskCounter < 4) {
                        $this->addReference('task_' . ($taskCounter + 1), $task);
                    }

                    $previousTask = $task;
                    $taskCounter++;
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class,
            MilestoneFixtures::class,
            UserFixtures::class,
            RequirementFixtures::class,
        ];
    }
}