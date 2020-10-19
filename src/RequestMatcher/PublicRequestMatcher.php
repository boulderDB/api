<?php

namespace App\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class PublicRequestMatcher implements RequestMatcherInterface
{
    private const PATHS = [
        "/request-reset",
        "/reset",
        "/location",
        "/register",
        "/cancel-reservation",
        "/schedule/allocation",
    ];

    public function matches(Request $request)
    {
        $path = str_replace("/api", "", $request->getPathInfo());
        $location = $request->get("location");

        if ($location) {
            $path = str_replace("/{$location}", "", $path);
        }

        return in_array(strtolower($path), self::PATHS);
    }
}
