<?php

namespace App\Scoring;

use App\Components\Scoring\ScoringInterface;
use App\Entity\Ascent;
use App\Struct\BoulderStruct;

class DefaultScoring implements ScoringInterface
{
    /**
     * @param BoulderStruct[] $boulders
     * @return array
     */
    public function calculate(array $boulders): array
    {
        // calculate each ascent score first
        foreach ($boulders as $boulder) {
            $ascentCount = $boulder->getAscents()->count();
            $points = $boulder->getPoints();

            /**
             * @var Ascent $ascent
             */
            foreach ($boulder->getAscents() as $ascent) {

                if ($ascent->getType() === Ascent::ASCENT_FLASH) {
                    $score = ($points / $ascentCount) * 1.1;
                } else {
                    $score = $points / $ascentCount;
                }

                $ascent->setScore($score);
            }
        }

        $ranking = [];

        foreach ($boulders as $boulder) {
            foreach ($boulder->getAscents() as $ascent) {
                $userId = $ascent->getUser()->getId();

                if (!array_key_exists($userId, $ranking)) {
                    $ranking[$userId] = [
                        'score' => 0,
                        'boulders' => 0,
                        'tops' => 0,
                        'flashes' => 0,
                        'user' => $ascent->getUser()->getId()
                    ];
                } else {
                    $ranking[$userId]['score'] += $ascent->getScore();
                }

                $ranking[$userId]['boulders']++;

                if ($ascent->isType(Ascent::ASCENT_TOP)) {
                    $ranking[$userId]['tops']++;
                }

                if ($ascent->isType(Ascent::ASCENT_FLASH)) {
                    $ranking[$userId]['flashes']++;
                }

                $ranking[$userId]['score'] = round($ranking[$userId]['score']);
            }
        }

        $ranking = array_values($ranking);
        $ranking = array_filter($ranking, function ($rank) {
            return $rank['score'] > 0;
        });

        foreach ($ranking as $key => $rank) {
            if ($key === count($ranking) - 1) {
                $rank['advance'] = 0;
            } else {
                $rank['advance'] = $rank['score'] - $ranking[$key + 1]['score'];
                $rank['advance'] = round($rank['advance']);
            }
        }

        usort($ranking, function ($a, $b) {
            return $a['score'] < $b['score'];
        });

        return $ranking;
    }

    public function getIdentifier(): string
    {
      return 'default';
    }
}