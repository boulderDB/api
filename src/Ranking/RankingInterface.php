<?php

namespace App\Ranking;

use App\Scoring\ScoringInterface;

interface RankingInterface
{
    public function getIdentifier(): string;

    public function getSorter(): \Closure;

    public function getScoring(): ScoringInterface;
}