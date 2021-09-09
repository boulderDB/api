<?php

namespace App\Controller;

use App\Collection\TimeSlotCollection;
use App\Entity\TimeSlot;
use App\Form\TimeSlotType;
use App\Helper\TimeHelper;
use App\Repository\TimeSlotRepository;
use App\Service\ContextService;
use App\Service\Serializer;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/time-slot")
 */
class TimeSlotController extends AbstractController
{
    use RequestTrait;
    use ResponseTrait;
    use ContextualizedControllerTrait;

    private TimeSlotRepository $timeSlotRepository;
    private ContextService $contextService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        TimeSlotRepository $timeSlotRepository,
        ContextService $contextService,
        EntityManagerInterface $entityManager
    )
    {
        $this->timeSlotRepository = $timeSlotRepository;
        $this->contextService = $contextService;
        $this->entityManager    = $entityManager;
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        $timeSlots = $this->timeSlotRepository->getForLocationAndRoom(
            $this->contextService->getLocation()->getId(),
            $request->query->get("roomId")
        );

        $timeSlots = TimeSlotCollection::orderByDayAndTime($timeSlots);

        return $this->okResponse(Serializer::serialize($timeSlots));
    }

    /**
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        $timeSlot = new TimeSlot();

        $form = $this->createForm(TimeSlotType::class, $timeSlot);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($timeSlot);
        $this->entityManager->flush();

        return $this->createdResponse($timeSlot);
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        $timeSlot = $this->timeSlotRepository->find($id);

        if (!$timeSlot) {
            return $this->resourceNotFoundResponse("TimeSlot", $id);
        }

        $form = $this->createForm(TimeSlotType::class, $timeSlot);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($timeSlot);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function read(string $id)
    {
        $this->denyUnlessLocationAdmin();

        $timeSlot = $this->timeSlotRepository->find($id);

        if (!$timeSlot) {
            return $this->resourceNotFoundResponse("TimeSlot", $id);
        }

        return $this->okResponse(Serializer::serialize($timeSlot));
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        $timeSlot = $this->timeSlotRepository->find($id);

        if (!$timeSlot) {
            return $this->resourceNotFoundResponse("TimeSlot", $id);
        }

        $this->entityManager->remove($timeSlot);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }
}
