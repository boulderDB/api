<?php

namespace App\Form;

use App\Entity\Wall;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class WallType extends AbstractType implements SchemaType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", TextType::class, [
                "constraints" => [new NotBlank()]
            ])
            ->add("description", TextType::class, [
                "constraints" => [new NotBlank()]
            ])
            ->add("media", TextType::class)
            ->add("active", CheckboxType::class, [
                "constraints" => [new NotNull()]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "csrf_protection" => false,
            "data_class" => Wall::class,
        ]);
    }

    public function getSchema(): array
    {
        return [
            [
                "name" => "name",
                "type" => "TextType",
                "constraints" => ["NotBlank"]
            ],
            [
                "name" => "description",
                "type" => "TextType",
                "constraints" => ["NotBlank"]
            ],
            [
                "name" => "media",
                "type" => "TextType",
            ],
            [
                "name" => "active",
                "type" => "CheckboxType",
                "constraints" => ["NotBlank"]
            ]
        ];
    }
}