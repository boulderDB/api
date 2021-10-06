<?php

namespace App\Controller;

use App\Entity\BoulderTag;
use App\Form\BoulderTagType;
use App\Repository\BoulderTagRepository;
use App\Service\ContextService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/boulder-tags")
 */
class BoulderTagController extends AbstractController
{
    use CrudTrait;
    use ContextualizedControllerTrait;

    private ContextService $contextService;
    private EntityManagerInterface $entityManager;
    private BoulderTagRepository $boulderTagRepository;

    public function __construct(
        ContextService $contextService,
        EntityManagerInterface $entityManager,
        BoulderTagRepository $boulderTagRepository
    )
    {
        $this->contextService = $contextService;
        $this->entityManager = $entityManager;
        $this->boulderTagRepository = $boulderTagRepository;
    }

    /**
     * @Route(methods={"GET"}, name="boulder_tags_index")
     */
    public function index(Request $request)
    {
        $filters = $request->get("filter");

        if ($filters) {
            return $this->okResponse($this->boulderTagRepository->queryWhere(
                $this->getLocationId(),
                ["active" => "bool"],
                $filters
            ));
        }

        return $this->okResponse($this->boulderTagRepository->getActive(
            $this->getLocationId()
        ));
    }

    /**
     * @Route("/{id}", methods={"GET"}, name="boulder_tags_read")
     */
    public function read(int $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->readEntity(BoulderTag::class, $id, ["detail"]);
    }

    /**
     * @Route(methods={"POST"}, name="boulder_tags_create")
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        return $this->createEntity($request, BoulderTag::class, BoulderTagType::class);
    }

    /**
     * @Route("/{id}", methods={"PUT"}, name="boulder_tags_update")
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->updateEntity($request, BoulderTag::class, BoulderTagType::class, $id);
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="boulder_tags_delete")
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->deleteEntity(BoulderTag::class, $id, true);
    }
}