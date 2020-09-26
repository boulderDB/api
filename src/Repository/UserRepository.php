<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findUserByUsername(string $username): ?User
    {
        return $this->createQueryBuilder('user')
            ->where('lower(user.username) = lower(:username)')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function userExists(string $property, string $value): bool
    {
        $allowedProperties = [
            "username",
            "email"
        ];

        if (!in_array($property, $allowedProperties)) {
            return false;
        }

        $connection = $this->getEntityManager()->getConnection();
        $statement = "select id from users where {$property} = lower(:property)";
        $query = $connection->prepare($statement);

        $query->execute([
            'property' => strtolower($value)
        ]);

        $result = $query->fetch();

        return $result ? true : false;
    }
}