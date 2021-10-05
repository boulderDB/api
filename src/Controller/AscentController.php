<?php

namespace App\Controller;

use App\Entity\Ascent;
use App\Form\AscentType;
use App\Repository\AscentRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use FOS\HttpCacheBundle\Configuration\InvalidateRoute;

/**
 * @Route("/ascents")
 */
class AscentController extends AbstractController
{
    use CrudTrait;
    use ResponseTrait;
    use RequestTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private AscentRepository $ascentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        AscentRepository $ascentRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->ascentRepository = $ascentRepository;
    }

    /**
     * @Route(methods={"POST"}, name="ascents_create")
     *
     * @InvalidateRoute("boulders_index", params={"id" = {"expression"="id"}})")
     */
    public function create(Request $request)
    {
        return $this->createEntity($request, Ascent::class, AscentType::class);
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="ascents_delete")
     *
     * @InvalidateRoute("areas_read", params={"id" = {"expression"="id"}})")
     */
    public function delete(string $id)
    {
        return $this->deleteEntity(Ascent::class, $id);
    }
}
