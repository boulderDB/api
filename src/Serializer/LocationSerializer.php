<?php

namespace App\Serializer;

class LocationSerializer
{
    public static function serializeArray(array $location)
    {
        $location['addressLineOne'] = $location['address_line_one'];
        $location['addressLineTwo'] = $location['address_line_two'];
        $location['countryCode'] = $location['country_code'];

        return $location;
    }
}