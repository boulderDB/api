<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class RateLimitException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            "Too many requests",
            Response::HTTP_TOO_MANY_REQUESTS,
        );
    }
}