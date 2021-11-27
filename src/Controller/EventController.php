<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
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

    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @Route(methods={"GET"}, name="events_index")
     */
    public function index(Request $request)
    {
        $filters = $request->get("filter");

        if ($filters === "all") {
            $this->denyUnlessLocationAdmin();

            return $this->okResponse($this->eventRepository->getActive(
                $this->getLocationId()
            ));
        }

        return $this->okResponse($this->eventRepository->getActive(
            $this->getLocationId(),
            new \DateTime()
        ));
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