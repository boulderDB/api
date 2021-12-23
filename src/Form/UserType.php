<?php

namespace App\Form;

use App\Entity\Notification;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("visible", CheckboxType::class)
            ->add("gender", ChoiceType::class, [
                "choices" => [
                    "female" => "female",
                    "male" => "male",
                    "neutral" => "neutral"
                ],
                "constraints" => [
                    new NotBlank()
                ]
            ])
            ->add("email", EmailType::class, [
                "constraints" => [
                    new NotBlank()
                ]
            ])
            ->add("firstName", TextType::class)
            ->add("lastName", TextType::class)
            ->add("image", TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "csrf_protection" => false,
            "data_class" => User::class,
            "allow_extra_fields" => true
        ]);
    }

    public static function passWordField(): array
    {
        return [
            "password",
            TextType::class,
            [
                "property_path" => "plainPassword",
                "constraints" => [
                    new NotBlank(),
                    new Length(["min" => 6, "max" => 50])
                ]
            ]
        ];
    }

    public static function usernameField(): array
    {
        return [
            "username",
            TextType::class,
            [
                "constraints" => [
                    new NotBlank(),
                    new Length(["min" => 2, "max" => 50])
                ]
            ]
        ];
    }

    public static function notificationsField(array $notifications): array
    {

        return [
            "notifications",
            EntityType::class,
            [
                "class" => Notification::class,
                "multiple" => true,
                "constraints" => [],
                "choices" => $notifications,
                'choice_value' => function (?Notification $entity) {
                    return $entity ? $entity->getIdentifier() : '';
                },
            ]
        ];
    }
}
