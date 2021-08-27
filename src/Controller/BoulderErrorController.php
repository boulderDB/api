<?php

namespace App\Controller;

use App\Entity\BoulderError;
use App\Form\BoulderErrorType;
use App\Repository\BoulderErrorRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/boulder-error")
 */
class BoulderErrorController extends AbstractController
{
    use CrudTrait;
    use ContextualizedControllerTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private BoulderErrorRepository $boulderErrorRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        BoulderErrorRepository $boulderErrorRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->boulderErrorRepository = $boulderErrorRepository;
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index(Request $request)
    {
        $this->denyUnlessLocationAdminOrSetter();

        $filters = $request->get("filter");

        if ($filters) {
            return $this->okResponse($this->boulderErrorRepository->queryWhere(
                $this->getLocationId(),
                ["status" => "string"],
                $filters
            ));
        }

        return $this->okResponse(
            $this->boulderErrorRepository->findByStatus($this->contextService->getLocation()->getId())
        );
    }

    /**
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $boulderError = new BoulderError();
        $boulderError->setAuthor($this->getUser());

        return $this->createEntity($request, $boulderError, BoulderErrorType::class);
    }

    /**
     * @Route("/count", methods={"GET"})
     */
    public function count()
    {
        $this->denyUnlessLocationAdminOrSetter();
        $count = $this->boulderErrorRepository->countByStatus($this->contextService->getLocation()->getId());

        return $this->okResponse($count);
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdminOrSetter();

        return $this->updateEntity(
            $request,
            BoulderError::class,
            BoulderErrorType::class,
            $id
        );
    }
}
