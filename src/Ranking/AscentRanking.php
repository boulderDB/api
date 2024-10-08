<?php

namespace App\Ranking;

use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Scoring\AscentScoring;
use App\Scoring\ScoringInterface;

class AscentRanking implements RankingInterface
{
    public const IDENTIFIER = "ascent";

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
        return new AscentScoring();
    }

    public function getAscents(array $boulders): array
    {
        $ascents = [];

        /**
         * @var Boulder[] $boulders
         */

        foreach ($boulders as $boulder) {
            foreach ($boulder->getAscents() as $ascent) {
                $ascents[] = $ascent;
            }
        }

        return $ascents;
    }
}