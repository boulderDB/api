<?php

namespace App\Repository;

/**
 * @method createQueryBuilder(string $string)
 */
trait ReadableIdentifierTrait
{
    public function getByReadableInterface(int $locationId, string $identifier)
    {
        return $this->createQueryBuilder("object")
            ->innerJoin("object.readableIdentifier", "readableIdentifier")
            ->where("object.location = :locationId")
            ->andWhere("readableIdentifier.value = :identifier")
            ->setParameter("locationId", $locationId)
            ->setParameter("identifier", $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }
}