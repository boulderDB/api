<?php

namespace App\Scoring;

use App\Entity\Ascent;
use App\Entity\Boulder;

interface ScoringInterface
{
    public const ASCENT_TYPES = [
        Ascent::ASCENT_FLASH,
        Ascent::ASCENT_TOP,
        Ascent::ASCENT_RESIGNED
    ];

    public function calculateScore(Boulder $boulder): void;

    public function getIdentifier(): string;

    public function getScoredAscentTypes(): array;
}
