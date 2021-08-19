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

        function logTableArguments(array $users): array
        {
            return [
                ['id', 'username', 'email', 'roles'],
                array_map(function ($user) {
                    /**
                     * @var User $user
                     */
                    return [
                        $user->getId(),
                        $user->getUsername(),
                        $user->getEmail(),
                        json_encode($user->getRoles())
                    ];
                }, $users)
            ];
        }

        $admins = $this->userRepository->getByRole(
            ContextService::getLocationRoleName(User::ADMIN, $locationId, true)
        );

        $io->section("Admins");
        $io->table(...logTableArguments($admins));

        $counters = $this->userRepository->getByRole(
            ContextService::getLocationRoleName(User::COUNTER, $locationId, true)
        );

        $io->section("Counters");
        $io->table(...logTableArguments($counters));

        $setters = $this->userRepository->getByRole(
            ContextService::getLocationRoleName(User::SETTER, $locationId, true)
        );

        $io->section("Setters");
        $io->table(...logTableArguments($setters));

        return 0;
    }
}