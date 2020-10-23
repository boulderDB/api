<?php

namespace App\Form;

use App\Entity\Room;
use App\Entity\TimeSlot;
use App\Service\ContextService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TimeSlotType extends AbstractType
{
    private const DAY_NAME_CHOICES = [
        "Monday" => "monday",
        "Tuesday" => "tuesday",
        "Wednesday" => "wednesday",
        "Thursday" => "thursday",
        "Friday" => "friday",
        "Saturday" => "saturday",
        "Sunday" => "sunday"
    ];

    private ContextService $contextService;

    public function __construct(ContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locationId = $this->contextService->getLocation()->getId();

        $builder
            ->add("capacity", NumberType::class, [
                "constraints" => [new NotBlank()],
            ])
            ->add("min_quantity", NumberType::class, [
                "constraints" => [new NotBlank()],
            ])
            ->add("max_quantity", NumberType::class, [
                "constraints" => [new NotBlank()],
            ])
            ->add("room", EntityType::class, [
                "class" => Room::class,
                "query_builder" => function (EntityRepository $repository) use ($locationId) {
                    return $repository->createQueryBuilder("room")
                        ->where("room.location = :locationId")
                        ->setParameter("locationId", $locationId);
                },
                "constraints" => [new NotBlank()],
            ])
            ->add("day_name", ChoiceType::class, [
                "constraints" => [new NotBlank()],
                "choices" => self::DAY_NAME_CHOICES
            ])
            ->add("start_time", TextType::class, [
                "constraints" => [
                    new Length([
                        "min" => 5,
                        "max" => 5
                    ]),
                    new NotBlank()
                ],
            ])
            ->add("end_time", TextType::class, [
                "constraints" => [
                    new Length([
                        "min" => 5,
                        "max" => 5
                    ]),
                    new NotBlank()
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => TimeSlot::class,
            "csrf_protection" => false,
        ]);
    }
}
