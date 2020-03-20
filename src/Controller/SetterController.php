<?php

namespace App\Controller;

use App\Components\Constants;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/setter")
 */
class SetterController extends AbstractController
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
    public function setters()
    {
        $connection = $this->entityManager->getConnection();
        $statement = 'select id, username from users where roles like :role';
        $query = $connection->prepare($statement);

//        // todo: enable for v2
//        $query->execute([
//            'role' => '%"' . $this->contextService->getLocationRole(Constants::ROLE_SETTER) . '"%'
//        ]);

        $query->execute([
            'role' => "%" . addcslashes(Constants::ROLE_SETTER, '%_') . "%"
        ]);

        $results = $query->fetchAll();

        return $this->json($results);
    }
}