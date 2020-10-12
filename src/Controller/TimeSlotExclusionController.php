<?php

namespace App\Controller;

use App\Entity\TimeSlotExclusion;
use App\Form\TimeSlotExclusionType;
use App\Repository\TimeSlotExclusionRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/time-slot-exclusion")
 */
class TimeSlotExclusionController extends AbstractController
{
    use RequestTrait;
    use ResponseTrait;
    use ContextualizedControllerTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private TimeSlotExclusionRepository $timeSlotExclusionRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        TimeSlotExclusionRepository $timeSlotExclusionRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->timeSlotExclusionRepository = $timeSlotExclusionRepository;
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index()
    {
        $this->denyUnlessLocationAdmin();

        $exclusions = $this->timeSlotExclusionRepository->pendingLocationExclusions(
            $this->contextService->getLocation()->getId()
        );

        return $this->okResponse($exclusions);
    }

    /**
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        $timeSlotExclusion = new TimeSlotExclusion();

        $form = $this->createForm(TimeSlotExclusionType::class, $timeSlotExclusion);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $timeSlotExclusion->generateHashId();

        $this->entityManager->persist($timeSlotExclusion);
        $this->entityManager->flush();

        return $this->createdResponse($timeSlotExclusion);
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        $timeSlotExclusion = $this->timeSlotExclusionRepository->find($id);

        if (!$timeSlotExclusion) {
            return $this->resourceNotFoundResponse("TimeSlotExclusion", $id);
        }

        $form = $this->createForm(TimeSlotExclusionType::class, $timeSlotExclusion);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $timeSlotExclusion->generateHashId();

        $this->entityManager->persist($timeSlotExclusion);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        $timeSlotExclusion = $this->timeSlotExclusionRepository->find($id);

        if (!$timeSlotExclusion) {
            return $this->resourceNotFoundResponse("TimeSlotExclusion", $id);
        }

        $this->entityManager->remove($timeSlotExclusion);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }
}