<?php

namespace App\Repository;

use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Scoring\DefaultScoring;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AscentRepository extends ServiceEntityRepository
{
    private BoulderRepository $boulderRepository;

    public function __construct(ManagerRegistry $registry, BoulderRepository $boulderRepository)
    {
        parent::__construct($registry, Ascent::class);
        $this->boulderRepository = $boulderRepository;
    }

    public function countByUser(int $locationId)
    {
        $scoring = new DefaultScoring();

        $boulderCount = $this->boulderRepository->countByLocation($locationId);

        $results = $this->createQueryBuilder("ascent")
            ->select("ascent.type, count(ascent.id) as count, user.username, user.id")
            ->innerJoin("ascent.user", "user")
            ->where("ascent.location = :locationId")
            ->groupBy("user.username, user.id, ascent.type")
            ->setParameter("locationId", $locationId)
            ->getQuery()
            ->getArrayResult();

        $users = [];

        foreach ($results as $result) {
            $users[$result["id"]][] = $result;
        }

        $ranking = [];

        foreach ($users as $user) {
            $rank = [
                "user" => [
                    "id" => $user[0]["id"],
                    "username" => $user[0]["username"]
                ],
                Ascent::ASCENT_TOP => [
                    "count" => 0,
                    "rate" => 0
                ],
                Ascent::ASCENT_FLASH => [
                    "count" => 0,
                    "rate" => 0
                ],
                "total" => [
                    "count" => 0,
                    "rate" => 0
                ]
            ];

            foreach ($user as $ascent) {
                if (!in_array($ascent["type"], $scoring->getScoredAscentTypes())) {
                    continue;
                }

                $rank[$ascent["type"]]["count"] = $ascent["count"];
            }

            $ranking[] = $rank;
        }

        foreach ($ranking as &$rank) {
            $rank["total"]["count"] = $rank[Ascent::ASCENT_TOP]["count"] + $rank[Ascent::ASCENT_FLASH]["count"];

            $rank[Ascent::ASCENT_TOP]["rate"] = DefaultScoring::calculateRate($boulderCount, $rank[Ascent::ASCENT_TOP]["count"]);
            $rank[Ascent::ASCENT_FLASH]["rate"] = DefaultScoring::calculateRate($boulderCount, $rank[Ascent::ASCENT_FLASH]["count"]);
            $rank["total"]["rate"] = DefaultScoring::calculateRate($boulderCount, $rank["total"]["count"]);
        }

        usort($ranking, function ($a, $b) {
            return $a["total"] > $b["total"] ? -1 : 1;
        });

        return $ranking;
    }

    /**
     * @return Ascent[]
     */
    public function getByUserAndLocation(int $userId, int $locationId)
    {
        return $this->createQueryBuilder("ascent")
            ->innerJoin("ascent.boulder", "boulder")
            ->where("boulder.location = :location")
            ->andWhere("boulder.status = :status")
            ->andWhere("ascent.user = :user")
            ->setParameter("location", $locationId)
            ->setParameter("status", Boulder::STATUS_ACTIVE)
            ->setParameter("user", $userId)
            ->getQuery()
            ->getResult();
    }
}