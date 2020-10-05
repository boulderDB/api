<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;

trait RequestTrait
{
    protected static function decodePayLoad(Request $request)
    {
        return json_decode($request->getContent(), true);
    }
}