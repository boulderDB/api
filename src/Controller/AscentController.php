<?php

namespace App\Controller;

use App\Components\Controller\ApiControllerTrait;
use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Entity\User;
use App\Form\AscentType;
use App\Serializer\AscentSerializer;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("", methods={"POST"})
     */
    public function create(Request $request)
    {
        $ascent = new Ascent();

        /**
         * @var User $user
         */
        $user = $this->getUser();
        $ascent->setUser($user);

        $form = $this->createForm(AscentType::class, $ascent);
        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(AscentSerializer::serialize($ascent));
    }

    /**
     * @Route("", methods={"DELETE"})
     */
    public function delete()
    {

    }

    /**
     * @Route("/active-boulders")
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

            $scores[] = [
                // calculate +1 to return the score the user will get when checked
                'boulderId' => $boulderId,
                'points' => round($result['points'] / ($ascents + 1)),
                'ascents' => $ascents,
                'me' => self::filterUserAscent($result['ascents'], $userId)
            ];
        }

        return $this->json($scores);
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