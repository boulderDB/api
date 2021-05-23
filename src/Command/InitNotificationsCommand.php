<?php

namespace App\Command;

use App\Repository\LocationRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitNotificationsCommand extends Command
{
    protected static $defaultName = "blocbeta:notifications:init";

    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private LocationRepository $locationRepository;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        LocationRepository $locationRepository,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->locationRepository = $locationRepository;
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var \App\Entity\User[] $users
         */
        $users = $this->userRepository->findAll();
        $progress = $io->createProgressBar(count($users));

        /**
         * @var \App\Entity\Location[] $locations
         */
        $locations = $this->locationRepository->findAll();

        $updates = 0;

        foreach ($users as $user) {
            $notifications = [];

            foreach ($locations as $location) {
                $notifications = array_merge($notifications, NotificationService::getNotifications($user, $location));
            }

            $user->setNotifications($notifications);

            $this->entityManager->persist($user);
            $updates++;

            $progress->advance();

            if ($updates % 100 === 0) {
                $this->entityManager->flush();
            }
        }

        $progress->finish();

        return 0;
    }
}