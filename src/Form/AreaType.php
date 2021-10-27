<?php

namespace App\Form;

use App\Entity\Area;
use App\Entity\Wall;
use App\Service\ContextService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class AreaType extends AbstractType
{
    private ContextService $contextService;

    public function __construct(ContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locationId = $this->contextService->getLocation()?->getId();

        $builder
            ->add("name", TextType::class, [
                "constraints" => [new NotBlank()]
            ])
            ->add("walls", EntityType::class, [
                "class" => Wall::class,
                "multiple" => true,
                "query_builder" => function (EntityRepository $entityRepository) use ($locationId) {
                    return $entityRepository->createQueryBuilder("wall")
                        ->where("wall.location = :location")
                        ->setParameter("location", $locationId);
                }
            ])
            ->add("active", CheckboxType::class, [
                    "constraints" => [new NotNull()]
                ]
            );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => Area::class,
        ]);
    }
}