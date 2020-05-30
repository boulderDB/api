<?php

namespace App\Controller;

use App\Components\Controller\ApiControllerTrait;
use App\Entity\Boulder;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/statistic")
 */
class StatisticController extends AbstractController
{
    use ApiControllerTrait;

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
            ->where('boulder.location = :location')
            ->andWhere('boulder.status = :status')
            ->setParameter('location', $this->contextService->getLocation()->getId())
            ->setParameter('status', Boulder::STATUS_ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();

        $current = new \DateTime();

        $newBoulders = $this->entityManager->createQueryBuilder()
            ->select('count(boulder.id)')
            ->from(Boulder::class, 'boulder')
            ->where('boulder.location = :location')
            ->andWhere('boulder.createdAt >= :from')
            ->andWhere('boulder.status = :status')
            ->setParameter('from', $current->modify(Boulder::NEW_BOULDERS_DATE_MODIFIER))
            ->setParameter('status', Boulder::STATUS_ACTIVE)
            ->setParameter('location', $this->contextService->getLocation()->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json([
            'activeBoulders' => $boulderCount,
            'newBoulders' => $newBoulders
        ]);
    }

    /**
     * @Route("/wall", methods={"GET"})
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

    /**
     * @Route("/wall-reset-rotation", methods={"GET"})
     */
    public function resetRotation()
    {
        $connection = $this->entityManager->getConnection();
        $statement = "SELECT start_wall_id,created_at FROM boulder WHERE status = :status AND tenant_id = :tenantId";
        $query = $connection->prepare($statement);

        $query->execute([
            'tenantId' => $this->contextService->getLocation()->getId(),
            'status' => Boulder::STATUS_ACTIVE
        ]);

        $data = [];
        foreach ($query->fetchAll() as $result) {
            $data[$result['start_wall_id']][] = strtotime($result['created_at']);
        }

        $result = [];
        foreach (array_keys($data) as $wallId) {
            $average = array_sum($data[$wallId]) / count($data[$wallId]);

            $result[] = [
                'id' => $wallId,
                'averageSetDate' => (int)round($average)
            ];
        }

        usort($result, function ($a, $b) {
            return $a['averageSetDate'] > $b['averageSetDate'];
        });

        foreach ($result as & $average) {
            $average['averageSetDate'] = self::getApiDate($average['averageSetDate']);
        }

        return $this->json($result);
    }
}