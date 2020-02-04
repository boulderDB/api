<?php

namespace App\Form;

use App\Components\Constants;
use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AscentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'constraints' => [new NotBlank()],
                'class' => User::class
            ])
            ->add('boulder', EntityType::class, [
                'constraints' => [new NotBlank()],
                'class' => Boulder::class
            ])
            ->add('type', ChoiceType::class, [
                    'constraints' => [new NotBlank()],
                    'choices' => array_combine(Constants::ASCENT_TYPES, Constants::ASCENT_TYPES)
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => Ascent::class,
        ]);
    }
}