<?php

namespace App\Controller;

use App\Command\Ranking\IndexCurrentCommand;
use App\Factory\RedisConnectionFactory;
use App\Repository\BoulderRepository;
use App\Service\ContextService;
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
        $locationId = $this->contextService->getLocation()->getId();

        $cacheKey = IndexCurrentCommand::getCacheKey($locationId);
        $timestampCacheKey = IndexCurrentCommand::getTimestampCacheKey($locationId);

        if (!$this->redis->exists($cacheKey)) {
            $data = [];
        } else {
            $data = json_decode($this->redis->get($cacheKey));
        }

        return $this->json([
            'ranking' => $data,
            'lastRun' => $this->redis->get($timestampCacheKey)
        ]);
    }

    /**
     * @Route("/all-time", methods={"GET"})
     */
    public function allTime()
    {
        $ranking = $this->redis->get($this->contextService->getLocation()->getId() . "-all-time-ranking");

        return $this->json([
            'data' => json_decode($ranking),
            'lastRun' => null
        ], true);
    }
}