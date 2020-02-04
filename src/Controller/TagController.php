<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/tag")
 */
class TagController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
            'tenantId' => 28
        ]);

        $results = $query->fetchAll();

        return $this->json($results);
    }
}