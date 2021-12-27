<?php

namespace App\Repository;

use App\Entity\ReadableIdentifier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReadableIdentifierRepository extends ServiceEntityRepository
{
    use DeactivatableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReadableIdentifier::class);
    }

    public function getUnassigned(int $locationId)
    {
        $connection = $this->getEntityManager()->getConnection();
        $statement = "SELECT readable_identifier.id, readable_identifier.value from readable_identifier LEFT JOIN boulder ON readable_identifier.id = boulder.readable_identifier_id WHERE boulder.readable_identifier_id IS NULL AND readable_identifier.tenant_id = :locationId;";

        $query = $connection
            ->prepare($statement)
            ->executeQuery([
                "locationId" => $locationId
            ]);

        return $query->fetchAllAssociative();
    }
}