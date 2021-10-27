<?php

namespace App\Controller;

use App\Collection\AscentCollection;
use App\Entity\Ascent;
use App\Entity\User;
use App\Repository\AscentRepository;
use App\Repository\BoulderRepository;
use App\Repository\UserRepository;
use App\Service\ContextService;
use App\Struct\ComparisonStruct;
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
    private AscentRepository $ascentRepository;
    private BoulderRepository $boulderRepository;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        AscentRepository $ascentRepository,
        BoulderRepository $boulderRepository,
        UserRepository $userRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->ascentRepository = $ascentRepository;
        $this->boulderRepository = $boulderRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/{userIdA}/to/{userIdB}/at/current", methods={"GET"})
     */
    public function compareCurrent(int $userIdA, int $userIdB)
    {
        /**
         * @var User $userB
         */
        $userA = $this->userRepository->find($userIdA);

        /**
         * @var User $userB
         */
        $userB = $this->userRepository->find($userIdB);

        if (!$userA || !$userB) {
            return $this->badRequestResponse("User not found");
        }

        if (!$userA->isVisible() || !$userB->isVisible()) {
            return $this->badRequestResponse("User is not comparable");
        }

        $locationId = $this->contextService->getLocation()?->getId();
        $boulders = $this->boulderRepository->getByStatus($locationId);

        $ascentACollection = new AscentCollection($this->ascentRepository->getByUserAndLocation($userIdA, $locationId));
        $ascentBCollection = new AscentCollection($this->ascentRepository->getByUserAndLocation($userIdB, $locationId));

        if (!$boulders) {
            return $this->noContentResponse();
        }

        /**
         * @var ComparisonStruct[] $comparisons
         */
        $comparisons = [];

        foreach ($boulders as $boulder) {
            $a = $ascentACollection->findForBoulder($boulder)->first();
            $b = $ascentBCollection->findForBoulder($boulder)->first();

            $comparisons[] = new ComparisonStruct($boulder, $a, $b);
        }

        foreach ($comparisons as $comparison) {
            /* @var Ascent $a */
            $a = $comparison->getA();
            /* @var Ascent $b */
            $b = $comparison->getB();

            if ($a) {
                if ($a->getType() === Ascent::ASCENT_FLASH) {
                    $comparison->setPositionA(2);
                } else if ($a->getType() === Ascent::ASCENT_TOP) {
                    $comparison->setPositionA(1);
                }
            } else {
                $comparison->setPositionA(0);
            }

            if ($b) {
                if ($b->getType() === Ascent::ASCENT_FLASH) {
                    $comparison->setPositionB(2);
                } else if ($b->getType() === Ascent::ASCENT_TOP) {
                    $comparison->setPositionB(1);
                }
            } else {
                $comparison->setPositionB(0);
            }
        }

        return $this->okResponse($comparisons);
    }
}
