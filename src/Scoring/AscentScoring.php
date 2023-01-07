<?php

namespace App\Scoring;

use App\Entity\Ascent;
use App\Entity\Boulder;

class AscentScoring implements ScoringInterface
{
    public function calculateScore(Boulder $boulder): void
    {
        /**
         * @var Ascent $ascent
         */
        foreach ($boulder->getAscents() as $ascent) {
            if ($ascent->getType() === Ascent::ASCENT_FLASH || $ascent->getType() === Ascent::ASCENT_TOP) {
                $ascent->setScore(1);
            } else {
                $ascent->setScore(0);
            }
        }
    }

    public function getScoredAscentTypes(): array
    {
        return [Ascent::ASCENT_TOP, Ascent::ASCENT_FLASH];
    }
}