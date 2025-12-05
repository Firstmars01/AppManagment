<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Define a Symfony command with metadata
#[AsCommand(
    name: 'app:create-user', // Command name used in terminal
    description: 'Create a user via arguments or interactive prompts' // Description shown in "php bin/console list"
)]
class CreateUserCommand extends Command
{
    // Inject services: EntityManager for database operations and PasswordHasher for password hashing
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct(); // Call parent constructor
    }

    // Configure optional command arguments
    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email') // User email
            ->addArgument('password', InputArgument::OPTIONAL, 'Password') // User password
            ->addArgument('name', InputArgument::OPTIONAL, 'First name') // First name
            ->addArgument('secondName', InputArgument::OPTIONAL, 'Last name'); // Last name
    }

    // Interactive prompts if arguments are not provided
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output); // Use SymfonyStyle for nicer CLI UI

        if (!$input->getArgument('email')) {
            $input->setArgument('email', $io->ask('Email')); // Ask for email
        }

        if (!$input->getArgument('password')) {
            $input->setArgument('password', $io->askHidden('Password')); // Ask for password hidden
        }

        if (!$input->getArgument('name')) {
            $input->setArgument('name', $io->ask('First name')); // Ask for first name
        }

        if (!$input->getArgument('secondName')) {
            $input->setArgument('secondName', $io->ask('Last name')); // Ask for last name
        }
    }

    // Execute command logic
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output); // CLI interface

        // Retrieve input arguments
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $name = $input->getArgument('name');
        $secondName = $input->getArgument('secondName');

        // Check if the email already exists in the database
        $existing = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $io->error("A user with the email $email already exists."); // Show error if user exists
            return Command::FAILURE;
        }

        // Create a new user entity
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setSecondName($secondName);

        // Hash the password before saving
        $hashedPass = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPass);

        // Persist the user to the database
        $this->em->persist($user);
        $this->em->flush();

        $io->success("User created: $name $secondName <$email>"); // Success message

        return Command::SUCCESS; // Return success
    }
}
