<?php

namespace App\Controller;

use App\Entity\Area;
use App\Entity\Ascent;
use App\Entity\AscentDoubt;
use App\Entity\Boulder;
use App\Entity\BoulderComment;
use App\Entity\BoulderError;
use App\Entity\BoulderRating;
use App\Entity\BoulderTag;
use App\Entity\Grade;
use App\Entity\HoldType;
use App\Entity\Setter;
use App\Entity\User;
use App\Entity\Wall;
use App\Form\AreaType;
use App\Form\AscentDoubtType;
use App\Form\AscentType;
use App\Form\BoulderCommentType;
use App\Form\BoulderErrorType;
use App\Form\BoulderRatingType;
use App\Form\BoulderTagType;
use App\Form\BoulderType;
use App\Form\GradeType;
use App\Form\HoldTypeType;
use App\Form\SchemaTypeInterface;
use App\Form\SetterType;
use App\Form\UserType;
use App\Form\WallTypeInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/schemas")
 */
class SchemaController extends AbstractController
{
    use ResponseTrait;

    private const MAP = [
        Area::RESOURCE_NAME => AreaType::class,
        Ascent::RESOURCE_NAME => AscentType::class,
        AscentDoubt::RESOURCE_NAME => AscentDoubtType::class,
        Boulder::RESOURCE_NAME => BoulderType::class,
        BoulderComment::RESOURCE_NAME => BoulderCommentType::class,
        BoulderError::RESOURCE_NAME => BoulderErrorType::class,
        BoulderRating::RESOURCE_NAME => BoulderRatingType::class,
        BoulderTag::RESOURCE_NAME => BoulderTagType::class,
        Grade::RESOURCE_NAME => GradeType::class,
        HoldType::RESOURCE_NAME => HoldTypeType::class,
        Setter::RESOURCE_NAME => SetterType::class,
        User::RESOURCE_NAME => UserType::class,
        Wall::RESOURCE_NAME => WallTypeInterface::class,
    ];

    /**
     * @Route("/{name}", methods={"GET"}, name="boulders_schema")
     */
    public function name(string $name): JsonResponse
    {
        if (!array_key_exists($name, self::MAP)) {
            return $this->resourceNotFoundResponse($name);
        }

        $class = self::MAP[$name];

        $form = new $class;

        if (!$form instanceof SchemaTypeInterface) {
            return $this->json(null, Response::HTTP_NOT_IMPLEMENTED);
        }

        return $this->json($form->getSchema());
    }

}