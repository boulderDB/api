<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

trait FilterTrait
{
    /* https://symfony.com/doc/current/reference/constraints/Type.html#reference-constraint-type-type */
    public function addFilters(QueryBuilder $builder, string $alias, array $config, array $filters): void
    {
        if (!$filters) {
            return;
        }

        $validator = Validation::createValidator();

        $converters = [
            "bool" => fn($value) => $value === "true",
            "integer" => fn($value) => (int)$value,
        ];

        $supportedFilters = array_keys($config);

        foreach (array_diff($supportedFilters, $filters) as $property) {
            $value = $filters[$property];
            $variable = $property . "Value";
            $type = $config[$property];
            $converter = $converters[$type];

            $violations = $validator->validate($converter($value), [
                new Type($type)
            ]);

            if (count($violations)) {
                continue;
            }

            $builder
                ->andWhere("$alias.$property = :$variable")
                ->setParameter($variable, $converter($value));
        }
    }

    public function queryWhere(int $locationId, array $config, array $filters)
    {
        $builder = $this->createQueryBuilder("object")
            ->where("object.location = :locationId")
            ->setParameter("locationId", $locationId);

        $this->addFilters($builder, "object", $config, $filters);

        return $builder
            ->getQuery()
            ->getResult();

    }

}