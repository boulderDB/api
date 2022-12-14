<?php

namespace App\Ranking;

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
            return $a["points"] > $b["points"] ? -1 : 1;
        };
    }

    public function getScoring(): ScoringInterface
    {
       return new AscentScoring();
    }
}