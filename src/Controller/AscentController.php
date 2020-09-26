<?php

namespace App\Controller;

use App\Components\Controller\ApiControllerTrait;
use App\Components\Scoring\ScoringInterface;
use App\Entity\Ascent;
use App\Entity\AscentDoubt;
use App\Entity\Boulder;
use App\Factory\ResponseFactory;
use App\Form\AscentDoubtType;
use App\Form\AscentType;
use App\Serializer\AscentSerializer;
use BlocBeta\Service\ContextService;
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
    use ApiControllerTrait;

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
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $ascent = new Ascent();

        $ascent->setUser($this->getUser());
        $ascent->setLocation($this->contextService->getLocation());

        $form = $this->createForm(AscentType::class, $ascent);
        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            return $this->badRequest($this->getFormErrors($form));
        }

        if (!$ascent->getBoulder()->isActive()) {
            return $this->json([
                'code' => Response::HTTP_GONE,
                'message' => "Boulder {$ascent->getBoulder()->getId()} is deactivated"
            ]);
        }

        $this->entityManager->persist($ascent);
        $this->entityManager->flush();

        return $this->json(AscentSerializer::serialize($ascent));
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function delete(string $id)
    {
        $ascent = $this->entityManager->getRepository(Ascent::class)->find($id);

        if (!$ascent) {
            return $this->notFound("Ascent", $id);
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
            ->select('
                partial boulder.{id, points},
                partial ascent.{id, userId, type},
                partial user.{id, visible}
            ')
            ->from(Boulder::class, 'boulder')
            ->leftJoin('boulder.ascents', 'ascent')
            ->leftJoin('ascent.user', 'user', 'WITH')
            ->where('boulder.status = :status')
            ->andWhere('boulder.location = :location')
            ->setParameter('location', $this->contextService->getLocation()->getId())
            ->setParameter('status', 'active')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, 1)
            ->getArrayResult();

        $scores = [];

        foreach ($results as $result) {
            $boulderId = $result['id'];
            $userId = $this->getUser()->getId();

            $ascents = array_filter($result['ascents'], function ($ascent) use ($boulderId) {
                if (in_array($ascent['type'], ScoringInterface::SCORED_ASCENT_TYPES) && $ascent['user']['visible'] === true) {
                    return true;
                }

                return false;
            });

            $ascentCount = count($ascents) ? count($ascents) : 0;

            if ($ascentCount === 0) {
                $points = $result['points'];
            } else {
                $points = $result['points'] / ($ascentCount + 1);
            }

            $scores[] = [
                'boulderId' => $boulderId,
                'points' => round($points),
                'ascents' => $ascentCount,
                'me' => self::filterUserAscent($result['ascents'], $userId)
            ];
        }

        return $this->json($scores);
    }

    /**
     * @Route("/{id}/doubt", methods={"POST"})
     */
    public function doubt(Request $request, string $id)
    {
        $ascentDoubt = new AscentDoubt();
        $ascentDoubt->setAuthor($this->getUser());

        /**
         * @var Ascent $ascent
         */
        $ascent = $this->entityManager->getRepository(Ascent::class)->find($id);

        if (!$ascent) {
            return $this->notFound("Ascent", $id);
        }

        $ascentDoubt->setBoulder($ascent->getBoulder());

        $form = $this->createForm(AscentDoubtType::class, $ascentDoubt);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form));
        }

        $ascent->setDoubted();

        $this->entityManager->persist($ascent);
        $this->entityManager->persist($ascentDoubt);

        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_CREATED);
    }

    private static function filterUserAscent(array $ascents, int $userId): ?array
    {
        $userAscent = array_filter($ascents, function ($ascent) use ($userId) {
            return $ascent['userId'] === $userId;
        });

        $userAscent = array_values($userAscent);

        if (!array_values($userAscent)) {
            return null;
        }

        unset($userAscent['userId']);

        return array_values($userAscent)[0];
    }
}