<?php

namespace App\Controller;

use App\Entity\BoulderError;
use App\Form\BoulderErrorType;
use App\Repository\BoulderErrorRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @Route("/error")
 */
class BoulderErrorController extends AbstractController
{
    use ContextualizedControllerTrait;
    use FormErrorTrait;
    use RequestTrait;
    use ResponseTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private BoulderErrorRepository $boulderErrorRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        BoulderErrorRepository $boulderErrorRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->boulderErrorRepository = $boulderErrorRepository;
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index()
    {
        $this->denyUnlessLocationAdmin();

        $errors = $this->boulderErrorRepository->findByStatus(
            $this->contextService->getLocation()->getId(),
            BoulderError::STATUS_UNRESOLVED
        );

        return $this->json($errors);
    }

    /**
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $boulderError = new BoulderError();
        $boulderError->setAuthor($this->getUser());

        $form = $this->createForm(BoulderErrorType::class, $boulderError);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form));
        }

        $this->entityManager->persist($boulderError);
        $this->entityManager->flush();

        return $this->createdResponse($boulderError);
    }

    /**
     * @Route("/count", methods={"GET"})
     */
    public function count()
    {
        $this->denyUnlessLocationAdmin();

        $connection = $this->entityManager->getConnection();
        $statement = "select count(id) from boulder_error where tenant_id = :locationId and status = :status";
        $query = $connection->prepare($statement);

        $query->execute([
            "locationId" => $this->contextService->getLocation()->getId(),
            "status" => BoulderError::STATUS_UNRESOLVED
        ]);

        $results = $query->fetch();

        return $this->json($results);
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function resolve(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();
        $error = $this->boulderErrorRepository->find($id);

        if (!$error) {
            return $this->resourceNotFoundResponse("BoulderError", $id);
        }

        $form = $this->createFormBuilder($error, ["csrf_protection" => false])
            ->add("status", ChoiceType::class, [
                "choices" => [
                    BoulderError::STATUS_UNRESOLVED => BoulderError::STATUS_UNRESOLVED,
                    BoulderError::STATUS_RESOLVED => BoulderError::STATUS_RESOLVED
                ],
                "constraints" => [
                    new NotBlank()
                ]
            ])
            ->getForm();

        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($error);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }
}
