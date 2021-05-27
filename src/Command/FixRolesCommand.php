<?php

namespace App\Command;

use App\Entity\User;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixRolesCommand extends Command
{
    protected static $defaultName = "blocbeta:user:fix-roles";

    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var User[] $users
         */
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $updates = 0;

        foreach ($users as $user) {
            $user->setRoles(array_values($user->getRoles()));

            $this->entityManager->persist($user);
            $updates++;

            if ($updates % 100 === 0) {
                $this->entityManager->flush();
            }
        }

        return 0;
    }
}