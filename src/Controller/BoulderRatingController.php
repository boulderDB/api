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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rating")
 */
class BoulderRatingController extends AbstractController
{
    use ContextualizedControllerTrait;
    use FormErrorTrait;
    use RequestTrait;
    use ResponseTrait;

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
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $rating = new BoulderRating();
        $rating->setAuthor($this->getUser());

        $form = $this->createForm(BoulderRatingType::class, $rating);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form));
        }

        try {
            $this->entityManager->persist($rating);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            return $this->conflictResponse("You already left a rating for this boulder.");
        }

        return $this->createdResponse($rating);
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function delete(int $id)
    {
        /**
         * @var \App\Entity\BoulderRating $rating
         */
        $rating = $this->boulderRatingRepository->find($id);

        if (!$rating) {
            return $this->resourceNotFoundResponse("BoulderRating", $id);
        }

        if (!$rating->getAuthor() === $this->getUser()) {
            return $this->unauthorizedResponse();
        }

        $this->entityManager->remove($rating);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
