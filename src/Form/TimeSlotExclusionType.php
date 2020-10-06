<?php

namespace App\Form;

use App\Entity\Room;
use App\Entity\TimeSlotExclusion;
use App\Service\ContextService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TimeSlotExclusionType extends AbstractType
{
    private ContextService $contextService;

    public function __construct(ContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locationId = $this->contextService->getLocation()->getId();

        $builder
            ->add("start_date", DateTimeType::class, [
                "widget" => "single_text",
                "input_format" => "Y-m-d H:i:s",
                "constraints" => [new NotBlank()],
            ])
            ->add("end_date", DateTimeType::class, [
                "widget" => "single_text",
                "input_format" => "Y-m-d H:i:s",
                "constraints" => [new NotBlank()],
            ])
            ->add("quantity", NumberType::class)
            ->add("room", EntityType::class, [
                "class" => Room::class,
                "query_builder" => function (EntityRepository $repository) use ($locationId) {
                    return $repository->createQueryBuilder("room")
                        ->where("room.location = :locationId")
                        ->setParameter("locationId", $locationId);
                },
                "constraints" => [new NotBlank()],
            ])
            ->add("note", TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => TimeSlotExclusion::class
        ]);
    }
}