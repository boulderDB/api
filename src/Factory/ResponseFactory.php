<?php

namespace App\Factory;

class ResponseFactory
{
    public static function createError(string $message, int $code)
    {
        return [
            "message" => $message,
            "code" => $code
        ];
    }
}