<?php

namespace App\EventSubscriber;

use App\Entity\Location;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestSubscriber implements EventSubscriberInterface
{
    private $contextService;
    private $entityManager;

    public function __construct(
        ContextService $contextService,
        EntityManagerInterface $entityManager
    )
    {
        $this->contextService = $contextService;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $slug = $event->getRequest()->get('location');

        $location = $this->entityManager->createQueryBuilder()
            ->select('location')
            ->from(Location::class, 'location')
            ->where('location.url = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$location) {
            return;
        }

        $this->contextService->setLocation($location);
    }
}
