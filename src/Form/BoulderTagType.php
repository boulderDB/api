<?php

namespace App\Form;

use App\Entity\BoulderTag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class BoulderTagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class, [
                "constraints" => [new NotBlank()]
            ])
            ->add("emoji", TextType::class, [
                "constraints" => [new NotBlank()]
            ])
            ->add("active", CheckboxType::class, [
                "constraints" => [new NotNull()]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => BoulderTag::class,
        ]);
    }

}