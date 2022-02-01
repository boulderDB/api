<?php

namespace App\Scoring;

use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Entity\Event;

class DefaultScoring implements ScoringInterface
{
    public function calculateScore(Boulder $boulder, Event $event = null): void
    {
        if ($boulder->isCalculated()) {
            return;
        }

        $points = $boulder->getPoints();

        $validAscentsCount = $boulder->getAscents()->filter(function ($ascent) {
            /**
             * @var Ascent $ascent
             */
            return in_array($ascent->getType(), $this->getScoredAscentTypes()) && $ascent->getUser()->isVisible();
        })->count();

        /**
         * @var Ascent $ascent
         */
        foreach ($boulder->getAscents() as $ascent) {
            if ($ascent->getType() === Ascent::ASCENT_FLASH) {
                $ascent->setScore(round(($points / $validAscentsCount) * 1.1));

            } else if ($ascent->getType() === Ascent::ASCENT_TOP) {
                $ascent->setScore(round($points / $validAscentsCount));
            } else {
                $ascent->setScore(0);
            }

            if ($validAscentsCount > 0) {
                $boulder->setCurrentPoints(round($boulder->getPoints() / ($validAscentsCount + 1)));
            }
        }

        $boulder->setCalculated(true);
    }

    public function getScoredAscentTypes(): array
    {
        return [Ascent::ASCENT_TOP, Ascent::ASCENT_FLASH];
    }
}
