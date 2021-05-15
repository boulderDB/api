<?php

namespace App\Serializer;

use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Entity\BoulderComment;
use App\Entity\Setter;
use App\Entity\Tag;
use App\Service\Serializer;
use App\Service\SerializerInterface;
use App\Service\SerializerTrait;

class BoulderSerializer implements SerializerInterface
{
    use SerializerTrait;

    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        $detail = self::matchesGroup(SerializerInterface::GROUP_DETAIL);
        $admin = self::matchesGroup(SerializerInterface::GROUP_ADMIN);

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
            "status" => $class->getStatus()
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

            $comments = array_map(function ($comment) {
                /**
                 * @var BoulderComment $comment
                 */
                return [
                    "id" => $comment->getId(),
                    "author" => $comment->getAuthor()->getUsername(),
                    "message" => $comment->getMessage(),
                    "created_at" => Serializer::formatDate($comment->getCreatedAt()),
                ];

            }, $class->getComments()->toArray());

            $data["ascents"] = array_values($ascents);
            $data["comments"] = array_values($comments);
        }

        if ($admin) {
            $data["internal_grade"] = Serializer::serialize($class->getInternalGrade(), $groups);
        }

        return $data;
    }
}
