<?php
namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\Uid\Uuid;

class ProjectFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $owner = $this->getReference('user_owner', User::class);

        // Tableau de noms pour plusieurs projets
        $projectNames = [
            'Projet Alpha',
            'Projet Beta',
            'Projet Gamma',
            'Projet Delta',
            'Projet Epsilon',
        ];

        foreach ($projectNames as $index => $name) {
            $project = (new Project())
                ->setId(Uuid::v4())
                ->setName($name)
                ->setOwner($owner)
                ->setCreatedAt(new DateTimeImmutable())
                ->setUpdatedAt(new DateTimeImmutable());

            $manager->persist($project);

            // Ajouter des références pour d'autres fixtures
            $this->addReference('project_' . $index, $project);

            // On garde "project_main" pour compatibilité
            if ($index === 0) {
                $this->addReference('project_main', $project);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
