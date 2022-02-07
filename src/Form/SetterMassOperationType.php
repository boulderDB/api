<?php

namespace App\Form;

use App\Entity\Setter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SetterMassOperationType extends AbstractType
{
    public const OPERATION_DEACTIVATE = "deactivate";
    public const OPERATION_REACTIVATE = "reactivate";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("items", EntityType::class, [
                "class" => Setter::class,
                "multiple" => true,
            ])
            ->add("operation", ChoiceType::class, [
                "choices" => [
                    self::OPERATION_DEACTIVATE,
                    self::OPERATION_REACTIVATE,
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