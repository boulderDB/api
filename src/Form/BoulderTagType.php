<?php

namespace App\Form;

use App\Entity\BoulderTag;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BoulderTagType extends AbstractSchemaType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => BoulderTag::class,
        ]);
    }

    public function getSchema(): array
    {
        return [
            [
                "name" => "name",
                "type" => TextType::class,
                "options" => [
                    "constraints" => [new NotBlank()]
                ],
            ],
            [
                "name" => "emoji",
                "type" => TextType::class,
                "options" => [
                    "constraints" => [new NotBlank()]
                ],
            ],
            [
                "name" => "active",
                "type" => CheckboxType::class,
                "options" => [
                    "constraints" => []
                ],
            ],
        ];
    }
}