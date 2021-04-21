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

    public function exists(string $property, string $value): bool
    {
        $connection = $this->getEntityManager()->getConnection();
        $statement = "select id from setter where lower({$property}) = lower(:property)";
        $query = $connection->prepare($statement);

        $query->execute([
            "property" => strtolower($value)
        ]);

        $result = $query->fetchOne();

        return $result ? true : false;
    }

    public static function getIndexStatement(string $locationId)
    {
        return "SELECT setter.id, setter.username FROM boulder 
                INNER JOIN boulder_setters_v2 ON boulder_setters_v2.boulder_id = boulder.id 
                INNER JOIN setter ON setter.id = boulder_setters_v2.setter_id 
                WHERE boulder.tenant_id = {$locationId} 
                GROUP BY setter.id 
                ORDER BY lower(setter.username) ASC";
    }

    public static function getCurrentStatement(string $locationId)
    {
        return "SELECT setter.id, setter.username, user.id as user_id FROM boulder 
                INNER JOIN boulder_setters_v2 ON boulder_setters_v2.boulder_id = boulder.id 
                INNER JOIN setter ON setter.id = boulder_setters_v2.setter_id 
                LEFT JOIN user ON setter.user_id = user.id
                WHERE boulder.status = 'active' AND boulder.tenant_id = {$locationId} 
                AND setter.active = true
                GROUP BY setter.id 
                ORDER BY lower(setter.username) ASC";
    }
}
