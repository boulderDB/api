<?php

namespace App\Controller;

use App\Entity\BoulderRating;
use App\Form\BoulderRatingType;
use App\Repository\BoulderRatingRepository;
use App\Service\ContextService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/boulder-ratings")
 */
class BoulderRatingController extends AbstractController
{
    use CrudTrait;
    use ContextualizedControllerTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private BoulderRatingRepository $boulderRatingRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        BoulderRatingRepository $boulderRatingRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->boulderRatingRepository = $boulderRatingRepository;
    }

    /**
     * @Route(methods={"POST"}, name="boulder_ratings_create")
     */
    public function create(Request $request)
    {
        $rating = new BoulderRating();
        $rating->setAuthor($this->getUser());

        try {
            return $this->createEntity($request, $rating, BoulderRatingType::class);
        } catch (UniqueConstraintViolationException $exception) {
            return $this->conflictResponse("You already left a rating for this boulder.");
        }
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="boulder_ratings_delete")
     */
    public function delete(int $id)
    {
        return $this->deleteEntity(BoulderRating::class, $id);
    }
}
