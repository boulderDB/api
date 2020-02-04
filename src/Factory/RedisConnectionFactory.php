<?php

namespace App\Factory;

class RedisConnectionFactory
{
    public static function create()
    {
        $redis = new \Redis();
        $redis->connect(getenv("REDIS_HOST"));

        return $redis;
    }

}