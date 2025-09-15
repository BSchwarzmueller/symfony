<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Creates an admin user with optional e-mail and password.')]
class CreateAdminUserCommand extends Command {
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);

        $admin = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :role') // nur bei MySQL JSON
            ->setParameter('role', '%"ROLE_ADMIN"%')
            ->getQuery()
            ->getResult();

        if(!empty($admin)) {
            $io->error('Admin user already exists.');
            return Command::FAILURE;
        }

        $email = $io->ask('E-Mail (admin@test.de)', 'admin@test.de');
        $password = $io->ask('Password (administrator)', 'administrator');

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($hashedPassword);

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $io->success('Admin user '. $email .' created successfully.');
            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error('Error creating admin user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
