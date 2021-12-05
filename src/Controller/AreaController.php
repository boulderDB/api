<?php

namespace App\Controller;

use App\Entity\Area;
use App\Form\AreaType;
use App\Repository\AreaRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/areas")
 */
class AreaController extends AbstractController
{
    use CrudTrait;
    use ContextualizedControllerTrait;
    use FilterTrait;

    private AreaRepository $areaRepository;
    private ContextService $contextService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        AreaRepository $areaRepository,
        ContextService $contextService,
        EntityManagerInterface $entityManager
    )
    {
        $this->areaRepository = $areaRepository;
        $this->contextService = $contextService;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(methods={"GET"}, name="areas_index")
     */
    public function index(Request $request)
    {
        $matches = $this->handleFilters(
            $request->get("filter"),
            $this->areaRepository,
            $this->getLocationId()
        );

        return $this->okResponse($matches);
    }

    /**
     * @Route("/{id}", methods={"GET"}, name="areas_read")
     */
    public function read(int $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->readEntity(Area::class, $id, ["detail"]);
    }

    /**
     * @Route(methods={"POST"}, name="areas_create")
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        return $this->createEntity($request, Area::class, AreaType::class);
    }

    /**
     * @Route("/{id}", methods={"PUT"}, name="areas_update")
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->updateEntity($request, Area::class, AreaType::class, $id);
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="areas_delete")
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->deleteEntity(Area::class, $id, true);
    }
}