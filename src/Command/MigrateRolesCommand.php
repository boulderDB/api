<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateRolesCommand extends Command
{
    protected static $defaultName = 'MigrateRoles';
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
        $this->setDescription('Remove post migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var EntityRepository $userRepository
         */
        $userRepository = $this->entityManager->getRepository(User::class);
        $setterRole = "ROLE_SETTER";

        /**
         * @var User[] $setters
         */
        $setters = $userRepository->createQueryBuilder('user')
            ->where('user.roles LIKE :roles')
            ->setParameter('roles', '%"' . $setterRole . '"%')
            ->getQuery()
            ->getResult();

        foreach ($setters as $setter) {
            $io->writeln($setter->getId());

            $setter->removeRole("SETTER@28");
            $setter->removeRole("SETTER@29");

            $setter->addRole("ROLE_SETTER@28");
            $setter->addRole("ROLE_SETTER@29");

            $this->entityManager->persist($setter);
        }

        $this->entityManager->flush();

        return 0;
    }
}
