<?php

namespace App\Form;

use App\Entity\HoldType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class HoldTypeType extends AbstractSchemaType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => HoldType::class,
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
                "name" => "image",
                "type" => TextType::class,
                "options" => [
                    "constraints" => [new NotBlank()]
                ],
                "schema" => [
                    "type" => "upload",
                    "resource" => "/upload"
                ]
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