<?php

namespace App\Command;

use App\Entity\Location;
use App\Factory\RedisConnectionFactory;
use App\Repository\BoulderRepository;
use App\Repository\LocationRepository;
use App\Scoring\DefaultScoring;
use App\Struct\BoulderStruct;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IndexCurrentCommand extends Command
{
    protected static $defaultName = 'blocbeta:ranking:index-current';

    private \Redis $redis;
    private LocationRepository $locationRepository;
    private BoulderRepository $boulderRepository;

    public function __construct(
        LocationRepository $locationRepository,
        BoulderRepository $boulderRepository,
        string $name = null
    )
    {
        parent::__construct($name);

        $this->redis = RedisConnectionFactory::create();
        $this->locationRepository = $locationRepository;
        $this->boulderRepository = $boulderRepository;
    }

    protected function configure()
    {
        $this->setDescription('Index current rankings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $current = new \DateTime();

        /**
         * @var Location[] $locations
         */
        $locations = $this->locationRepository->findAll();

        foreach ($locations as $location) {
            $locationId = $location->getId();

            $io->writeln("Processing location $locationId – {$location->getName()}");

            $boulders = $this->boulderRepository->getWithAscents($locationId);

            $defaultScoring = new DefaultScoring();
            $ranking = $defaultScoring->calculate($boulders);

            $this->redis->set(self::getTimestampCacheKey($locationId), $current->format('c'));
            $this->redis->set(self::getCacheKey($locationId), json_encode($ranking));
        }

        $io->success('All current ranking indexed successfully');

        return 0;
    }

    public static function getTimestampCacheKey(string $locationId): string
    {
        return "location-{$locationId}-current-ranking:last-run";
    }

    public static function getCacheKey(string $locationId): string
    {
        return "location-{$locationId}-current-ranking";
    }
}