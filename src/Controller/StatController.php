<?php

namespace App\Controller;

use App\Entity\Boulder;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/stat")
 */
class StatController extends AbstractController
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
     * @Route("/boulder", methods={"GET"})
     */
    public function boulder()
    {
        $boulderCount = $this->entityManager->createQueryBuilder()
            ->select('count(boulder.id)')
            ->from(Boulder::class, 'boulder')
            ->where('boulder.tenant = :tenant')
            ->andWhere('boulder.status = :status')
            ->setParameter('tenant', $this->contextService->getLocation()->getId())
            ->setParameter('status', Boulder::STATUS_ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();

        $current = new \DateTime();

        $newBoulders = $this->entityManager->createQueryBuilder()
            ->select('count(boulder.id)')
            ->from(Boulder::class, 'boulder')
            ->where('boulder.tenant = :tenant')
            ->andWhere('boulder.createdAt >= :from')
            ->andWhere('boulder.status = :status')
            ->setParameter('from', $current->modify(Boulder::NEW_BOULDERS_DATE_MODIFIER))
            ->setParameter('status', Boulder::STATUS_ACTIVE)
            ->setParameter('tenant', $this->contextService->getLocation()->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json([
            'activeBoulders' => $boulderCount,
            'newBoulders' => $newBoulders
        ]);
    }

    /**
     * @Route("/wall")
     */
    public function wall()
    {
        $connection = $this->entityManager->getConnection();
        $statement = 'SELECT wall.id, wall.name, COUNT(boulder.id) FROM wall LEFT JOIN boulder ON boulder.start_wall_id = wall.id AND boulder.status = :status WHERE wall.tenant_id = :tenantId GROUP BY wall.id ORDER BY wall.name';
        $query = $connection->prepare($statement);

        $query->execute([
            'tenantId' => $this->contextService->getLocation()->getId(),
            'status' => Boulder::STATUS_ACTIVE
        ]);

        return $this->json($query->fetchAll());
    }
}