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
 * @Route("/holdtypes")
 */
class HoldTypeController extends AbstractController
{
    use CrudTrait;
    use ContextualizedControllerTrait;
    use FilterTrait;

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
     * @Route(methods={"GET"}, name="hold_types_index")
     */
    public function index(Request $request)
    {
        $matches = $this->handleFilters(
            $request->get("filter"),
            $this->holdTypeRepository,
            $this->getLocationId()
        );

        return $this->okResponse($matches);
    }

    /**
     * @Route("/{id}", methods={"GET"}, name="hold_types_rea")
     */
    public function read(int $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->readEntity(HoldType::class, $id, ["detail"]);
    }

    /**
     * @Route(methods={"POST"}, name="hold_types_create")
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        return $this->createEntity($request, HoldType::class, HoldTypeType::class);
    }

    /**
     * @Route("/{id}", methods={"PUT"}, name="hold_types_update")
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->updateEntity($request, HoldType::class, HoldTypeType::class, $id);
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="hold_types_delete")
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->deleteEntity(HoldType::class, $id, true);
    }
}
