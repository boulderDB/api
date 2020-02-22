<?php

namespace App\Controller;

use App\Service\ContextService;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/tag")
 */
class TagController extends AbstractController
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
     * @Route("")
     */
    public function index()
    {
        $connection = $this->entityManager->getConnection();
        $statement = 'select id, name, emoji from tag where tenant_id = :tenantId';
        $query = $connection->prepare($statement);

        $query->execute([
            'tenantId' => $this->contextService->getLocation()->getId()
        ]);

        $results = $query->fetchAll();

        return $this->json($results);
    }
}