<?php

namespace App\Service;

use App\Entity\Ascent;
use App\Scoring\DefaultScoring;
use App\Scoring\ScoringInterface;

class RankingService
{
    public function calculateRanking(ScoringInterface $scoring, array $boulders): array
    {
        $data = [];

        /**
         * @var \App\Entity\Boulder $boulder
         */
        foreach ($boulders as $boulder) {
            $scoring->calculateScore($boulder);

            /**
             * @var \App\Entity\Ascent $ascent
             */
            foreach ($boulder->getAscents() as $ascent) {
                if (!in_array($ascent->getType(), $scoring->getScoredAscentTypes())) {
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
        }

        foreach ($data as &$rank) {
            $rank["total"]["count"] = $rank[Ascent::ASCENT_TOP]["count"] + $rank[Ascent::ASCENT_FLASH]["count"];
            $rank[Ascent::ASCENT_TOP]["rate"] = DefaultScoring::calculateRate(count($boulders), $rank[Ascent::ASCENT_TOP]["count"]);
            $rank[Ascent::ASCENT_FLASH]["rate"] = DefaultScoring::calculateRate(count($boulders), $rank[Ascent::ASCENT_FLASH]["count"]);
            $rank["total"]["rate"] = DefaultScoring::calculateRate(count($boulders), $rank["total"]["count"]);
        }

        usort($data, function ($a, $b) {
            return $a["points"] > $b["points"] ? -1 : 1;
        });

        return array_values($data);
    }

}