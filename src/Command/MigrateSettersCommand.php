<?php

namespace App\Command;

use App\Entity\Location;
use App\Entity\Setter;
use App\Entity\User;
use App\Repository\LocationRepository;
use App\Repository\SetterRepository;
use App\Repository\UserRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateSettersCommand extends Command
{
    protected static $defaultName = "blocbeta:migrate-setters";

    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private LocationRepository $locationRepository;
    private ContextService $contextService;
    private SetterRepository $setterRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        LocationRepository $locationRepository,
        ContextService $contextService,
        SetterRepository $setterRepository,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->locationRepository = $locationRepository;
        $this->contextService = $contextService;
        $this->setterRepository = $setterRepository;
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->locationRepository->findAll() as $location) {

            /**
             * @var Location $location
             */
            $role = ContextService::getLocationRoleName("SETTER", $location->getId(), true);

            $io->writeln($location->getName());

            $users = $this->userRepository->createQueryBuilder('user')
                ->where('user.roles LIKE :role')
                ->setParameter('role', '%"' . $role . '"%')
                ->orderBy('lower(user.username)', 'ASC')
                ->getQuery()
                ->getResult();

            /**
             * @var User $user
             */
            foreach ($users as $user) {

                /**
                 * @var Setter $existingSetter
                 */
                $existingSetter = $this->setterRepository->findOneBy([
                    "username" => $user->getUsername()
                ]);

                if ($existingSetter) {
                    if ($existingSetter->getLocations()->contains($location)) {
                        continue;
                    }

                    $existingSetter->getLocations()->add($location);

                    $this->entityManager->persist($existingSetter);
                    $this->entityManager->flush();

                    continue;
                }

                $setter = new Setter();

                $setter->setUsername($user->getUsername());
                $setter->getLocations()->add($location);
                $setter->setActive(true);
                $setter->setUser($user);

                $this->entityManager->persist($setter);
                $this->entityManager->flush();
            }
        }

        return 0;
    }
}
