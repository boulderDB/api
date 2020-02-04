<?php

namespace App\Components\Scoring;

interface ScoringInterface
{
    public function calculateAscentScore(array $ascent): int;

    public function calculateRanking(array $boulders): array;

    public function sortRanking(array $ranking): void;
}