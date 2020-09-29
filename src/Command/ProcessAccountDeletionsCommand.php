<?php

namespace App\Command;

use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessAccountDeletionsCommand extends Command
{
    protected static $defaultName = 'blocbeta:user:process-account-deletions';

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

        foreach ($deletions as $key => $deletion) {
            $data = RedisConnectionFactory::explodeKey($key);

            $timestamp = $this->redis->get($key);

            if (time() < $timestamp) {
                continue;
            }

            /**
             * @var User $users
             */
            $user = $repository->find($data['user']);

            if (!$user) {
                $io->error("User {$data['user']} not found");
                continue;
            }

            $this->entityManager->remove($user);
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