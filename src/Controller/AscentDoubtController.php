<?php


namespace App\Controller;

use App\Entity\AscentDoubt;
use App\Repository\AscentDoubtRepository;
use App\Service\ContextService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/doubt")
 */
class AscentDoubtController extends AbstractController
{
    private ContextService $contextService;
    private AscentDoubtRepository $ascentDoubtRepository;

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
     * @Route(methods={"GET"})
     */
    public function index(Request $request)
    {
        $doubts = $this->ascentDoubtRepository->getDoubts(
            $this->contextService->getLocation()->getId(),
            $this->getUser()->getId(),
            AscentDoubt::STATUS_UNRESOLVED
        );

        return $this->json($doubts);
    }

}
