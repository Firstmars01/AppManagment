<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a user via arguments or interactive prompts'
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
            ->addArgument('name', InputArgument::OPTIONAL, 'First name')
            ->addArgument('secondName', InputArgument::OPTIONAL, 'Last name');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getArgument('email')) {
            $input->setArgument('email', $io->ask('Email'));
        }

        if (!$input->getArgument('password')) {
            $input->setArgument('password', $io->askHidden('Password'));
        }

        if (!$input->getArgument('name')) {
            $input->setArgument('name', $io->ask('First name'));
        }

        if (!$input->getArgument('secondName')) {
            $input->setArgument('secondName', $io->ask('Last name'));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $name = $input->getArgument('name');
        $secondName = $input->getArgument('secondName');

        // Check email uniqueness
        $existing = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $io->error("A user with the email $email already exists.");
            return Command::FAILURE;
        }

        // Create user
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setSecondName($secondName);

        $hashedPass = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPass);

        $this->em->persist($user);
        $this->em->flush();

        $io->success("User created: $name $secondName <$email>");

        return Command::SUCCESS;
    }
}
