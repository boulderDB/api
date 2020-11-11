<?php

namespace App\Scoring;

use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Struct\AscentStruct;
use App\Struct\BoulderStruct;

class DefaultScoring implements ScoringInterface
{
    public function calculateScore(Boulder $boulder): void
    {
        $ascentCount = $boulder->getAscents()->count();
        $points = $boulder->getPoints();

        /**
         * @var AscentStruct $ascent
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

    /**
     * @param BoulderStruct[] $boulders
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
                $userId = $ascent->getUser()->getId();

                if (!array_key_exists($userId, $ranking)) {
                    $ranking[$userId] = [
                        "score" => 0,
                        "boulders" => 0,
                        "tops" => 0,
                        "flashes" => 0,
                        "advance" => 0,
                        "user" => [
                            "id" => $ascent->getUser()->getId(),
                            "gender" => $ascent->getUser()->getGender(),
                            "image" => $ascent->getUser()->getImage(),
                            "lastActivity" => $ascent->getUser()->getLastActivity()->format("c"),
                            "username" => $ascent->getUser()->getUsername()
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
