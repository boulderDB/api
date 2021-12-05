<?php

namespace App\Repository;

/**
 * @method createQueryBuilder(string $string)
 */
trait DeactivatableRepositoryTrait
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

    public function getAll(int $locationId)
    {
        return $this->createQueryBuilder("object")
            ->where("object.location = :locationId")
            ->setParameter("locationId", $locationId)
            ->getQuery()
            ->getResult();
    }
}