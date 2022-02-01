<?php

namespace App\Scoring;

use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Entity\Event;

interface ScoringInterface
{
    public const ASCENT_TYPES = [
        Ascent::ASCENT_FLASH,
        Ascent::ASCENT_TOP,
        Ascent::ASCENT_RESIGNED
    ];

    public function calculateScore(Boulder $boulder, Event $event = null): void;

    public function getScoredAscentTypes(): array;
}
