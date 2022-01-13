<?php

namespace App\Command;

use App\Factory\RedisConnectionFactory;
use App\Ranking\DefaultPointsRanking;
use App\Repository\EventRepository;
use App\Service\RankingService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ArchiveEndedEventsCommand extends Command
{
    protected static $defaultName = 'boulderdb:event:archive-ended';

    private \Redis $redis;
    private EventRepository $eventRepository;
    private RankingService $rankingService;

    public function __construct(
        EventRepository $eventRepository,
        RankingService $rankingService,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->redis = RedisConnectionFactory::create();
        $this->eventRepository = $eventRepository;
        $this->rankingService = $rankingService;
    }

    protected function configure()
    {
        $this->setDescription('Archive ended events');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var \App\Entity\Event[] $events
         */
        $events = $this->eventRepository->getEnded();
        $progress = $io->createProgressBar(count($events));


        foreach ($events as $event) {
            $ranking = $this->rankingService->calculateRanking(
                new DefaultPointsRanking(),
                $event->getBoulders()->toArray(),
                $event
            );

            $this->redis->set("event:{$event->getId()}:ranking", json_encode($ranking));

            $progress->advance();
        }

        $progress->finish();

        return 0;
    }
}