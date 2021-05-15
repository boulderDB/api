<?php

namespace App\Form;

use App\Entity\Boulder;
use App\Entity\BoulderComment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BoulderCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("message", TextType::class, [
                "constraints" => [new NotBlank()]
            ])
            ->add("boulder", EntityType::class, [
                "class" => Boulder::class,
                "constraints" => [new NotBlank()]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "csrf_protection" => false,
            "data_class" => BoulderComment::class,
        ]);
    }
}