<?php

namespace App\Controller;

use App\Entity\Ascent;
use App\Entity\BoulderComment;
use App\Entity\BoulderError;
use App\Form\BoulderCommentType;
use App\Repository\BoulderCommentRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/comment")
 */
class BoulderCommentController extends AbstractController
{
    use ContextualizedControllerTrait;
    use FormErrorTrait;
    use RequestTrait;
    use ResponseTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private BoulderCommentRepository $boulderCommentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        BoulderCommentRepository $boulderCommentRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->boulderCommentRepository = $boulderCommentRepository;
    }

    /**
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $comment = new BoulderComment();
        $comment->setAuthor($this->getUser());

        $form = $this->createForm(BoulderCommentType::class, $comment);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form));
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return $this->createdResponse($comment);
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function delete(int $id)
    {
        /**
         * @var BoulderComment $comment
         */
        $comment = $this->boulderCommentRepository->find($id);

        if (!$comment) {
            return $this->resourceNotFoundResponse("BoulderComment", $id);
        }

        if (!$comment->getAuthor() === $this->getUser()) {
            return $this->unauthorizedResponse();
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
