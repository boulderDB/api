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

class IndexCurrentRankingCommand extends Command
{
    protected static $defaultName = 'blocbeta:index-current-ranking';

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

        /**
         * @var Location[] $locations
         */
        $locations = $this->locationRepository->findAll();

        foreach ($locations as $location) {
            $locationId = $location->getId();

            $io->writeln("Processing location $locationId – {$location->getName()}");

            $boulders = $this->boulderRepository->getAscentData($locationId);

            $boulderStructs = array_map(function ($boulder) {
                return BoulderStruct::fromArray($boulder);
            }, $boulders);

            $defaultScoring = new DefaultScoring();
            $ranking = $defaultScoring->calculate($boulderStructs);

            $this->redis->set(self::getCacheKey($locationId), json_encode($ranking));
        }

        $io->success('All current ranking indexed successfully');

        return 0;
    }

    public static function getCacheKey(string $locationId): string
    {
        return "location-{$locationId}-current-ranking";
    }
}