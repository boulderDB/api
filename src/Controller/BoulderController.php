<?php

namespace App\Controller;

use App\Entity\Boulder;
use App\Form\BoulderType;
use App\Form\MassOperationType;
use App\Repository\BoulderRepository;
use App\Scoring\DefaultScoring;
use App\Service\ContextService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/boulders")
 */
class BoulderController extends AbstractController
{
    use ContextualizedControllerTrait;
    use CrudTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private BoulderRepository $boulderRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        BoulderRepository $boulderRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->boulderRepository = $boulderRepository;
    }

    /**
     * @Route("/archive", methods={"GET"}, name="boulders_archive")
     */
    public function archive(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        $parameters = [
            "location" => $this->contextService->getLocation()->getId()
        ];

        $total = $this->boulderRepository->getTotalItemsCount($parameters);

        $page = (int)$request->get("page");
        $size = (int)$request->get("size") ? $request->get("size") : 50;
        $pages = ceil($total / $size);

        return $this->okResponse(
            [
                "items" => $this->boulderRepository->paginate(
                    $page,
                    $parameters,
                    $size
                ),
                "total" => $total,
                "page" => $page,
                "size" => $size,
                "pages" => $pages,
                "hasNextPage" => $page < $pages,
                "hasPreviousPage" => $page > $pages,
            ]
        );
    }

    /**
     * @Route(methods={"GET"}, name="boulders_index")
     */
    public function index()
    {

        $userId = $this->getUser()->getId();
        $boulders = $this->boulderRepository->getByStatus($this->contextService->getLocation()?->getId());
        $scoring = new DefaultScoring();

        /* todo: add postload listener */
        /**
         * @var Boulder $boulder
         */
        foreach ($boulders as $boulder) {
            $scoring->calculateScore($boulder);
            $boulder->setUserAscent($userId);
        }

        return $this->okResponse($boulders);
    }

    /**
     * @Route(methods={"POST"}, name="boulders_create")
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdminOrSetter();

        return $this->createEntity($request, Boulder::class, BoulderType::class);
    }

    /**
     * @Route("/{id}", requirements={"id": "\d+"}, methods={"GET"}, name="boulders_read")
     */
    public function read(string $id)
    {
        return $this->readEntity(Boulder::class, $id, ["detail"]);
    }

    /**
     * @Route("/{id}", requirements={"id": "\d+"}, methods={"PUT"}, name="boulders_update")
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdminOrSetter();

        return $this->updateEntity($request, Boulder::class, BoulderType::class, $id);
    }

    /**
     * @Route("/{id}", requirements={"id": "\d+"}, methods={"DELETE"}, name="boulders_delete")
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdminOrSetter();

        return $this->deleteEntity(Boulder::class, $id, true);
    }

    /**
     * @Route("/count", methods={"GET"}, name="boulders_count")
     */
    public function count()
    {
        $count = $this->boulderRepository->countByStatus(
            $this->contextService->getLocation()?->getId()
        );

        return $this->okResponse($count);
    }

    /**
     * @Route("/mass", methods={"PUT"}, name="boulders_mass")
     */
    public function mass(Request $request)
    {
        $this->denyUnlessLocationAdminOrSetter();

        $form = $this->handleForm($request, null, MassOperationType::class);

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $items = $form->getData()["items"];
        $operation = $form->getData()["operation"];

        /**
         * @var Boulder $boulder
         */
        foreach ($items as $boulder) {
            if ($operation === MassOperationType::OPERATION_DEACTIVATE) {
                $boulder->setStatus(Boulder::STATUS_INACTIVE);
            }

            if ($operation === MassOperationType::OPERATION_PRUNE_ASCENTS) {
                $boulder->setAscents(new ArrayCollection());
            }

            $this->entityManager->persist($boulder);
        }

        $this->entityManager->flush();

        return $this->noContentResponse();
    }
}
