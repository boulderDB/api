<?php

namespace App\Service;

use App\Entity\Ascent;
use App\Entity\Event;
use App\Ranking\AscentRanking;
use App\Ranking\BBDFRanking;
use App\Ranking\DefaultPointsRanking;
use App\Ranking\RankingInterface;

class RankingService
{
    private const RANKINGS = [
        DefaultPointsRanking::IDENTIFIER => DefaultPointsRanking::class,
        AscentRanking::IDENTIFIER => AscentRanking::class,
        BBDFRanking::IDENTIFIER => BBDFRanking::class
    ];

    public function calculateRanking(RankingInterface $ranking, array $boulders, Event $event = null): array
    {
        $data = [];

        /**
         * @var \App\Entity\Boulder $boulder
         */
        foreach ($boulders as $boulder) {
            $ranking->getScoring()->calculateScore($boulder);
        }

        foreach ($ranking->getAscents($boulders) as $ascent) {
            if (!in_array($ascent->getType(), $ranking->getScoring()->getScoredAscentTypes())) {
                continue;
            }

            if ($event && $ascent->getCreatedAt() > $event->getEndDate() && $ascent->getSource() !== Ascent::SOURCE_ADMIN) {
                continue;
            }

            if ($event && !$event->isParticipant($ascent->getUser())) {
                continue;
            }

            $userId = $ascent->getUser()->getId();

            if (!array_key_exists($userId, $data)) {
                $data[$userId] = [
                    "user" => $ascent->getUser(),
                    Ascent::ASCENT_TOP => [
                        "count" => 0,
                        "rate" => 0
                    ],
                    Ascent::ASCENT_FLASH => [
                        "count" => 0,
                        "rate" => 0
                    ],
                    "total" => [
                        "count" => 0,
                        "rate" => 0
                    ],
                    "points" => 0
                ];
            }

            $data[$userId][$ascent->getType()]["count"]++;
            $data[$userId]["total"]["count"]++;
            $data[$userId]["points"] += $ascent->getScore();
        }

        foreach ($data as &$rank) {
            $rank["total"]["count"] = $rank[Ascent::ASCENT_TOP]["count"] + $rank[Ascent::ASCENT_FLASH]["count"];
            $rank[Ascent::ASCENT_TOP]["rate"] = self::calculateRate(count($boulders), $rank[Ascent::ASCENT_TOP]["count"]);
            $rank[Ascent::ASCENT_FLASH]["rate"] = self::calculateRate(count($boulders), $rank[Ascent::ASCENT_FLASH]["count"]);
            $rank["total"]["rate"] = self::calculateRate(count($boulders), $rank["total"]["count"]);
        }

        usort($data, $ranking->getSorter());

        return array_values($data);
    }

    public static function calculateRate(int $total, int $partial): float|int
    {
        if (!$total || !$partial) {
            return 0;
        }

        return round(($partial / $total) * 100);
    }

    public function getRankings(): array
    {
        return [
            new DefaultPointsRanking(),
            new AscentRanking(),
            new BBDFRanking()
        ];
    }

    public function createRanking(string $identifier = DefaultPointsRanking::IDENTIFIER): RankingInterface
    {
        return new (self::RANKINGS[$identifier]);
    }
}