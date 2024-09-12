<?php

namespace App\Scoring;

use App\Entity\Ascent;
use App\Entity\Boulder;

class BBDFScoring implements ScoringInterface
{
    public function calculateScore(Boulder $boulder): void
    {
        $points = $boulder->getPoints();

        /**
         * @var Ascent $ascent
         */
        foreach ($boulder->getAscents() as $ascent) {
            if ($ascent->getType() === Ascent::ASCENT_FLASH) {
                $ascent->setScore(round(($points) * 1.1));

            } else if ($ascent->getType() === Ascent::ASCENT_TOP) {
                $ascent->setScore(round($points));
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