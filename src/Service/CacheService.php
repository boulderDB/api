<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use Symfony\Component\Routing\RouterInterface;

class CacheService
{
    private StoreInterface $store;
    private ParameterBagInterface $parameterBag;
    private RouterInterface $router;
    private ContextService $contextService;
    private LoggerInterface $logger;

    public function __construct(
        ParameterBagInterface $parameterBag,
        RouterInterface $router,
        ContextService $contextService,
        LoggerInterface $logger
    )
    {
        $this->store = new Store($parameterBag->get("kernel.cache_dir") . "/http_cache");
        $this->parameterBag = $parameterBag;
        $this->router = $router;
        $this->contextService = $contextService;
        $this->logger = $logger;
    }

    public function invalidate(array $invalidations)
    {
        foreach ($invalidations as $routeName) {
            $url = $this->router->generate($routeName, [
                "location" => $this->contextService->getLocation()->getUrl()
            ]);

            $this->store->purge($url);
        }

    }

    public static function getCurrentRankingKey(string $locationId): string
    {
        return "location-{$locationId}-current-ranking";
    }

    public static function getCurrentRankingTimestampKey(string $locationId): string
    {
        return "location-{$locationId}-current-ranking:last-run";
    }

    public static function getAllTimeRankingKey(int $locationId)
    {
        return "location-{$locationId}-all-time-ranking";
    }

    public static function getBoulderCacheKey(int $locationId)
    {
        return "location-{$locationId}-boulder-cache";
    }
}