<?php

namespace App\Ranking;

use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Scoring\BBDFScoring;
use App\Scoring\ScoringInterface;

class BBDFRanking implements RankingInterface
{
    public const IDENTIFIER = "bbdf";

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public function getSorter(): \Closure
    {
        return function ($a, $b) {
            if ($a["total"] === $b["total"]) {
                return $a[Ascent::ASCENT_FLASH]["count"] > $b[Ascent::ASCENT_FLASH]["count"] ? -1 : 1;
            }

            return $a["total"] > $b["total"] ? -1 : 1;
        };
    }

    public function getScoring(): ScoringInterface
    {
        return new BBDFScoring();
    }

    public function getAscents(array $boulders): array
    {
        $userAscents = [];

        /**
         * @var Ascent[] $ascents
         */
        $ascents = [];

        /**
         * @var Boulder[] $boulders
         */

        foreach ($boulders as $boulder) {
            foreach ($boulder->getAscents() as $ascent) {
                $ascents[] = $ascent;
            }
        }

        usort($ascents, function ($a, $b) {
            /**
             * @var $a Ascent
             * @var $b Ascent
             */

            return $a->getScore() > $b->getScore() ? -1 : 1;
        });

        foreach ($ascents as $ascent) {
            $userId = $ascent->getUser()->getId();

            if (!array_key_exists($userId, $userAscents)) {
                $userAscents[$userId] = [];
            }

            if (count($userAscents[$userId]) < 5) {
                $userAscents[$userId][] = $ascent;
            }
        }

        return array_merge(...array_values($userAscents));
    }
}