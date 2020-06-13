<?php


namespace App\Controller;

use App\Entity\AscentDoubt;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/doubt")
 */
class BoulderDoubtController extends AbstractController
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
     * @Route("/{id}/resolve", methods={"PUT"})
     */
    public function resolve(string $id)
    {

    }

    /**
     * @Route("/unresolved", methods={"GET"})
     */
    public function unresolved()
    {
        $connection = $this->entityManager->getConnection();

        $statement = "SELECT
                        doubt.id,
                        author.username,
                        doubt.description,
                        doubt.created_at
                    FROM
                        boulder_doubt AS doubt
                        INNER JOIN users AS author ON author.id = users.id
                    WHERE
                        tenant_id = :locationId
                        AND status != :status
                        AND recipient_id = :recipientId";

        $query = $connection->prepare($statement);

        $query->execute([
            'locationId' => $this->contextService->getLocation()->getId(),
            'recipientId' => $this->getUser()->getId(),
            'status' => AscentDoubt::STATUS_RESOLVED
        ]);

        return $this->json($query->fetchAll());
    }

}