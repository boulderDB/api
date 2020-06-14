<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('visible', CheckboxType::class)
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('media', TextType::class, [
                'constraints' => [new NotBlank()]
            ])
            ->add('armSpan', NumberType::class, [
                'constraints' => [
                    new GreaterThanOrEqual(120),
                    new LessThanOrEqual(220)
                ]
            ])
            ->add('height', NumberType::class, [
                'constraints' => [
                    new GreaterThanOrEqual(120),
                    new LessThanOrEqual(220)
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => User::class,
            'allow_extra_fields' => true
        ]);
    }
}