<?php


namespace App\Controller;

use App\Entity\ReadableIdentifier;
use App\Form\ReadableIdentifierType;
use App\Repository\ReadableIdentifierRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/readable-identifiers")
 */
class ReadableIdentifierController extends AbstractController
{
    use ResponseTrait;
    use CrudTrait;
    use ContextualizedControllerTrait;

    private ReadableIdentifierRepository $readableIdentifierRepository;
    private ContextService $contextService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ReadableIdentifierRepository $readableIdentifierRepository,
        ContextService $contextService,
        EntityManagerInterface $entityManager
    )
    {
        $this->readableIdentifierRepository = $readableIdentifierRepository;
        $this->contextService = $contextService;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(methods={"GET"}, name="readable_identifier_index")
     */
    public function index()
    {
        $this->denyUnlessLocationAdmin();

        return $this->okResponse(
            $this->readableIdentifierRepository->getAll($this->contextService->getLocation()->getId())
        );
    }

    /**
     * @Route(methods={"POST"}, name="readable_identifier_create")
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        return $this->createEntity($request, ReadableIdentifier::class, ReadableIdentifierType::class);
    }

    /**
     * @Route("/{id}", methods={"GET"}, name="readable_identifier_read")
     */
    public function read(int $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->readEntity(ReadableIdentifier::class, $id, ["detail"]);
    }

    /**
     * @Route("/{id}", methods={"PUT"}, name="readable_identifier_update")
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->updateEntity($request, ReadableIdentifier::class, ReadableIdentifierType::class, $id);
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="readable_identifier_delete")
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->deleteEntity(ReadableIdentifier::class, $id);
    }
}