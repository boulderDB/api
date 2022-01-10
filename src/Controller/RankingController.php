<?php

namespace App\Controller;

use App\Entity\Event;
use App\Factory\RedisConnectionFactory;
use App\Ranking\DefaultPointsRanking;
use App\Repository\AscentRepository;
use App\Repository\BoulderRepository;
use App\Repository\EventRepository;
use App\Service\ContextService;
use App\Service\RankingService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/rankings")
 */
class RankingController extends AbstractController
{
    use ResponseTrait;

    private ContextService $contextService;
    private BoulderRepository $boulderRepository;
    private \Redis $redis;
    private AscentRepository $ascentRepository;
    private RankingService $rankingService;
    private EventRepository $eventRepository;

    public function __construct(
        ContextService $contextService,
        BoulderRepository $boulderRepository,
        AscentRepository $ascentRepository,
        RankingService $rankingService,
        EventRepository $eventRepository
    )
    {
        $this->contextService = $contextService;
        $this->boulderRepository = $boulderRepository;
        $this->ascentRepository = $ascentRepository;
        $this->redis = RedisConnectionFactory::create();
        $this->rankingService = $rankingService;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @Route("/current", methods={"GET"})
     */
    public function current()
    {
        $locationId = $this->contextService->getLocation()?->getId();
        $boulders = $this->boulderRepository->getWithAscents($locationId);

        return $this->okResponse($this->rankingService->calculateRanking(
            new DefaultPointsRanking(),
            $boulders
        ));
    }

    /**
     * @Route("/event/{eventId}", methods={"GET"})
     */
    public function event(int $eventId)
    {
        /**
         * @var \App\Entity\Event $event
         */
        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            return $this->resourceNotFoundResponse(Event::RESOURCE_NAME, $eventId);
        }

        return $this->okResponse($this->rankingService->calculateRanking(
            new DefaultPointsRanking(),
            $event->getBoulders()->toArray()
        ));
    }

    /**
     * @Route("/all-time", methods={"GET"})
     */
    public function allTime()
    {
        $locationId = $this->contextService->getLocation()?->getId();

        return $this->okResponse([]);

    }
}