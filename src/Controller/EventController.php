<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/events")
 */
class EventController extends AbstractController
{
    use CrudTrait;
    use ContextualizedControllerTrait;
    use FilterTrait;

    private EventRepository $eventRepository;
    private ContextService $contextService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EventRepository $eventRepository,
        ContextService $contextService,
        EntityManagerInterface $entityManager
    )
    {
        $this->eventRepository = $eventRepository;
        $this->contextService = $contextService;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(methods={"GET"}, name="events_index")
     */
    public function index(Request $request)
    {
        $matches = $this->handleFilters(
            $request->get("filter"),
            $this->eventRepository,
            $this->getLocationId(),
            function ($filters, $repository, $locationId) {
                return $repository->getActive($locationId);
            }
        );

        return $this->okResponse($matches);
    }

    /**
     * @Route("/{id}", methods={"GET"}, name="events_read")
     */
    public function read(int $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->readEntity(Event::class, $id, ["detail"]);
    }

    /**
     * @Route(methods={"POST"}, name="events_create")
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        return $this->createEntity($request, Event::class, EventType::class);
    }

    /**
     * @Route("/{id}", methods={"PUT"}, name="events_update")
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->updateEntity($request, Event::class, EventType::class, $id);
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="events_delete")
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->deleteEntity(Event::class, $id, true);
    }
}