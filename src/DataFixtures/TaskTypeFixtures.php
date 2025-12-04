<?php

namespace App\DataFixtures;

use App\Entity\TaskType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TaskTypeFixtures extends Fixture
{
    public const TYPES = [
        'Not started',
        'Started but not finished',
        'Finished',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::TYPES as $label) {
            $type = (new TaskType())
                ->setLabel($label);

            $manager->persist($type);
            $this->addReference('task_type_' . str_replace(' ', '_', strtolower($label)), $type);
        }

        $manager->flush();
    }
}
