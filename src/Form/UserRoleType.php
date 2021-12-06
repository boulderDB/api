<?php

namespace App\Form;

use App\Entity\Ascent;
use App\Entity\AscentDoubt;
use App\Entity\Boulder;
use App\Entity\User;
use App\Service\ContextService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserRoleType extends AbstractType
{
    private ?ContextService $contextService;

    public function __construct(ContextService $contextService = null)
    {
        $this->contextService = $contextService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locationId = $this->contextService?->getLocation()?->getId();

        $options = array_map(function (string $role) use ($locationId) {
            return ContextService::getLocationRoleName($role, $locationId, true);
        }, User::ROLES);

        $builder
            ->add("roles", ChoiceType::class, [
                "constraints" => [new NotBlank()],
                "multiple" => true,
                "choices" => array_combine($options, $options)
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "csrf_protection" => false,
        ]);
    }
}