<?php

namespace App\Repository;

/**
 * @method createQueryBuilder(string $string)
 */
trait DeactivatableTrait
{
    public function getActive(int $locationId)
    {
        return $this->createQueryBuilder("object")
            ->where("object.location = :locationId")
            ->andWhere("object.active = true")
            ->setParameter("locationId", $locationId)
            ->getQuery()
            ->getResult();
    }
}