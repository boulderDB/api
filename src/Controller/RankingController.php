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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
     * @Route("/current", methods={"GET"}, name="rankings_current")
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
     * @Route("/event/{eventId}", methods={"GET"}, name="rankings_event")
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

        if (!$event->isActive()) {
            $this->denyUnlessLocationAdmin();
        }

        if ($event->hasEnded()) {
            $this->denyUnlessLocationAdmin();
        }

        return $this->okResponse($this->rankingService->calculateRanking(
            new DefaultPointsRanking(),
            $event->getBoulders()->toArray(),
            $event
        ));
    }

    /**
     * @Route("/event/{eventId}/export", methods={"GET"}, name="rankings_event_export")
     */
    public function export(int $eventId)
    {
        $this->denyUnlessLocationAdmin();

        /**
         * @var \App\Entity\Event $event
         */
        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            return $this->resourceNotFoundResponse(Event::RESOURCE_NAME, $eventId);
        }

        $ranking = $this->rankingService->calculateRanking(
            new DefaultPointsRanking(),
            $event->getBoulders()->toArray(),
            $event
        );

        function getCSV(array $row): string
        {
            return implode(",", $row) . PHP_EOL;
        }

        $csv = getCSV([
            "id",
            "username",
            "firstName",
            "lastName",
            "gender",
            "topCount",
            "topRate",
            "flashCount",
            "flashRate",
            "totalCount",
            "totalRate",
            "points"
        ]);

        foreach ($ranking as $rank) {

            $csv .= getCSV([
                "id" => $rank["user"]->getId(),
                "username" => $rank["user"]->getUsername(),
                "firstName" => $rank["user"]->getFirstName(),
                "lastName" => $rank["user"]->getLastName(),
                "gender" => $rank["user"]->getGender(),
                "topCount" => $rank["top"]["count"],
                "topRate" => $rank["top"]["rate"],
                "flashCount" => $rank["flash"]["count"],
                "flashRate" => $rank["flash"]["rate"],
                "totalCount" => $rank["total"]["count"],
                "totalRate" => $rank["total"]["rate"],
                "points" => $rank["points"]
            ]);

        }

        $filename = "{$event->getName()} – export.csv";

        $response = new Response($csv);

        $response->headers->set("Content-Encoding", "UTF-8");
        $response->headers->set("Content-Type", "text/csv; charset=UTF-8");
        $response->headers->set("Content-Disposition", "attachment; filename=$filename");
        $response->headers->set('Content-length', strlen($csv));

        return $response;
    }

    /**
     * @Route("/all-time", methods={"GET"}, name="rankings_all_time")
     */
    public function allTime()
    {
        $locationId = $this->contextService->getLocation()?->getId();

        return $this->okResponse([]);

    }
}