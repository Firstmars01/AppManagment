<?php

namespace App\DataFixtures;

use App\Entity\Milestone;
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

        $tasksByProject = [
            [
                ['Wireframing', 'UI/UX Design', 'Design System'],
                ['API Development', 'Database Schema', 'Authentication'],
                ['React Components', 'Integration Tests', 'Performance Optimization']
            ],
            [
                ['Market Research', 'User Personas', 'Feature Planning'],
                ['Core Functionality', 'User Interface', 'Beta Testing'],
                ['App Store Setup', 'Marketing Campaign', 'Launch Event']
            ],
            [
                ['Server Provisioning', 'Network Configuration', 'Security Setup'],
                ['Data Migration', 'Load Testing', 'Disaster Recovery'],
                ['Production Deployment', 'Monitoring Setup', 'Documentation']
            ],
            [
                ['Data Source Integration', 'ETL Pipeline', 'Data Cleaning'],
                ['Statistical Analysis', 'Machine Learning Models', 'Model Validation'],
                ['Dashboard Creation', 'KPI Tracking', 'Stakeholder Reports']
            ],
            [
                ['Product Import', 'Category Structure', 'Inventory Management'],
                ['Stripe Integration', 'Checkout Flow', 'Order Management'],
                ['SEO Optimization', 'Mobile Responsiveness', 'Analytics Setup']
            ]
        ];

        $taskCounter = 0;

        for ($i = 0; $i < 5; $i++) {

            // Tous les requirements du projet
            $projectRequirements = [
                $this->getReference('requirement_' . $i . '_1', Requirement::class),
                $this->getReference('requirement_' . $i . '_2', Requirement::class),
                $this->getReference('requirement_' . $i . '_3', Requirement::class),
            ];

            for ($j = 0; $j < 3; $j++) {
                $milestone = $this->getReference('milestone_' . $i . '_' . $j, Milestone::class);
                $previousTask = null;

                for ($k = 0; $k < 3; $k++) {
                    $managerChoice = $users[($i + $j + $k) % count($users)];
                    $dayOffset = $i * 90 + $j * 30 + $k * 10;
                    $daysEstimate = rand(3, 15);

                    $task = (new Task())
                        ->setId(Uuid::v4())
                        ->setLabel($tasksByProject[$i][$j][$k])
                        ->setDescription('This task focuses on ' . strtolower($tasksByProject[$i][$j][$k]))
                        ->setMilestone($milestone)
                        ->setManager($managerChoice)
                        ->setIsFunctional(($i + $k) % 2 === 0)
                        ->setPlannedStartDate(new DateTimeImmutable('+'.$dayOffset.' days'))
                        ->setDaysEstimate($daysEstimate);

                    // On choisit 1 ou 2 requirements aléatoires parmi les 3
                    $numReqs = rand(1, 2);
                    $selectedKeys = array_rand($projectRequirements, $numReqs);

                    // Si un seul requirement est choisi, array_rand renvoie un int
                    if ($numReqs === 1) {
                        $task->addRequirement($projectRequirements[$selectedKeys]);
                    } else {
                        foreach ($selectedKeys as $key) {
                            $task->addRequirement($projectRequirements[$key]);
                        }
                    }

                    if ($previousTask !== null) {
                        $task->setPreviousTask($previousTask);
                    }

                    $manager->persist($task);
                    $this->addReference('task_' . $i . '_' . $j . '_' . $k, $task);

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
            MilestoneFixtures::class,
            UserFixtures::class,
            RequirementFixtures::class,
        ];
    }

}
