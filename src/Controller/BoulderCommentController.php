<?php

namespace App\Controller;

use App\Entity\BoulderComment;
use App\Form\BoulderCommentType;
use App\Repository\BoulderCommentRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/boulder-comments")
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
     * @Route(methods={"GET"}, name="boulder_comments_index")
     */
    public function index(Request $request)
    {
        $this->denyUnlessLocationAdminOrSetter();

        return $this->okResponse($this->boulderCommentRepository->findForActiveBoulders($this->getLocationId()));
    }

    /**
     * @Route(methods={"POST"}, name="boulder_comments_create")
     */
    public function create(Request $request)
    {
        return $this->createEntity(
            $request,
            BoulderComment::class,
            BoulderCommentType::class,
            function ($entity) {
                /**
                 * @var BoulderComment $entity
                 */
                $entity->setAuthor($this->getUser());
            }
        );
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="boulder_comments_delete")
     */
    public function delete(int $id)
    {
        return $this->deleteEntity(BoulderComment::class, $id);
    }
}
