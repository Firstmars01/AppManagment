<?php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            ['Jean', 'Dupont', 'jean.dupont@example.com'],
            ['Marie', 'Martin', 'marie.martin@example.com'],
            ['Paul', 'Durand', 'paul.durand@example.com'],
            ['Sophie', 'Bernard', 'sophie.bernard@example.com'],
            ['Lucas', 'Lefevre', 'lucas.lefevre@example.com'],
        ];

        foreach ($usersData as $index => [$firstName, $lastName, $email]) {
            $user = (new User())
                ->setId(Uuid::v4())
                ->setName($firstName)
                ->setLastName($lastName)
                ->setEmail($email);

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);

            $manager->persist($user);

            // Références utilisables dans d'autres fixtures
            if ($index === 0) {
                $this->addReference('user_owner', $user);
                $this->addReference('user_main', $user);
            }
            $this->addReference('user_' . $index, $user);
        }

        $manager->flush();
    }
}
