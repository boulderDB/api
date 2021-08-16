<?php


namespace App\Repository;

use App\Entity\Setter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SetterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setter::class);
    }

    public function exists(string $property, string $value, int $locationId): bool
    {
        $connection = $this->getEntityManager()->getConnection();
        $statement = "
            SELECT id FROM setter 
            INNER JOIN setter_locations on setter.id = setter_locations.setter_id 
            WHERE lower({$property}) = lower(:property) 
            AND setter_locations.location_id = :locationId
        ";

        $query = $connection->prepare($statement);

        $query->execute([
            "property" => strtolower($value),
            "locationId" => $locationId
        ]);

        $result = $query->fetchOne();

        return $result ? true : false;
    }

    public static function getIndexStatement(string $locationId, string $filter = null)
    {
        $filters = ["active", "current"];

        if ($filter && !in_array($filter, $filters)) {
            throw new \InvalidArgumentException("Unsupported filter: '$filter'");
        }

        if ($filter === "active") {
            return "SELECT * FROM setter
                INNER JOIN setter_locations ON setter.id = setter_locations.setter_id
                WHERE setter_locations.location_id = {$locationId}
                AND setter.active = true
                ORDER BY lower(setter.username) ASC";
        }

        if ($filter === "current") {
            return "SELECT setter.id, setter.username, setter.active, users.id as user_id FROM boulder 
                INNER JOIN boulder_setters_v2 ON boulder_setters_v2.boulder_id = boulder.id 
                INNER JOIN setter ON setter.id = boulder_setters_v2.setter_id 
                LEFT JOIN users ON setter.user_id = users.id
                WHERE boulder.status = 'active' AND boulder.tenant_id = {$locationId} 
                GROUP BY setter.id, users.id 
                ORDER BY lower(setter.username) ASC";
        }

        return "SELECT * FROM setter
                INNER JOIN setter_locations ON setter.id = setter_locations.setter_id
                WHERE setter_locations.location_id = {$locationId}
                ORDER BY lower(setter.username) ASC";
    }
}
