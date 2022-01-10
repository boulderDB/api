<?php

namespace App\Form;

use App\Entity\Boulder;
use App\Entity\Event;
use App\Service\ContextService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractSchemaType
{
    private ?ContextService $contextService;

    public function __construct(ContextService $contextService = null)
    {
        $this->contextService = $contextService;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "csrf_protection" => false,
            "data_class" => Event::class,
        ]);
    }

    public function getSchema(): array
    {
        $locationId = $this->contextService?->getLocation()?->getId();

        $boulderQuery = function (EntityRepository $entityRepository) use ($locationId) {
            return $entityRepository->createQueryBuilder("boulder")
                ->innerJoin("boulder.location", "location")
                ->where("location.id = :locationId")
                ->andWhere("boulder.status = :status")
                ->setParameter("locationId", $locationId)
                ->setParameter("status", Boulder::STATUS_ACTIVE);
        };

        return [
            [
                "name" => "name",
                "type" => TextType::class,
                "options" => [
                    "constraints" => [new NotBlank()]
                ],
            ],
            [
                "name" => "visible",
                "type" => CheckboxType::class,
                "options" => [
                    "constraints" => []
                ],
            ],
            [
                "name" => "public",
                "type" => CheckboxType::class,
                "options" => [
                    "constraints" => []
                ],
            ],
            [
                "name" => "boulders",
                "type" => EntityType::class,
                "options" => [
                    "class" => Boulder::class,
                    "constraints" => [new NotBlank()],
                    "query_builder" => $boulderQuery,
                    "multiple" => true,
                ],
                "schema" => [
                    "resource" => "/boulders",
                    "labelProperty" => "name"
                ]
            ],
            [
                "name" => "startDate",
                "type" => DateTimeType::class,
                "options" => [
                    "constraints" => [new NotBlank()],
                    "widget" => "single_text"
                ],
            ],
            [
                "name" => "endDate",
                "type" => DateTimeType::class,
                "options" => [
                    "constraints" => [new NotBlank()],
                    "widget" => "single_text"
                ],
            ]
        ];
    }
}