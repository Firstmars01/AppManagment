<?php

namespace App\DataFixtures;

use App\Entity\RequirementType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;


class RequirementTypeFixtures extends Fixture
{
    public const TYPES = [
        'data',
        'performances',
        'interfaces_utilisateur',
        'qualite',
        'services',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::TYPES as $label) {
            $type = (new RequirementType())
                ->setId(Uuid::v4())
                ->setLabel(ucfirst(str_replace('_', ' ', $label)));

            $manager->persist($type);
            $this->addReference('requirement_type_' . $label, $type);
        }

        $manager->flush();
    }
}
