<?php

namespace App\Form;

use App\Entity\Boulder;
use App\Entity\BoulderRating;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class BoulderRatingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("rating", NumberType::class, [
                "constraints" => [
                    new NotBlank(),
                    new Range([
                        "min" => 0,
                        "max" => 10
                    ])
                ]
            ])
            ->add("boulder", EntityType::class, [
                "class" => Boulder::class,
                "constraints" => [new NotBlank()]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "csrf_protection" => false,
            "data_class" => BoulderRating::class,
        ]);
    }
}