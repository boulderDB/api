<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\StorageClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateUserAvatars extends Command
{
    protected static $defaultName = "blocbeta:migrate-user-avatars";

    private EntityManagerInterface $entityManager;
    private StorageClient $storageClient;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        StorageClient $storageClient,
        UserRepository $userRepository,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->storageClient = $storageClient;
        $this->userRepository = $userRepository;
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var User[] $users
         */
        $users = $this->userRepository
            ->createQueryBuilder("user")
            ->where("user.image is not null")
            ->getQuery()
            ->getResult();

        foreach ($users as $user) {

            try {
                $contents = file_get_contents("https://boulderdb.de/uploads/{$user->getImage()}");
            } catch (\Exception $exception) {
                continue;
            }

            if (!$contents) {
                continue;
            }

            $resource = $this->storageClient->uploadContent(
                $contents,
                pathinfo($user->getImage(), \PATHINFO_EXTENSION)
            );

            $user->setImage($resource);
            $io->writeln($resource);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return 0;
    }
}
