<?php

namespace App\Controller;

use App\Entity\Ascent;
use App\Form\AscentType;
use App\Form\MassOperationType;
use App\Repository\AscentRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\ContextService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        AscentRepository $ascentRepository,
        EventRepository $eventRepository,
        UserRepository $userRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->ascentRepository = $ascentRepository;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route(methods={"POST"}, name="ascents_create")
     */
    public function create(Request $request)
    {
        $ascent = new Ascent();

        if ($request->query->has("forUser") && $this->isLocationAdmin()) {
            $userId = $request->query->get("forUser");

            /**
             * @var \App\Entity\User $user
             */
            $user = $this->userRepository->find($userId);

            if (!$user) {
                return $this->badRequestResponse("User $userId not found");
            }

            if (!$user->isActive()) {
                return $this->badRequestResponse("User $userId is inactive");
            }

            $ascent->setUser($user);
            $ascent->setSource(Ascent::SOURCE_ADMIN);
        } else {
            $ascent->setUser($this->getUser());
        }

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

        return $this->createdResponse($ascent);
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="ascents_delete")
     */
    public function delete(Request $request, string $id)
    {
        $ascent = $this->ascentRepository->find($id);

        if (!$ascent) {
            return $this->resourceNotFoundResponse(Ascent::RESOURCE_NAME, $id);
        }

        if ($request->query->has("forUser") && $this->isLocationAdmin()) {
            $userId = $request->query->get("forUser");

            /**
             * @var \App\Entity\User $user
             */
            $user = $this->userRepository->find($userId);

            if (!$user) {
                return $this->badRequestResponse("User $userId not found");
            }

            if (!$user->isActive()) {
                return $this->badRequestResponse("User $userId is inactive");
            }

            $this->entityManager->remove($ascent);
            $this->entityManager->flush();

            return $this->noContentResponse();
        }

        return $this->deleteEntity(Ascent::class, $id);
    }
}
