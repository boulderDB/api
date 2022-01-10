<?php

namespace App\Form;

use App\Entity\Wall;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class WallType extends AbstractSchemaType
{

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
                "type" => TextType::class,
                "constraints" => [new NotBlank()],
                "options" => [
                    "constraints" => []
                ],
            ],
            [
                "name" => "description",
                "type" => TextType::class,
                "constraints" => [],
                "options" => [
                    "constraints" => [new NotBlank()]
                ],
            ],
            [
                "name" => "media",
                "type" => TextType::class,
                "schema" => [
                    "type" => "upload",
                    "resource" => "/upload"
                ],
                "options" => [
                    "constraints" => []
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