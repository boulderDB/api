<?php

namespace App\Form;

use App\Entity\Boulder;
use App\Entity\BoulderTag;
use App\Entity\Grade;
use App\Entity\HoldStyle;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Wall;
use App\Service\ContextService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class BoulderType extends AbstractType
{
    private $contextService;

    public function __construct(ContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $setterRole = $this->contextService->getLocationRole(User::ROLE_SETTER);
        $locationId = $this->contextService->getLocation()->getId();

        $setterQuery = function (EntityRepository $entityRepository) use ($setterRole) {

            return $entityRepository->createQueryBuilder('user')
                ->where('user.roles LIKE :role')
                ->setParameter('role', '%"' . $setterRole . '"%')
                ->orderBy('lower(user.username)', 'ASC');
        };

        $locationQuery = function (EntityRepository $entityRepository) use ($locationId) {

            return $entityRepository->createQueryBuilder('locationResource')
                ->where('locationResource.location = :location')
                ->setParameter('location', $locationId);
        };

        $builder
            ->add('name', TextType::class, [])
            ->add('holdStyle', EntityType::class, [
                'class' => HoldStyle::class,
                'constraints' => [new NotBlank()],
                'query_builder' => $locationQuery
            ])
            ->add('grade', EntityType::class,
                [
                    'class' => Grade::class,
                    'constraints' => [new NotBlank()],
                    'query_builder' => $locationQuery
                ]
            )
            ->add('internalGrade', EntityType::class,
                [
                    'class' => Grade::class,
                    'query_builder' => $locationQuery
                ]
            )
            ->add('startWall', EntityType::class, [
                'class' => Wall::class,
                'constraints' => [new NotBlank()],
                'query_builder' => $locationQuery
            ])
            ->add('endWall', EntityType::class, [
                'class' => Wall::class,
                'query_builder' => $locationQuery

            ])
            ->add('setters', EntityType::class,
                [
                    'class' => User::class,
                    'multiple' => true,
                    'constraints' => [new NotNull()],
                    'query_builder' => $setterQuery
                ]
            )
            ->add('tags', EntityType::class, [
                'class' => BoulderTag::class,
                'multiple' => true,
                'constraints' => [new NotNull()],
                'query_builder' => $locationQuery
            ])
            ->add('points', IntegerType::class, [
                'constraints' => [new NotBlank()]
            ])
            ->add('status', ChoiceType::class, [
                    'constraints' => [new NotBlank()],
                    'choices' => [
                        'active' => Boulder::STATUS_ACTIVE,
                        'removed' => Boulder::STATUS_INACTIVE
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
