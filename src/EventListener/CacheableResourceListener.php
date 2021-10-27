<?php

namespace App\EventListener;

use App\Entity\CacheableInterface;
use App\Service\ContextService;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;

class CacheableResourceListener implements EventSubscriber
{
    private StoreInterface $store;
    private RequestStack $requestStack;
    private LoggerInterface $logger;
    private ContextService $contextService;

    public function __construct(
        RequestStack $requestStack,
        ParameterBagInterface $parameterBag,
        LoggerInterface $logger,
        ContextService $contextService
    )
    {
        $this->requestStack = $requestStack;
        $this->store = new Store($parameterBag->get('kernel.cache_dir') . '/http_cache');
        $this->logger = $logger;
        $this->contextService = $contextService;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postUpdate,
            Events::postPersist
        ];
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $subject = $eventArgs->getObject();

        if (!$subject instanceof CacheableInterface) {
            return;
        }

        $this->invalidate($subject->invalidates());
    }

    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $subject = $eventArgs->getObject();

        if (!$subject instanceof CacheableInterface) {
            return;
        }

        $this->invalidate($subject->invalidates());
    }

    private function invalidate(array $invalidations)
    {
        foreach ($invalidations as $invalidation) {
            $url = "{$_ENV["APP_HOST"]}/api/{$this->contextService->getLocation()?->getUrl()}{$invalidation}";

            $this->store->purge($url);
            $this->logger->error($url);
        }
    }
}