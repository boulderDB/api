<?php

namespace App\Controller;

use App\Service\ContextService;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/holdstyle")
 */
class HoldStyleController extends AbstractController
{
    private $entityManager;
    private $contextService;

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
    public function index()
    {
        $connection = $this->entityManager->getConnection();
        $statement = 'select id, name, icon from hold_color where tenant_id = :locationId';
        $query = $connection->prepare($statement);

        $query->execute([
            'locationId' => $this->contextService->getLocation()->getId()
        ]);

        $results = $query->fetchAll();

        return $this->json($results);
    }
}