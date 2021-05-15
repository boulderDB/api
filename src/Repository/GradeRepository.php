<?php

namespace App\Repository;

use App\Entity\Grade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Grade::class);
    }

    public static function getIndexStatement(int $locationId, ?string $filter, bool $isAdmin): array
    {
        if ($filter === "active" && $isAdmin) {
            return [
                "sql" => "SELECT id, name, active FROM grade WHERE tenant_id = :locationId AND active = :active",
                "parameters" => [
                    "locationId" => $locationId,
                    "active" => true
                ]
            ];
        }

        if ($filter === "active") {
            return [
                "sql" => "SELECT id, name, active FROM grade WHERE tenant_id = :locationId AND active = :active AND public = :public",
                "parameters" => [
                    "locationId" => $locationId,
                    "active" => true,
                    "public" => true
                ]
            ];
        }

        if ($isAdmin) {
            return [
                "sql" => "SELECT id, name, active FROM grade WHERE tenant_id = :locationId",
                "parameters" => [
                    "locationId" => $locationId
                ]
            ];
        }

        return [
            "sql" => "SELECT id, name, active color FROM grade WHERE tenant_id = :locationId AND public = :public",
            "parameters" => [
                "locationId" => $locationId,
                "public" => true
            ]
        ];
    }
}