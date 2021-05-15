<?php

namespace App\Controller;

use App\Repository\GradeRepository;
use App\Service\ContextService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/grade")
 */
class GradeController extends AbstractController
{
    use ContextualizedControllerTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index(Request $request)
    {
        $connection = $this->entityManager->getConnection();

        $statement = GradeRepository::getIndexStatement(
            $this->contextService->getLocation()->getId(),
            $request->query->get("filter"),
            $this->isLocationAdmin()
        );

        $query = $connection->prepare($statement["sql"]);
        $query->execute($statement["parameters"]);

        return $this->json($query->fetchAllAssociative());
    }
}