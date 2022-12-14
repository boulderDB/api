<?php

namespace App\Ranking;

use App\Entity\Ascent;
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
}