<?php

namespace App\Controller;

use App\Components\Constants;
use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Entity\User;
use App\Factory\ResponseFactory;
use App\Service\CompareService;
use App\Service\ContextService;
use App\Struct\ComparisonStruct;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/compare")
 */
class CompareController extends AbstractController
{
    private $entityManager;
    private $compareService;
    private $contextService;

    public function __construct(
        EntityManagerInterface $entityManager,
        CompareService $compareService,
        ContextService $contextService
    )
    {
        $this->entityManager = $entityManager;
        $this->compareService = $compareService;
        $this->contextService = $contextService;
    }

    /**
     * @Route("/{userA}/to/{userB}/at/current", methods={"GET"})
     */
    public function compareCurrent(int $userA, int $userB)
    {
        if (!$this->checkUserComparable($userA)) {
            return $this->json(ResponseFactory::createError("User {$userA} is not comparable", Response::HTTP_BAD_REQUEST));
        }

        if (!$this->checkUserComparable($userB)) {
            return $this->json(ResponseFactory::createError("User {$userB} is not comparable", Response::HTTP_BAD_REQUEST));
        }

        $boulders = $this->getActiveBoulders();
        $ascentsA = $this->getAscents($userA);
        $ascentsB = $this->getAscents($userB);

        if (!$boulders) {
            return $this->json(null, Response::HTTP_NO_CONTENT);
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

        return $this->json($data);
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
                ->getSingleResult(AbstractQuery::HYDRATE_ARRAY);
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