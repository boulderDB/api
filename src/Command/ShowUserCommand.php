<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ShowUserCommand extends Command
{
    protected static $defaultName = "blocbeta:user:show";

    private UserRepository $userRepository;

    public function __construct(
        UserRepository $userRepository,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->userRepository = $userRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription("Command to inspect a user")
            ->addArgument("username", InputArgument::REQUIRED);

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $input->getArgument("username");
        $user = $this->userRepository->findUserByUsername($input->getArgument("username"));

        if (!$user) {
            $io->error("User: $username not found");
        } else {
            dump($user);
        }

        return 0;
    }
}
