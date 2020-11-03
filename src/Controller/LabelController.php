<?php

namespace App\Controller;

use App\Repository\LabelRepository;
use App\Service\Serializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/label")
 */
class LabelController extends AbstractController
{
    use ContextualizedControllerTrait;
    use RequestTrait;
    use ResponseTrait;

    private LabelRepository $labelRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        LabelRepository $labelRepository,
        EntityManagerInterface $entityManager)
    {
        $this->labelRepository = $labelRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(methods={"get"})
     */
    public function index()
    {
        $label = $this->labelRepository->findByUser($this->getUser()->getId());

        return $this->okResponse(Serializer::serialize($label));
    }

    /**
     * @Route(methods={"create"})
     */
    public function create(Request $request)
    {

    }

    /**
     * @Route(methods={"delete"})
     */
    public function delete(Request $request)
    {

    }
}
