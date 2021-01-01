<?php

namespace App\Serializer;

use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Entity\Setter;
use App\Entity\Tag;
use App\Service\Serializer;
use App\Service\SerializerInterface;

class BoulderSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        $detail = in_array(SerializerInterface::GROUP_DETAIL, $groups);

        /**
         * @var Boulder $class
         */
        $data = [
            "id" => $class->getId(),
            "name" => $class->getName(),
            "points" => $class->getPoints(),

            "start_wall" => $detail ?
                Serializer::serialize($class->getStartWall()) :
                $class->getStartWall()->getId(),

            "end_wall" => $detail ?
                Serializer::serialize($class->getEndWall()) :
                $class->getEndWall()->getId(),

            "grade" => $detail ?
                Serializer::serialize($class->getGrade()) :
                $class->getGrade()->getId(),

            "hold_type" =>
                $detail ?
                    Serializer::serialize($class->getHoldType()) :
                    $class->getHoldType()->getId(),

            "tags" => array_map(function ($tag) use ($detail) {
                /**
                 * @var Tag $tag
                 */
                if ($detail) {

                    return [
                        "id" => $tag->getId(),
                        "name" => $tag->getName(),
                        "emoji" => $tag->getEmoji()
                    ];
                }

                return $tag->getId();

            }, $class->getTags()->toArray()),

            "setters" => array_map(function ($setter) use ($detail) {
                /**
                 * @var Setter $setter
                 */
                if ($detail) {

                    return [
                        "id" => $setter->getId(),
                        "username" => $setter->getUsername()
                    ];
                }

                return $setter->getId();

            }, $class->getSetters()->toArray()),
            "created_at" => Serializer::formatDate($class->getCreatedAt()),
        ];

        if ($detail) {

            $ascents = array_map(function ($ascent) {

                /**
                 * @var Ascent $ascent
                 */
                return [
                    "id" => $ascent->getId(),
                    "type" => $ascent->getType(),
                    "username" => $ascent->getUser()->getUsername()
                ];

            }, $class->getAscents()->toArray());

            $data["ascents"] = array_values($ascents);
        }

        return $data;
    }
}
