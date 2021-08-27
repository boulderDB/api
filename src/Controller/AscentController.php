<?php

namespace App\Controller;

use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Form\AscentType;
use App\Scoring\DefaultScoring;
use App\Scoring\ScoringInterface;
use App\Serializer\AscentSerializer;
use App\Service\ContextService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ascent")
 */
class AscentController extends AbstractController
{
    use ResponseTrait;
    use RequestTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
    }

    /**
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $ascent = new Ascent();

        $ascent->setUser($this->getUser());
        $ascent->setLocation($this->contextService->getLocation());

        $form = $this->createForm(AscentType::class, $ascent);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        if (!$ascent->getBoulder()->isActive()) {
            return $this->json([
                "code" => Response::HTTP_GONE,
                "message" => "Boulder {$ascent->getBoulder()->getId()} is deactivated"
            ]);
        }

        try {
            $this->entityManager->persist($ascent);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            return $this->conflictResponse("You already checked this boulder.");
        }

        $defaultScoring = new DefaultScoring();
        $defaultScoring->calculateScore($ascent->getBoulder());

        return $this->json(AscentSerializer::serialize($ascent));
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function delete(string $id)
    {
        /**
         * @var Ascent $ascent
         */
        $ascent = $this->entityManager->getRepository(Ascent::class)->find($id);

        if (!$ascent) {
            return $this->resourceNotFoundResponse("Ascent", $id);
        }

        if (!$ascent->getUser() === $this->getUser()) {
            return $this->unauthorizedResponse();
        }

        $this->entityManager->remove($ascent);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index()
    {
        $results = $this->entityManager->createQueryBuilder()
            ->select("
                partial boulder.{id, points},
                partial ascent.{id, userId, type},
                partial user.{id, visible}
            ")
            ->from(Boulder::class, "boulder")
            ->leftJoin("boulder.ascents", "ascent")
            ->leftJoin("ascent.user", "user", "WITH")
            ->where("boulder.status = :status")
            ->andWhere("boulder.location = :location")
            ->setParameter("location", $this->contextService->getLocation()->getId())
            ->setParameter("status", "active")
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, 1)
            ->getArrayResult();

        $scores = [];

        foreach ($results as $result) {
            $boulderId = $result["id"];
            $userId = $this->getUser()->getId();

            $ascents = array_filter($result["ascents"], function ($ascent) use ($boulderId) {
                if (in_array($ascent["type"], ScoringInterface::SCORED_ASCENT_TYPES) && $ascent["user"]["visible"] === true) {
                    return true;
                }

                return false;
            });

            $ascentCount = count($ascents) ? count($ascents) : 0;

            if ($ascentCount === 0) {
                $points = $result["points"];
            } else {
                $points = $result["points"] / ($ascentCount + 1);
            }

            $scores[] = [
                "boulderId" => $boulderId,
                "points" => round($points),
                "ascents" => $ascentCount,
                "me" => self::filterUserAscent($result["ascents"], $userId)
            ];
        }

        return $this->json($scores);
    }

    private static function filterUserAscent(array $ascents, int $userId): ?array
    {
        $userAscent = array_filter($ascents, function ($ascent) use ($userId) {
            return $ascent["userId"] === $userId;
        });

        $userAscent = array_values($userAscent);

        if (!array_values($userAscent)) {
            return null;
        }

        unset($userAscent["userId"]);

        return array_values($userAscent)[0];
    }
}
