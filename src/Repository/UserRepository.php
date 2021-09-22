<?php

namespace App\Repository;

use App\Entity\User;
use App\Service\ContextService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getByRole(string $role): ?array
    {
        return $this->createQueryBuilder("user")
            ->where("user.roles LIKE :roles")
            ->setParameter('roles', '%"' . $role . '"%')
            ->getQuery()
            ->getResult();
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
        $statement = "select id from users where lower({$property}) = lower(:property)";
        $query = $connection->prepare($statement);

        $query->execute([
            "property" => strtolower($value)
        ]);

        $result = $query->fetch();

        return $result ? true : false;
    }

    public function loadUserByUsername(string $username)
    {
        return $this->createQueryBuilder("user")
            ->where("lower(user.username) = lower(:username)")
            ->orWhere("lower(user.email) = lower(:username)")
            ->setParameter("username", $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function searchByUsername(string $username)
    {
        $builder = $this->createQueryBuilder("user");

        return $builder
            ->distinct()
            ->select("user.id, user.username")
            ->where("user.visible = true")
            ->andWhere($builder->expr()->like("lower(user.username)", ":term"))
            ->setParameter("term", "%" . addcslashes(strtolower($username), "%") . "%")
            ->orderBy("user.username")
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function loadUserByIdentifier(string $identifier)
    {
       return $this->loadUserByUsername($identifier);
    }
}
