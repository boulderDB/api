<?php

namespace App\Form;

use App\Entity\Boulder;
use App\Entity\Event;
use App\Service\ContextService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType implements SchemaTypeInterface
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
            "data_class" => Event::class,
        ]);
    }

    public function getSchema(): array
    {
        $locationId = $this->contextService?->getLocation()?->getId();

        $boulderQuery = function (EntityRepository $entityRepository) use ($locationId) {
            return $entityRepository->createQueryBuilder("boulder")
                ->innerJoin("boulder.locations", "location")
                ->where("location.id = :locationId")
                ->andWhere("boulder.status = :status")
                ->setParameter("locationId", $locationId)
                ->setParameter("status", Boulder::STATUS_ACTIVE)
                ->orderBy("lower(setter.username)", "ASC");
        };

        return [
            [
                "name" => "name",
                "type" => TextType::class,
                "constraints" => [new NotBlank()]
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
                    "resource" => "/boulders"
                ]
            ],
            [
                "nameDate" => "start",
                "type" => DateTimeType::class,
                "constraints" => [new NotBlank()]
            ],
            [
                "nameDate" => "end",
                "type" => DateTimeType::class,
                "constraints" => [new NotBlank()]
            ]
        ];
    }
}