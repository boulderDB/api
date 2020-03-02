<?php

namespace App\Controller;

use App\Entity\Boulder;
use App\Service\ContextService;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/ranking")
 */
class RankingController extends AbstractController
{
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
     * @Route("/current")
     */
    public function current()
    {
        $boulders = $this->entityManager->createQueryBuilder()
            ->select('
                partial boulder.{id, points},
                partial ascent.{id, type},
                partial user.{id, username, gender, lastActivity, media}
            ')
            ->from(Boulder::class, 'boulder')
            ->innerJoin('boulder.ascents', 'ascent')
            ->innerJoin('ascent.user', 'user')
            ->where('boulder.tenant = :tenant')
            ->andWhere('boulder.status = :status')
            ->setParameter('tenant', $this->contextService->getLocation()->getId())
            ->setParameter('status', 'active')
            ->getQuery()
            ->getArrayResult();

        foreach ($boulders as &$boulder) {
            $ascents = count($boulder['ascents']) ? count($boulder['ascents']) : 0;
            $points = $boulder['points'];

            foreach ($boulder['ascents'] as &$ascent) {

                if ($ascent['type'] === 'flash') {
                    $score = ($points / $ascents) * 1.1;
                } else {
                    $score = $points / $ascents;
                }

                $ascent['score'] = $score;
            }
        }

        $ranking = [];

        foreach ($boulders as $boulder) {
            foreach ($boulder['ascents'] as $ascent) {
                $userId = $ascent['user']['id'];

                $ascent['user']['lastActivity'] = $ascent['user']['lastActivity']->format('c');

                if (!array_key_exists($userId, $ranking)) {
                    $ranking[$userId] = [
                        'score' => 0,
                        'boulders' => 0,
                        'tops' => 0,
                        'flashes' => 0
                    ];
                } else {
                    $ranking[$userId]['score'] += $ascent['score'];
                }

                $ranking[$userId]['user'] = $ascent['user'];
                $ranking[$userId]['boulders']++;

                if ($ascent['type'] === 'top') {
                    $ranking[$userId]['tops']++;
                }

                if ($ascent['type'] === 'flash') {
                    $ranking[$userId]['flashes']++;
                }

                $ranking[$userId]['score'] = round($ranking[$userId]['score']);
            }
        }

        $ranking = array_values($ranking);
        $ranking = array_filter($ranking, function ($rank) {
            return $rank['score'] > 0;
        });

        usort($ranking, function ($a, $b) {
            return $a['score'] < $b['score'];
        });

        return $this->json($ranking);
    }
}