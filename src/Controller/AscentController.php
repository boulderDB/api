<?php

namespace App\Controller;

use App\Components\Controller\ApiControllerTrait;
use App\Entity\Ascent;
use App\Entity\AscentDoubt;
use App\Entity\Boulder;
use App\Form\AscentDoubtType;
use App\Form\AscentType;
use App\Serializer\AscentSerializer;
use App\Service\ContextService;
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
        $ascent->setTenant($this->contextService->getLocation());

        $form = $this->createForm(AscentType::class, $ascent);
        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form));
        }

        if (!$ascent->getBoulder()->isActive()) {
            return $this->json([
                'code' => Response::HTTP_NOT_ACCEPTABLE,
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
            return $this->json("Ascent {$id} not found", Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($ascent);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/filter/active")
     */
    public function active()
    {
        $results = $this->entityManager->createQueryBuilder()
            ->select('
                partial boulder.{id, points},
                partial ascent.{id, userId, type}
            ')
            ->from(Boulder::class, 'boulder')
            ->leftJoin('boulder.ascents', 'ascent')
            ->where('boulder.status = :status')
            ->andWhere('boulder.tenant = :tenant')
            ->setParameter('tenant', $this->contextService->getLocation()->getId())
            ->setParameter('status', 'active')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, 1)
            ->getArrayResult();

        $scores = [];

        foreach ($results as $result) {

            $boulderId = $result['id'];
            $ascents = count($result['ascents']) ? count($result['ascents']) : 0;
            $userId = $this->getUser()->getId();

            if ($ascents === 0) {
                $points = $result['points'];
            } else {
                $points = $result['points'] / ($ascents);
            }

            $scores[] = [
                'boulderId' => $boulderId,
                'points' => round($points),
                'ascents' => $ascents,
                'me' => self::filterUserAscent($result['ascents'], $userId)
            ];
        }

        return $this->json($scores);
    }

    /**
     * @Route("/doubt", methods={"POST"})
     */
    public function doubt(Request $request)
    {
        $ascentDoubt = new AscentDoubt();
        $ascentDoubt->setAuthor($this->getUser());

        $form = $this->createForm(AscentDoubtType::class, $ascentDoubt);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form));
        }

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