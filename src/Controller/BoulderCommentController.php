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
    use CrudTrait;
    use ContextualizedControllerTrait;

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
     * @Route(methods={"GET"})
     */
    public function index(Request $request)
    {
        $this->denyUnlessLocationAdminOrSetter();

        $filter = $request->query->get("filter") ? $request->query->get("filter") : "active";
        $comments = $this->boulderCommentRepository->getLatest($filter);

        return $this->okResponse($comments);
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
            return $this->badFormRequestResponse($form);
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
        return $this->deleteEntity(BoulderComment::class, $id);
    }
}
