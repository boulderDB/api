<?php

namespace App\Controller;

use App\Exception\RateLimitException;
use App\Factory\RedisConnectionFactory;
use Symfony\Component\HttpFoundation\Request;

trait RateLimiterTrait
{
    public static function rateLimit(Request $request, string $resource, int $limit = 60, int $timeout = 3600): void
    {
        $ip = $_SERVER["HTTP_X_REAL_IP"] ?? $request->getClientIp();
        $redis = RedisConnectionFactory::create();

        $key = "request_limit_{$resource}_{$ip}";

        if ($redis->exists($key)) {
            $redis->incr($key);
        } else {
            $redis->set($key, 1, $timeout);
        }

        if ($redis->get($key) > $limit) {
            throw new RateLimitException();
        }
    }

}