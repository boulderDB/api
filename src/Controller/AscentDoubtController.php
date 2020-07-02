<?php


namespace App\Controller;

use App\Entity\AscentDoubt;
use App\Repository\AscentDoubtRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/doubt")
 */
class AscentDoubtController extends AbstractController
{
    private $contextService;
    private $ascentDoubtRepository;

    public function __construct(
        ContextService $contextService,
        AscentDoubtRepository $ascentDoubtRepository
    )
    {
        $this->contextService = $contextService;
        $this->ascentDoubtRepository = $ascentDoubtRepository;
    }

    /**
     * @Route("/{id}/resolve", methods={"PUT"})
     */
    public function resolve(string $id)
    {

    }

    /**
     * @Route("/unread", methods={"GET"})
     */
    public function unread()
    {
        $doubts = $this->ascentDoubtRepository->getDoubts(
            $this->contextService->getLocation()->getId(),
            $this->getUser()->getId(),
            AscentDoubt::STATUS_UNREAD
        );

        return $this->json($doubts);
    }

    /**
     * @Route("/unresolved", methods={"GET"})
     */
    public function unresolved()
    {
        $doubts = $this->ascentDoubtRepository->getDoubts(
            $this->contextService->getLocation()->getId(),
            $this->getUser()->getId(),
            AscentDoubt::STATUS_UNRESOLVED
        );

        return $this->json($doubts);
    }

}