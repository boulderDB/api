<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\RoomType;
use App\Repository\RoomRepository;
use App\Service\ContextService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/room")
 */
class RoomController extends AbstractController
{
    use RequestTrait;
    use ResponseTrait;
    use ContextualizedControllerTrait;

    private ContextService $contextService;
    private RoomRepository $roomRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ContextService $contextService,
        RoomRepository $roomRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->contextService = $contextService;
        $this->roomRepository = $roomRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index()
    {
        return $this->okResponse($this->roomRepository->all($this->contextService->getLocation()->getId()));
    }

    /**
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        $room = new Room();

        $form = $this->createForm(RoomType::class, $room);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($room);
        $this->entityManager->flush();

        return $this->createdResponse($room);
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        $room = $this->roomRepository->find($id);

        if (!$room) {
            return $this->resourceNotFoundResponse("Room", $id);
        }

        $form = $this->createForm(RoomType::class, $room);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($room);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        $room = $this->roomRepository->find($id);

        if (!$room) {
            return $this->resourceNotFoundResponse("Room", $id);
        }

        $this->entityManager->remove($room);

        try {
            $this->entityManager->flush();
        } catch (ForeignKeyConstraintViolationException $exception) {
            return $this->json([
                "message" => "This room is referenced and cannot be deleted.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        return $this->noContentResponse();
    }
}