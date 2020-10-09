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

class AddRoleCommand extends Command
{
    protected static $defaultName = 'blocbeta:user:add-role';

    private $entityManager;

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
        $this
            ->setDescription('Command to add a role to a user')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('role', InputArgument::REQUIRED)
            ->addArgument('locationId', InputArgument::REQUIRED);

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $role = strtoupper($input->getArgument('role'));
        $locationId = $input->getArgument('locationId');

        if (!in_array($role, [User::SETTER, User::ADMIN])) {
            $io->error("Role {$role} does not exist");

            return 1;
        }

        /**
         * @var EntityRepository $userRepository
         */
        $userRepository = $this->entityManager->getRepository(User::class);

        /**
         * @var User $user
         */
        $user = $userRepository->createQueryBuilder('user')
            ->where('user.username = :username')
            ->setParameter('username', $input->getArgument('username'))
            ->getQuery()
            ->getSingleResult();

        $locationRole = ContextService::getLocationRoleName($role, $locationId);

        $user->addRole($locationRole);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("Added role {$role} ({$locationRole}) to user {$user->getUsername()}");

        return 0;
    }
}
