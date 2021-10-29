<?php

namespace App\Controller;

use App\Entity\Area;
use App\Entity\Boulder;
use App\Entity\BoulderTag;
use App\Entity\Grade;
use App\Entity\HoldType;
use App\Entity\Setter;
use App\Entity\Wall;
use App\Form\AreaType;
use App\Form\BoulderTagType;
use App\Form\BoulderType;
use App\Form\GradeType;
use App\Form\HoldTypeType;
use App\Form\SchemaTypeInterface;
use App\Form\SetterType;
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
        Boulder::RESOURCE_NAME => BoulderType::class,
        BoulderTag::RESOURCE_NAME => BoulderTagType::class,
        Grade::RESOURCE_NAME => GradeType::class,
        HoldType::RESOURCE_NAME => HoldTypeType::class,
        Setter::RESOURCE_NAME => SetterType::class,
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

        $data = $form->getSchema();

        foreach ($data as &$field) {
            $elements = explode("\\", $field["type"]);
            $key = array_key_last($elements);

            $field["type"] = $elements[$key];
        }

        return $this->json($data);
    }

}