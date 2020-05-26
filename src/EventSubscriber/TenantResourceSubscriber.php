<?php


namespace App\EventSubscriber;

use App\Components\Entity\TenantResourceInterface;
use App\Service\ContextService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class TenantResourceSubscriber implements EventSubscriber
{
    private $contextService;

    public function __construct(ContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof TenantResourceInterface) {
            return;
        }

        $subject->setTenant($this->contextService->getLocation());
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof TenantResourceInterface) {
            return;
        }

        $subject->setTenant($this->contextService->getLocation());
    }
}