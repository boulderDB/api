<?php

namespace App\Form;

use App\Entity\AscentDoubt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AscentDoubtUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("status", ChoiceType::class, [
            "choices" => [
                AscentDoubt::STATUS_RESOLVED => AscentDoubt::STATUS_RESOLVED,
                AscentDoubt::STATUS_READ => AscentDoubt::STATUS_READ,
                AscentDoubt::STATUS_UNREAD => AscentDoubt::STATUS_UNREAD,
                AscentDoubt::STATUS_UNRESOLVED => AscentDoubt::STATUS_UNRESOLVED,
            ],
            "constraints" => [
                new NotBlank()
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "csrf_protection" => false,
            "data_class" => AscentDoubt::class,
        ]);
    }
}