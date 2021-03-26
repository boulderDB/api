<?php

namespace App\Controller;

use App\Command\IndexCurrentCommand;
use App\Factory\RedisConnectionFactory;
use App\Repository\BoulderRepository;
use App\Service\CacheService;
use App\Service\ContextService;
use Symfony\Component\HttpFoundation\Request;
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

        $cacheKey = CacheService::getCurrentRankingKey($locationId);
        $timestampCacheKey = CacheService::getCurrentRankingTimestampKey($locationId);

        if (!$this->redis->exists($cacheKey)) {
            $data = [];
        } else {
            $data = json_decode($this->redis->get($cacheKey));
        }

        return $this->json([
            "list" => $data,
            "updated" => $this->redis->get($timestampCacheKey)
        ]);
    }

    /**
     * @Route("/all-time", methods={"GET"})
     */
    public function allTime()
    {
        $ranking = $this->redis->get(CacheService::getAllTimeRankingKey($this->contextService->getLocation()->getId()));

        return $this->json([
            "list" => json_decode($ranking),
            "updated" => null
        ]);
    }
}