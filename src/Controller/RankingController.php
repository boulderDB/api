<?php

namespace App\Controller;

use App\Factory\RedisConnectionFactory;
use App\Repository\BoulderRepository;
use App\Scoring\DefaultScoring;
use App\Service\ContextService;
use App\Struct\BoulderStruct;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/ranking")
 */
class RankingController extends AbstractController
{
    private ContextService $contextService;
    private BoulderRepository $boulderRepository;
    private \Redis $redis;

    public function __construct(
        ContextService $contextService,
        BoulderRepository $boulderRepository
    )
    {
        $this->contextService = $contextService;
        $this->boulderRepository = $boulderRepository;
        $this->redis = RedisConnectionFactory::create();
    }

    /**
     * @Route("/current", methods={"GET"})
     */
    public function current()
    {
        $boulders = $this->boulderRepository->getAscentData(
            $this->contextService->getLocation()->getId()
        );

        $boulderStructs = array_map(function ($boulder) {
            return BoulderStruct::fromArray($boulder);
        }, $boulders);

        $defaultScoring = new DefaultScoring();
        $ranking = $defaultScoring->calculate($boulderStructs);

        return $this->json($ranking);
    }

    /**
     * @Route("/all-time", methods={"GET"})
     */
    public function allTime()
    {
        $ranking = $this->redis->get($this->contextService->getLocation()->getId() . "-all-time-ranking");

        return $this->json(json_decode($ranking, true));
    }
}