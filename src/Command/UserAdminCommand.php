<?php

namespace App\Command;

use App\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserAdminCommand extends Command
{

    protected static $defaultName = 'app:user:admin';
    protected static $defaultDescription = 'Creation d\'un admin.';

    private $entityManager;
    private $passwordHasher;

    /**
     * @param string|null $name
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(string $name = null, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        if (!$email && !$password) {
            $io->error('The command requires the email and password to create the user. Please check the information.');
            return Command::FAILURE;
        }
        
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setLastname('Admin');
        $user->setFirstname('Admin');
        $user->setCkeckbox('1');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('The user has been successfully created.');

        return Command::SUCCESS;
    }
}
