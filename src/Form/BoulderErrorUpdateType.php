<?php

namespace App\Form;

use App\Entity\Boulder;
use App\Entity\BoulderError;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BoulderErrorUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("status", ChoiceType::class, [
                "choices" => [
                    BoulderError::STATUS_UNRESOLVED => BoulderError::STATUS_UNRESOLVED,
                    BoulderError::STATUS_RESOLVED => BoulderError::STATUS_RESOLVED
                ],
                "constraints" => [
                    new NotBlank()
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "csrf_protection" => false,
            "data_class" => BoulderError::class,
        ]);
    }
}