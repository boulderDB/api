<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReadableIdentifierType extends AbstractSchemaType
{
    public function getSchema(): array
    {
        return [
            [
                "name" => "value",
                "type" => TextType::class,
                "options" => [
                    "constraints" => [
                        new NotBlank(),
                        new Length(["min" => 2, "max" => 50])
                    ]
                ]
            ]
        ];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false
        ]);
    }
}