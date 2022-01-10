<?php

namespace App\Scoring;

use App\Entity\Ascent;
use App\Entity\Boulder;

class DefaultScoring implements ScoringInterface
{
    public function calculateScore(Boulder $boulder): void
    {
        $ascentCount = $boulder->getAscents()->count();
        $points = $boulder->getPoints();

        /**
         * @var Ascent $ascent
         */
        foreach ($boulder->getAscents() as $ascent) {
            $validAscentsCount = $boulder->getAscents()->filter(function ($ascent) {
                /**
                 * @var Ascent $ascent
                 */
                return in_array($ascent->getType(), $this->getScoredAscentTypes()) && $ascent->getUser()->isVisible();
            })->count();

            if ($ascent->getType() === Ascent::ASCENT_FLASH) {
                $ascent->setScore(round(($points / $ascentCount) * 1.1));

            } else if ($ascent->getType() === Ascent::ASCENT_TOP) {
                $ascent->setScore(round($points / $ascentCount));
            } else {
                $ascent->setScore(0);
            }

            if ($validAscentsCount > 0) {
                $boulder->setCurrentPoints(round($boulder->getPoints() / ($validAscentsCount + 1)));
            }
        }
    }

    public function getScoredAscentTypes(): array
    {
        return [Ascent::ASCENT_TOP, Ascent::ASCENT_FLASH];
    }
}
