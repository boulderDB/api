<?php

namespace App\Controller;

use App\Entity\HoldType;
use App\Form\HoldTypeType;
use App\Repository\HoldTypeRepository;
use App\Service\ContextService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/holdtype")
 */
class HoldTypeController extends AbstractController
{
    use CrudTrait;
    use ContextualizedControllerTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private HoldTypeRepository $holdTypeRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        HoldTypeRepository $holdTypeRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->holdTypeRepository = $holdTypeRepository;
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index(Request $request)
    {
        $filters = $request->get("filter");

        if ($filters) {
            return $this->okResponse($this->holdTypeRepository->queryWhere(
                $this->getLocationId(),
                ["active" => "bool"],
                $filters
            ));
        }

        return $this->okResponse($this->holdTypeRepository->getActive(
            $this->getLocationId()
        ));
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function read(int $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->readEntity(HoldType::class, $id);
    }

    /**
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        return $this->createEntity($request, HoldType::class, HoldTypeType::class);
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->updateEntity($request, HoldType::class, HoldTypeType::class, $id);
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->deleteEntity(HoldType::class, $id, true);
    }
}
