<?php

namespace App\Form;

use App\Entity\Boulder;
use App\Entity\HoldType;
use App\Entity\Setter;
use App\Entity\BoulderTag;
use App\Entity\Grade;
use App\Entity\Wall;
use App\Service\ContextService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class BoulderType extends AbstractType implements SchemaTypeInterface
{
    private ?ContextService $contextService;

    public function __construct(ContextService $contextService = null)
    {
        $this->contextService = $contextService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->getSchema() as $field) {
            $builder->add($field["name"], $field["type"], $field["options"]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "csrf_protection" => false,
            "data_class" => Boulder::class,
        ]);
    }

    public function getSchema(): array
    {
        $locationId = $this->contextService?->getLocation()?->getId();

        $setterQuery = function (EntityRepository $entityRepository) use ($locationId) {
            return $entityRepository->createQueryBuilder("setter")
                ->innerJoin("setter.locations", "location")
                ->where("location.id = :locationId")
                ->setParameter("locationId", $locationId)
                ->orderBy("lower(setter.username)", "ASC");
        };

        $locationQuery = function (EntityRepository $entityRepository) use ($locationId) {
            return $entityRepository->createQueryBuilder("locationResource")
                ->where("locationResource.location = :location")
                ->setParameter("location", $locationId);
        };

        return [
            [
                "name" => "name",
                "type" => TextType::class,
                "constraints" => [new NotBlank()],
                "options" => []
            ],
            [
                "name" => "holdType",
                "type" => EntityType::class,
                "options" => [
                    "class" => HoldType::class,
                    "constraints" => [new NotBlank()],
                    "query_builder" => $locationQuery
                ],
                "schema" => [
                    "resource" => "/holdtypes",
                    "labelProperty" => "name"
                ]
            ],
            [
                "name" => "grade",
                "type" => EntityType::class,
                "options" => [
                    "class" => Grade::class,
                    "constraints" => [new NotBlank()],
                    "query_builder" => $locationQuery
                ],
                "schema" => [
                    "resource" => "/grades",
                    "labelProperty" => "name"
                ]
            ],
            [
                "name" => "internalGrade",
                "type" => EntityType::class,
                "options" => [
                    "class" => Grade::class,
                    "constraints" => [],
                    "query_builder" => $locationQuery
                ],
                "schema" => [
                    "resource" => "/grades",
                    "labelProperty" => "name"
                ]
            ],
            [
                "name" => "startWall",
                "type" => EntityType::class,
                "options" => [
                    "class" => Wall::class,
                    "constraints" => [new NotBlank()],
                    "query_builder" => $locationQuery
                ],
                "schema" => [
                    "resource" => "/walls",
                    "labelProperty" => "name"
                ]
            ],
            [
                "name" => "endWall",
                "type" => EntityType::class,
                "options" => [
                    "class" => Wall::class,
                    "constraints" => [],
                    "query_builder" => $locationQuery
                ],
                "schema" => [
                    "resource" => "/walls",
                    "labelProperty" => "name"
                ]
            ],
            [
                "name" => "setters",
                "type" => EntityType::class,
                "options" => [
                    "class" => Setter::class,
                    "multiple" => true,
                    "constraints" => [new NotNull()],
                    "query_builder" => $setterQuery
                ],
                "schema" => [
                    "resource" => "/setters",
                    "labelProperty" => "username"
                ]
            ],
            [
                "name" => "tags",
                "type" => EntityType::class,
                "options" => [
                    "class" => BoulderTag::class,
                    "multiple" => true,
                    "constraints" => [new NotNull()],
                    "query_builder" => $locationQuery
                ],
                "schema" => [
                    "resource" => "/boulder-tags",
                    "labelProperty" => "name"
                ]
            ],
            [
                "name" => "points",
                "type" => NumberType::class,
                "options" => [
                    "constraints" => [new NotBlank()]
                ],
                "schema" => [
                    "default" => Boulder::DEFAULT_SCORE
                ]
            ],
            [
                "name" => "status",
                "type" => ChoiceType::class,
                "options" => [
                    "constraints" => [new NotBlank()],
                    "choices" => [
                        Boulder::STATUS_ACTIVE => Boulder::STATUS_ACTIVE,
                        Boulder::STATUS_INACTIVE => Boulder::STATUS_INACTIVE
                    ],
                ],
                "schema" => [
                    "default" => Boulder::STATUS_ACTIVE
                ]
            ],
        ];
    }
}
