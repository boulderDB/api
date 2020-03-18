<?php

namespace App\Factory;

class RedisConnectionFactory
{
    public static function create()
    {
        $redis = new \Redis();
        $redis->connect($_ENV["REDIS_HOST"]);

        return $redis;
    }

}