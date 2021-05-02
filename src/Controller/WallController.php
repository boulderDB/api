<?php

namespace App\Controller;

use App\Entity\Wall;
use App\Form\WallType;
use App\Repository\WallRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wall")
 */
class WallController extends AbstractController
{
    use ContextualizedControllerTrait;
    use ResponseTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private WallRepository $wallRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        WallRepository $wallRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->wallRepository = $wallRepository;
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index(Request $request)
    {
        $connection = $this->entityManager->getConnection();

        $statement = WallRepository::getIndexStatement(
            $this->contextService->getLocation()->getId(),
            $request->query->get("filter")
        );

        $query = $connection->prepare($statement["sql"]);
        $query->execute($statement["parameters"]);

        $results = $query->fetchAllAssociative();

        return $this->json($results);
    }

    /**
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        $wall = new Wall();

        $form = $this->createForm(WallType::class, $wall);
        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        return $this->json(null, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        $wall = $this->wallRepository->find($id);

        if (!$wall) {
            return $this->resourceNotFoundResponse("Wall", $id);
        }

        $form = $this->createForm(WallType::class, $wall);
        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($wall);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function show(int $id)
    {
        if (!$this->wallRepository->exists($id, $this->contextService->getLocation()->getId())) {
            return $this->resourceNotFoundResponse('wall', $id);
        }

        $detail = $this->wallRepository->getDetail(
            $id,
            $this->contextService->getLocation()->getId()
        );

        return $this->okResponse($detail);
    }
}
