<?php

namespace App\Controller;

use App\Entity\Wall;
use App\Form\WallType;
use App\Repository\WallRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/walls")
 */
class WallController extends AbstractController
{
    use CrudTrait;
    use ContextualizedControllerTrait;
    use FilterTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private WallRepository $wallRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        WallRepository $wallRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->wallRepository = $wallRepository;
    }

    /**
     * @Route(methods={"GET"}, name="walls_index")
     */
    public function index(Request $request)
    {
        $matches = $this->handleFilters(
            $request->get("filter"),
            $this->wallRepository,
            $this->getLocationId()
        );

        return $this->okResponse($matches);
    }

    /**
     * @Route("/{id}", methods={"GET"}, name="walls_read")
     */
    public function read(int $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->readEntity(Wall::class, $id, ["detail"]);
    }

    /**
     * @Route(methods={"POST"}, name="walls_create")
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        return $this->createEntity($request, Wall::class, WallType::class);
    }

    /**
     * @Route("/{id}", methods={"PUT"}, name="walls_update")
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->updateEntity($request, Wall::class, WallType::class, $id);
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="walls_delete")
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->deleteEntity(Wall::class, $id, true);
    }
}
