<?php

namespace App\Serializer;

use App\Entity\Location;
use App\Service\SerializerInterface;

class LocationSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var Location $class
         */
        $data = [
            "id" => $class->getId(),
            "name" => $class->getName(),
            "url" => $class->getUrl()
        ];

        if (in_array(self::GROUP_DETAIL, $groups)) {

            $data = array_merge($data, [
                "city" => $class->getCity(),
                "zip" => $class->getZip(),
                "address_line_one" => $class->getAddressLineOne(),
                "address_line_two" => $class->getAddressLineTwo(),
                "country_code" => $class->getCountryCode(),
                "image" => $class->getImage(),
                "website" => $class->getWebsite(),
                "facebook" => $class->getFacebook(),
                "instagram" => $class->getInstagram(),
                "twitter" => $class->getTwitter(),
            ]);
        }

        return $data;
    }
}