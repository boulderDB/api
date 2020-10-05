<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Service\ContextService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReservationType extends AbstractType
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
            ->add("start_time", TextType::class, [
                "constraints" => [new NotBlank()],
            ])
            ->add("end_time", TextType::class, [
                "constraints" => [new NotBlank()],
            ])
            ->add("date", DateType::class, [
                "widget" => "single_text",
                "input_format" => "Y-m-d",
                "constraints" => [new NotBlank()],
            ])
            ->add("room", EntityType::class, [
                "class" => Room::class,
                "query_builder" => function (EntityRepository $repository) use ($locationId) {
                    return $repository->createQueryBuilder("room")
                        ->where("room.location = :locationId")
                        ->setParameter("locationId", $locationId);
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Reservation::class,
            "csrf_protection" => false,
        ]);
    }
}