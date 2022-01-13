<?php

namespace App\Command;

use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessAccountDeletionsCommand extends Command
{
    protected static $defaultName = 'boulderdb:user:process-account-deletions';

    private EntityManagerInterface $entityManager;
    private \Redis $redis;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->redis = RedisConnectionFactory::create();
    }

    protected function configure()
    {
        $this->setDescription('Permanently delete user accounts scheduled for deletion');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $deletions = $this->redis->keys("deletion:user=*");
        $repository = $this->entityManager->getRepository(User::class);

        foreach ($deletions as $key) {
            $data = RedisConnectionFactory::explodeKey($key);
            $timestamp = $this->redis->get($key);

            if (time() < $timestamp) {
                continue;
            }

            /**
             * @var User $user
             */
            $user = $repository->find($data['user']);

            if (!$user) {
                $io->error("User {$data['user']} not found");
                continue;
            }

            $io->writeln("Removed user ${$data['user']}");

            $user->setUsername("removed-user-{$user->getId()}");
            $user->setEmail("removed-user-{$user->getId()}");
            $user->setGender("neutral");
            $user->setFirstName("");
            $user->setLastName("");
            $user->setActive(false);
            $user->setVisible(false);
            $user->setRoles([]);
            $user->setImage(null);
            $user->setPassword(null);
            $user->setNotifications(new ArrayCollection());
            $user->setLastActivity(new \DateTime());
            $user->setLastVisitedLocation(null);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success("Removed user $user");
        }

        return 0;
    }

    public static function getAccountDeletionCacheKey($userId): string
    {
        return "deletion:user=$userId";
    }
}