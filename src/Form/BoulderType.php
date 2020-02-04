<?php

namespace App\Form;

use App\Components\Constants;
use App\Entity\Boulder;
use App\Entity\Grade;
use App\Entity\HoldStyle;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Wall;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BoulderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [new NotBlank()]
            ])
            ->add('color', EntityType::class, [
                'class' => HoldStyle::class,
                'constraints' => [new NotBlank()]
            ])
            ->add('grade', EntityType::class,
                [
                    'class' => Grade::class,
                    'constraints' => [new NotBlank()]
                ]
            )
            ->add('startWall', EntityType::class, [
                'class' => Wall::class,
                'constraints' => [new NotBlank()]
            ])
            ->add('endWall', EntityType::class, [
                'class' => Wall::class
            ])
            ->add('setters', EntityType::class,
                [
                    'class' => User::class,
                    'multiple' => true,
                    'constraints' => [new NotBlank()],
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository->createQueryBuilder('user')
                            ->where('user.roles LIKE :roles')
                            ->setParameter('roles', '%"' . Constants::ROLE_SETTER . '"%')
                            ->orderBy('lower(user.username)', 'ASC');
                    },
                ]
            )
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'constraints' => [new NotBlank()]
            ])
            ->add('points', IntegerType::class, [
                'constraints' => [new NotBlank()]
            ])
            ->add('status', ChoiceType::class, [
                    'constraints' => [new NotBlank()],
                    'choices' => [
                        'active' => Constants::STATUS_ACTIVE,
                        'removed' => Constants::STATUS_INACTIVE
                    ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => Boulder::class,
        ]);
    }
}
