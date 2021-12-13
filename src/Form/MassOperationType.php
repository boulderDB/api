<?php

namespace App\Form;

use App\Entity\Boulder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MassOperationType extends AbstractType
{
    public const OPERATION_DEACTIVATE = "deactivate";
    public const OPERATION_REACTIVATE = "reactivate";
    public const OPERATION_PRUNE_ASCENTS = "prune-ascents";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("items", EntityType::class, [
                "class" => Boulder::class,
                "multiple" => true,
            ])
            ->add("operation", ChoiceType::class, [
                "choices" => [
                    self::OPERATION_DEACTIVATE,
                    self::OPERATION_REACTIVATE,
                    self::OPERATION_PRUNE_ASCENTS,
                ],
                "constraints" => [new NotBlank()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "csrf_protection" => false
        ]);
    }
}