<?php

namespace App\Controller;

use App\Entity\Ascent;
use App\Factory\RedisConnectionFactory;
use App\Repository\AscentRepository;
use App\Repository\BoulderRepository;
use App\Scoring\DefaultScoring;
use App\Service\ContextService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/ranking")
 */
class RankingController extends AbstractController
{
    use ResponseTrait;

    private ContextService $contextService;
    private BoulderRepository $boulderRepository;
    private \Redis $redis;
    private AscentRepository $ascentRepository;

    public function __construct(
        ContextService $contextService,
        BoulderRepository $boulderRepository,
        AscentRepository $ascentRepository
    )
    {
        $this->contextService = $contextService;
        $this->boulderRepository = $boulderRepository;
        $this->ascentRepository = $ascentRepository;
        $this->redis = RedisConnectionFactory::create();
    }

    /**
     * @Route("/current", methods={"GET"})
     */
    public function current()
    {
        $locationId = $this->contextService->getLocation()->getId();

        $scoring = new DefaultScoring();
        $boulders = $this->boulderRepository->getWithAscents($locationId);

        $data = [];

        /**
         * @var \App\Entity\Boulder $boulder
         */
        foreach ($boulders as $boulder) {
            $scoring->calculateScore($boulder);

            /**
             * @var \App\Entity\Ascent $ascent
             */
            foreach ($boulder->getAscents() as $ascent) {
                if (!in_array($ascent->getType(), $scoring->getScoredAscentTypes())) {
                    continue;
                }

                $userId = $ascent->getUser()->getId();

                if (!array_key_exists($userId, $data)) {
                    $data[$userId] = [
                        "user" => $ascent->getUser(),
                        Ascent::ASCENT_TOP => [
                            "count" => 0,
                            "rate" => 0
                        ],
                        Ascent::ASCENT_FLASH => [
                            "count" => 0,
                            "rate" => 0
                        ],
                        "total" => [
                            "count" => 0,
                            "rate" => 0
                        ],
                    ];
                }

                $data[$userId][$ascent->getType()]["count"]++;
                $data[$userId]["total"]["count"]++;
            }
        }

        foreach ($data as &$rank) {
            $rank[Ascent::ASCENT_TOP]["rate"] = round(($rank[Ascent::ASCENT_TOP]["count"] / $rank["total"]["count"]) * 100);
            $rank[Ascent::ASCENT_FLASH]["rate"] = round(($rank[Ascent::ASCENT_FLASH]["count"] / $rank["total"]["count"]) * 100);

            $rank["total"]["rate"] = round(($rank["total"]["count"] / count($boulders)) * 100);
        }

        usort($data, function ($a, $b) {
            return ($a["total"]["count"] < $b["total"]["count"]) ? 1 : -1;
        });

        return $this->okResponse(array_values($data));
    }

    /**
     * @Route("/all-time", methods={"GET"})
     */
    public function allTime()
    {
        $locationId = $this->contextService->getLocation()->getId();

        return $this->okResponse($this->ascentRepository->countByUser($locationId));

    }
}