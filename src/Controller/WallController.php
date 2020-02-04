<?php

namespace App\Controller;

use App\Entity\Boulder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wall")
 */
class WallController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

//$connection = $this->entityManager->getConnection();
//$statement = 'SELECT wall.id, wall.name, COUNT(boulder.id) FROM wall LEFT JOIN boulder ON boulder.start_wall_id = wall.id AND boulder.status = :status WHERE wall.tenant_id = :tenantId GROUP BY wall.id';
//$query = $connection->prepare($statement);
//
//$query->execute([
//'tenantId' => 28,
//'status' => Boulder::STATUS_ACTIVE
//]);

    /**
     * @Route("")
     */
    public function index()
    {
        $connection = $this->entityManager->getConnection();
        $statement = 'select id, name from wall where tenant_id = :tenantId';
        $query = $connection->prepare($statement);

        $query->execute([
            'tenantId' => 28
        ]);

        $results = $query->fetchAll();

        return $this->json($results);
    }

}