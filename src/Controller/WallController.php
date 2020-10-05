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
    public function index()
    {
        $connection = $this->entityManager->getConnection();
        $statement = 'select id, name from wall where tenant_id = :tenantId and active = true';
        $query = $connection->prepare($statement);

        $query->execute([
            'tenantId' => $this->contextService->getLocation()->getId()
        ]);

        $results = $query->fetchAll();

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
}