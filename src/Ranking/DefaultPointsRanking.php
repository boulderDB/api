<?php

namespace App\Ranking;

use App\Scoring\DefaultScoring;
use App\Scoring\ScoringInterface;

class DefaultPointsRanking implements RankingInterface
{
    public const IDENTIFIER = "defaultPoints";

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public function getSorter(): \Closure
    {
        return function ($a, $b) {
            return $a["points"] > $b["points"] ? -1 : 1;
        };
    }

    public function getScoring(): ScoringInterface
    {
        return new DefaultScoring();
    }
}