<?php

namespace App\Controller;

use App\Repository\RoomRepository;
use App\Service\ContextService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/room")
 */
class RoomController extends AbstractController
{
    use ResponseTrait;

    private ContextService $contextService;
    private RoomRepository $roomRepository;

    public function __construct(
        ContextService $contextService,
        RoomRepository $roomRepository
    )
    {
        $this->contextService = $contextService;
        $this->roomRepository = $roomRepository;
    }

    /**
     * @Route(methods={"get"})
     */
    public function index()
    {
        return $this->okResponse($this->roomRepository->all($this->contextService->getLocation()->getId()));
    }
}