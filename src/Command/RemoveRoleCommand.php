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

class RemoveRoleCommand extends Command
{
    protected static $defaultName = "blocbeta:user:remove-role";

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
        $this
            ->setDescription("Command to remove a role from a user")
            ->addArgument("username", InputArgument::REQUIRED)
            ->addArgument("role", InputArgument::REQUIRED)
            ->addArgument("locationId", InputArgument::OPTIONAL);

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $role = strtoupper($input->getArgument("role"));
        $locationId = $input->getArgument("locationId");

        /**
         * @var EntityRepository $userRepository
         */
        $userRepository = $this->entityManager->getRepository(User::class);

        /**
         * @var User $user
         */
        $user = $userRepository->createQueryBuilder("user")
            ->where("user.username = :username")
            ->setParameter("username", $input->getArgument("username"))
            ->getQuery()
            ->getSingleResult();

        if ($locationId) {
            $role = ContextService::getLocationRoleName($role, $locationId, true);
        } else {
            $role = "ROLE_${$role}";
        }

        $user->setRoles(array_filter($user->getRoles(), function ($currentRole) use ($role) {
            return $currentRole !== $role;
        }));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("Removed role {$role} from user {$user->getUsername()}");

        return 0;
    }
}
