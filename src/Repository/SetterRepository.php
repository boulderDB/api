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
}
