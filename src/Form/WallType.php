<?php

namespace App\Form;

use App\Entity\Wall;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class WallType extends AbstractType implements SchemaTypeInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->getSchema() as $field) {
            $builder->add($field["name"], $field["type"], $field["options"]);
        }
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
                "type" => TextType::class,
                "constraints" => [new NotBlank()]
            ],
            [
                "name" => "description",
                "type" => TextType::class,
                "constraints" => [new NotBlank()]
            ],
            [
                "name" => "media",
                "type" => TextType::class,
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