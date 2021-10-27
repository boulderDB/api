<?php

namespace App\Controller;

use App\Entity\Ascent;
use App\Entity\AscentDoubt;
use App\Form\AscentDoubtType;
use App\Form\AscentDoubtUpdateType;
use App\Repository\AscentDoubtRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ascent-doubts")
 */
class AscentDoubtController extends AbstractController
{
    use CrudTrait;
    use ContextualizedControllerTrait;

    private ContextService $contextService;
    private AscentDoubtRepository $ascentDoubtRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ContextService $contextService,
        AscentDoubtRepository $ascentDoubtRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->contextService = $contextService;
        $this->ascentDoubtRepository = $ascentDoubtRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(methods={"GET"}, name="ascent_doubts_index")
     */
    public function index(Request $request)
    {
        $filters = $request->get("filter");
        $userId = $this->getUser()->getId();

        if ($filters && $filters["status"]) {
            return $this->okResponse($this->ascentDoubtRepository->getByStatus(
                $userId,
                $filters["status"]
            ));
        }

        return $this->okResponse($this->ascentDoubtRepository->getByStatus(
            $userId,
            $this->getLocationId()
        ));
    }

    /**
     * @Route(methods={"POST"}, name="ascent_doubts_create")
     */
    public function create(Request $request)
    {
        return $this->createEntity(
            $request,
            AscentDoubt::class,
            AscentDoubtType::class,
            function ($entity) {
                /**
                 * @var Ascent $ascent
                 */
                $ascent = $entity->getAscent();
                $ascent->setDoubted();
                $entity->setAuthor($this->getUser());

                $this->getDoctrine()->getManager()->persist($ascent);
            }
        );
    }

    /**
     * @Route("/{id}", methods={"PUT"}, name="ascent_doubts_update")
     */
    public function update(Request $request, string $id)
    {
        return $this->updateEntity(
            $request,
            AscentDoubt::class,
            AscentDoubtUpdateType::class,
            $id
        );
    }

    /**
     * @Route("/count", methods={"GET"}, name="ascent_doubts_count")
     */
    public function count()
    {
        $doubtCount = $this->ascentDoubtRepository->countDoubts(
            $this->contextService->getLocation()?->getId(),
            $this->getUser()->getId()
        );

        return $this->okResponse($doubtCount);
    }
}
