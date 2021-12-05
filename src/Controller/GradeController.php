<?php

namespace App\Controller;

use App\Entity\Grade;
use App\Form\GradeType;
use App\Repository\GradeRepository;
use App\Service\ContextService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/grades")
 */
class GradeController extends AbstractController
{
    use CrudTrait;
    use ContextualizedControllerTrait;
    use FilterTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private GradeRepository $gradeRepository;

    public function __construct(
        GradeRepository $gradeRepository,
        EntityManagerInterface $entityManager,
        ContextService $contextService
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->gradeRepository = $gradeRepository;
    }

    /**
     * @Route(methods={"GET"}, name="grades_index")
     */
    public function index(Request $request)
    {
        $matches = $this->handleFilters(
            $request->get("filter"),
            $this->gradeRepository,
            $this->getLocationId()
        );

        return $this->okResponse($matches);
    }

    /**
     * @Route("/{id}", methods={"GET"}, name="grades_read")
     */
    public function read(int $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->readEntity(Grade::class, $id, ["detail"]);
    }

    /**
     * @Route(methods={"POST"}, name="grades_create")
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        return $this->createEntity($request, Grade::class, GradeType::class);
    }

    /**
     * @Route("/{id}", methods={"PUT"}, name="grades_update")
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->updateEntity($request, Grade::class, GradeType::class, $id);
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="grades_delete")
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->deleteEntity(Grade::class, $id, true);
    }
}