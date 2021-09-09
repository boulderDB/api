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
            if ($ascent->getType() === Ascent::ASCENT_FLASH) {
                $ascent->setScore(($points / $ascentCount) * 1.1);
            } else if ($ascent->getType() === Ascent::ASCENT_TOP) {
                $ascent->setScore($points / $ascentCount);
            } else {
                $ascent->setScore(0);
            }
        }
    }

    /**
     * @param Boulder[] $boulders
     * @return array
     */
    public function calculate(array $boulders): array
    {
        // calculate each ascent score first
        foreach ($boulders as $boulder) {
            $this->calculateScore($boulder);
        }

        $ranking = [];

        foreach ($boulders as $boulder) {
            foreach ($boulder->getAscents() as $ascent) {

                /**
                 * @var Ascent $ascent
                 */
                $userId = $ascent->getOwner()->getId();

                if (!array_key_exists($userId, $ranking)) {
                    $ranking[$userId] = [
                        "score" => 0,
                        "boulders" => 0,
                        "tops" => 0,
                        "flashes" => 0,
                        "advance" => 0,
                        "user" => [
                            "id" => $ascent->getOwner()->getId(),
                            "gender" => $ascent->getOwner()->getGender(),
                            "image" => $ascent->getOwner()->getImage(),
                            "lastActivity" => $ascent->getOwner()->getLastActivity()->format("c"),
                            "username" => $ascent->getOwner()->getUsername()
                        ]
                    ];
                } else {
                    $ranking[$userId]["score"] += $ascent->getScore();
                }

                $ranking[$userId]["boulders"]++;

                if ($ascent->isType(Ascent::ASCENT_TOP)) {
                    $ranking[$userId]["tops"]++;
                }

                if ($ascent->isType(Ascent::ASCENT_FLASH)) {
                    $ranking[$userId]["flashes"]++;
                }

                $ranking[$userId]["score"] = round($ranking[$userId]["score"]);
            }
        }

        $ranking = array_values($ranking);

        $ranking = array_filter($ranking, function ($rank) {
            return $rank["score"] > 0;
        });

        usort($ranking, function ($a, $b) {
            return $a["score"] < $b["score"];
        });

        foreach ($ranking as $index => &$rank) {
            if ($index < count($ranking)) {
                $rank["advance"] = $rank["score"] - $ranking[$index + 1]["score"];
            }

            if ($index === count($ranking)) {
                $rank["advance"] = 0;
            }
        }

        foreach ($ranking as $key => &$rank) {
            $rank["rank"] = $key + 1;
        }

        return $ranking;
    }

    public function getIdentifier(): string
    {
        return "default";
    }
}
