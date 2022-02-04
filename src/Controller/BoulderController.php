<?php

namespace App\Controller;

use App\Entity\Boulder;
use App\Entity\Event;
use App\Form\BoulderType;
use App\Form\MassOperationType;
use App\Repository\EventRepository;
use App\Scoring\DefaultPointsRanking;
use App\Scoring\DefaultScoring;
use App\Repository\BoulderRepository;
use App\Service\ContextService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/boulders")
 */
class BoulderController extends AbstractController
{
    use ContextualizedControllerTrait;
    use CrudTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private BoulderRepository $boulderRepository;
    private EventRepository $eventRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        BoulderRepository $boulderRepository,
        EventRepository $eventRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->boulderRepository = $boulderRepository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @Route("/archive", methods={"GET"}, name="boulders_archive")
     */
    public function archive(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        $parameters = [
            "location" => $this->contextService->getLocation()->getId()
        ];

        $total = $this->boulderRepository->getTotalItemsCount($parameters);

        /* add utility function */
        $page = (int)$request->get("page");
        $size = (int)$request->get("size") ? $request->get("size") : 50;
        $pages = ceil($total / $size);

        if ($page < 0) {
            return $this->badRequestResponse("Page '$page' is out of range");
        }

        return $this->okResponse(
            [
                "items" => $this->boulderRepository->paginate(
                    $page,
                    $parameters,
                    $size
                ),
                "total" => $total,
                "page" => $page,
                "size" => $size,
                "pages" => $pages,
                "hasNextPage" => $page < $pages,
                "hasPreviousPage" => $page > 0,
            ]
        );
    }

    /**
     * @Route(methods={"GET"}, name="boulders_index")
     */
    public function index(Request $request)
    {
        $eventId = $request->get("event");

        if ($eventId) {
            /**
             * @var Event|null $event
             */
            $event = $this->eventRepository->find($eventId);

            if (!$event) {
                return $this->resourceNotFoundResponse(Event::RESOURCE_NAME, $eventId);
            }

            return $this->okResponse($event->getBoulders());
        }

        return $this->okResponse(
            $this->boulderRepository->getByStatus($this->contextService->getLocation()?->getId())
        );
    }

    /**
     * @Route(methods={"POST"}, name="boulders_create")
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdminOrSetter();

        return $this->createEntity($request, Boulder::class, BoulderType::class);
    }

    /**
     * @Route("/count", methods={"GET"}, name="boulders_count")
     */
    public function count()
    {
        $count = $this->boulderRepository->countByStatus(
            $this->contextService->getLocation()?->getId()
        );

        return $this->okResponse($count);
    }

    /**
     * @Route("/{id}", requirements={"id": "\d+"}, methods={"GET"}, name="boulders_read")
     */
    public function read(Request $request, string $id)
    {
        $eventId = $request->get("event");

        if ($eventId) {
            /**
             * @var Event|null $event
             */
            $event = $this->eventRepository->find($eventId);

            if (!$event) {
                return $this->resourceNotFoundResponse(Event::RESOURCE_NAME, $id);
            }

            $boulder = $event->findBoulder($id);

            if (!$boulder) {
                return $this->badRequestResponse("Boulder $id is not part of event $eventId");
            }

            return $this->okResponse($boulder, ["detail"]);
        }


        return $this->readEntity(Boulder::class, $id, ["detail"]);
    }

    /**
     * @Route("/{identifier}", methods={"GET"}, name="boulders_read_readable_identifier")
     */
    public function readByReadableIdentifier(string $identifier)
    {
        $boulder = $this->boulderRepository->getByReadableInterface(
            $this->contextService->getLocation()->getId(),
            $identifier
        );

        if (!$boulder) {
            return $this->resourceNotFoundResponse(Boulder::class, $identifier);
        }

        return $this->okResponse($boulder, ["detail"]);
    }

    /**
     * @Route("/{id}", requirements={"id": "\d+"}, methods={"PUT"}, name="boulders_update")
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdminOrSetter();

        return $this->updateEntity($request, Boulder::class, BoulderType::class, $id);
    }

    /**
     * @Route("/{id}", requirements={"id": "\d+"}, methods={"DELETE"}, name="boulders_delete")
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdminOrSetter();

        return $this->deleteEntity(Boulder::class, $id, true);
    }

    /**
     * @Route("/mass", methods={"PUT"}, name="boulders_mass")
     */
    public function mass(Request $request)
    {
        $this->denyUnlessLocationAdminOrSetter();

        $form = $this->handleForm($request, null, MassOperationType::class);

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $items = $form->getData()["items"];
        $operation = $form->getData()["operation"];

        /**
         * @var Boulder $boulder
         */
        foreach ($items as $boulder) {
            if ($operation === MassOperationType::OPERATION_DEACTIVATE) {
                $boulder->setStatus(Boulder::STATUS_INACTIVE);
            }

            if ($operation === MassOperationType::OPERATION_REACTIVATE) {
                $boulder->setStatus(Boulder::STATUS_ACTIVE);
            }

            if ($operation === MassOperationType::OPERATION_PRUNE_ASCENTS) {
                $boulder->setAscents(new ArrayCollection());
            }

            $this->entityManager->persist($boulder);
        }

        $this->entityManager->flush();

        return $this->noContentResponse();
    }
}
