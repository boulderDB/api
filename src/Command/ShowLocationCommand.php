<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\LocationRepository;
use App\Repository\SetterRepository;
use App\Repository\UserRepository;
use App\Service\ContextService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ShowLocationCommand extends Command
{
    protected static $defaultName = "blocbeta:location:show";

    private UserRepository $userRepository;
    private LocationRepository $locationRepository;
    private SetterRepository $setterRepository;

    public function __construct(
        UserRepository $userRepository,
        LocationRepository $locationRepository,
        SetterRepository $setterRepository,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->userRepository = $userRepository;
        $this->locationRepository = $locationRepository;
        $this->setterRepository = $setterRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription("Command to inspect a location")
            ->addArgument("id", InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $locationId = $input->getArgument("id");
        $io = new SymfonyStyle($input, $output);

        /**
         * @var \App\Entity\Location $location
         */
        $location = $this->locationRepository->find($locationId);

        if (!$location) {
            $io->error("Location '$locationId' not found");
            return 1;
        }

        $admins = $this->userRepository->getLocationAdmins($locationId);

        $io->table(
            ['id', 'username', 'email', 'notifications'],
            array_map(function ($admin) use ($location) {

                /**
                 * @var User $admin
                 */
                return [
                    $admin->getId(),
                    $admin->getUsername(),
                    $admin->getEmail(),
                    implode(",", $admin->getNotifications()[$location->getId()])
                ];
            }, $admins)
        );

        return 0;
    }
}