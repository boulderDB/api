<?php

namespace App\Form;

use App\Entity\Location;
use App\Entity\Setter;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SetterType extends AbstractType implements SchemaTypeInterface
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
            "data_class" => Setter::class,
            "csrf_protection" => false,
        ]);
    }

    public function getSchema(): array
    {
        return [
            [
                "name" => "active",
                "type" => CheckboxType::class,
                "options" => [
                    "constraints" => []
                ],
            ],
            [
                "name" => "username",
                "type" => TextType::class,
                "options" => [
                    "constraints" => [new NotBlank()]
                ],
            ],
            [
                "name" => "user",
                "type" => EntityType::class,
                "options" => [
                    "constraints" => [new NotBlank()],
                    "class" => User::class,
                ],
                "schema" => [
                    "resource" => "/users/search",
                    "labelProperty" => "name"
                ]
            ],
        ];
    }
}
