<?php

namespace App\Controller;

use App\Entity\Ascent;
use App\Form\AscentType;
use App\Form\MassOperationType;
use App\Repository\AscentRepository;
use App\Repository\EventRepository;
use App\Service\ContextService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ascents")
 */
class AscentController extends AbstractController
{
    use CrudTrait;
    use ResponseTrait;
    use RequestTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private AscentRepository $ascentRepository;
    private EventRepository $eventRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        AscentRepository $ascentRepository,
        EventRepository $eventRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->ascentRepository = $ascentRepository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @Route(methods={"POST"}, name="ascents_create")
     */
    public function create(Request $request)
    {
        $ascent = new Ascent();
        $ascent->setUser($this->getUser());

        $form = $this->handleForm($request, $ascent, AscentType::class);

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        try {
            $this->entityManager->persist($ascent);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            return $this->conflictResponse("You already checked this boulder");
        }
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="ascents_delete")
     */
    public function delete(string $id)
    {
        $ascent = $this->ascentRepository->find($id);

        if (!$ascent) {
            return $this->resourceNotFoundResponse(Ascent::RESOURCE_NAME, $id);
        }

        if ($this->eventRepository->getEndedByBoulder($ascent->getBoulder()?->getId())) {
           return $this->badRequestResponse("Ascent cannot be deleted as it is part of an event ranking");
        }

        return $this->deleteEntity(Ascent::class, $id);
    }
}
