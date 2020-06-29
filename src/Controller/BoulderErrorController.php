<?php

namespace App\Controller;

use App\Components\Controller\ApiControllerTrait;
use App\Components\Controller\ContextualizedControllerTrait;
use App\Entity\BoulderError;
use App\Form\BoulderErrorType;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/error")
 */
class BoulderErrorController extends AbstractController
{
    use ApiControllerTrait;
    use ContextualizedControllerTrait;

    private $entityManager;
    private $contextService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index()
    {
        $this->denyUnlessLocationAdmin();

        $errors = $this->entityManager->createQueryBuilder()
            ->select('
                partial boulderError.{id, description, createdAt, location}, 
                partial author.{id, username}, 
                partial boulder.{id, name, startWall},
                partial startWall.{id, name}
            ')
            ->from(BoulderError::class, 'boulderError')
            ->leftJoin('boulderError.author', 'author')
            ->leftJoin('boulderError.boulder', 'boulder')
            ->leftJoin('boulder.startWall', 'startWall')
            ->where('boulderError.location = :location')
            ->andWhere('boulderError.status = :status')
            ->setParameter('location', $this->contextService->getLocation()->getId())
            ->setParameter('status', BoulderError::STATUS_UNRESOLVED)
            ->getQuery()
            ->getArrayResult();

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
        $data = json_decode($request->getContent());

        $form->submit($data, true);

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form));
        }

        $this->entityManager->persist($boulderError);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_CREATED);
    }

    /**
     * @Route("/count", methods={"GET"})
     */
    public function count()
    {
        $this->denyUnlessLocationAdmin();

        $connection = $this->entityManager->getConnection();
        $statement = 'select count(id) from boulder_error where tenant_id = :locationId and status = :status';
        $query = $connection->prepare($statement);

        $query->execute([
            'locationId' => $this->contextService->getLocation()->getId(),
            'status' => BoulderError::STATUS_UNRESOLVED
        ]);

        $results = $query->fetch();

        return $this->json($results);
    }

    /**
     * @Route("/{id}/resolve", methods={"PUT"})
     */
    public function resolve(string $id)
    {

    }
}