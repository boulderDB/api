<?php

namespace App\Controller;

use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Entity\User;
use App\Service\ContextService;
use App\Struct\ComparisonStruct;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/compare")
 */
class CompareController extends AbstractController
{
    use ResponseTrait;

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
     * @Route("/{userIdA}/to/{userIdB}/at/current", methods={"GET"})
     */
    public function compareCurrent(int $userIdA, int $userIdB)
    {
        if (!$this->checkUserComparable($userIdA)) {
            return $this->badRequestResponse("User {$userIdA} is not comparable");
        }

        if (!$this->checkUserComparable($userIdB)) {
            return $this->badRequestResponse("User {$userIdB} is not comparable");
        }

        $boulders = $this->getActiveBoulders();
        $ascentsA = $this->getAscents($userIdA);
        $ascentsB = $this->getAscents($userIdB);

        if (!$boulders) {
            return $this->noContentResponse();
        }

        /**
         * @var ComparisonStruct[] $comparisons
         */
        $comparisons = [];

        foreach ($boulders as $boulderId) {
            $a = $ascentsA[$boulderId] ?? $ascentsA[$boulderId] ?? null;
            $b = $ascentsB[$boulderId] ?? $ascentsB[$boulderId] ?? null;

            $comparisons[] = new ComparisonStruct($boulderId, $a, $b);
        }

        foreach ($comparisons as $comparison) {

            if ($comparison->getA() === Ascent::ASCENT_FLASH) {
                $comparison->setPositionA(2);
            } else if ($comparison->getA() === Ascent::ASCENT_TOP) {
                $comparison->setPositionA(1);
            } else {
                $comparison->setPositionA(0);
            }

            if ($comparison->getB() === Ascent::ASCENT_FLASH) {
                $comparison->setPositionB(2);
            } else if ($comparison->getB() === Ascent::ASCENT_TOP) {
                $comparison->setPositionB(1);
            } else {
                $comparison->setPositionB(0);
            }
        }

        $data = array_map(function ($comparison) {
            /**
             * @var ComparisonStruct $comparison
             */
            return [
                'subject' => $comparison->getSubject(),
                'a' => $comparison->getA(),
                'b' => $comparison->getB(),
                'positionA' => $comparison->getPositionA(),
                'positionB' => $comparison->getPositionB()
            ];
        }, $comparisons);

        return $this->okResponse($data);
    }

    private function checkUserComparable(int $userId)
    {
        try {
            $user = $this->entityManager->createQueryBuilder()
                ->select('user.id, user.visible')
                ->from(User::class, 'user')
                ->where('user.id = :userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getSingleResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
            
        } catch (NoResultException $exception) {
            return false;
        }

        if (!$user['visible']) {
            return false;
        }

        return true;
    }

    private function getActiveBoulders(): ?array
    {
        $boulders = $this->entityManager->createQueryBuilder()
            ->select('boulder.id')
            ->from(Boulder::class, 'boulder')
            ->where('boulder.location = :location')
            ->andWhere('boulder.status = :status')
            ->setParameter('status', Boulder::STATUS_ACTIVE)
            ->setParameter('location', $this->contextService->getLocation()->getId())
            ->getQuery()
            ->getArrayResult();

        if (!$boulders) {
            return null;
        }

        return array_map(function ($boulder) {
            return $boulder['id'];
        }, $boulders);
    }

    private function getAscents(int $userId)
    {
        $ascents = $this->entityManager->createQueryBuilder()
            ->select('boulder.id, ascent.type')
            ->from(Ascent::class, 'ascent')
            ->innerJoin('ascent.boulder', 'boulder')
            ->where('boulder.location = :location')
            ->andWhere('boulder.status = :status')
            ->andWhere('ascent.user = :user')
            ->setParameter('location', $this->contextService->getLocation()->getId())
            ->setParameter('status', Boulder::STATUS_ACTIVE)
            ->setParameter('user', $userId)
            ->getQuery()
            ->getArrayResult();

        $data = [];

        foreach ($ascents as $ascent) {
            $data[$ascent["id"]] = $ascent["type"];
        }

        return $data;
    }
}
