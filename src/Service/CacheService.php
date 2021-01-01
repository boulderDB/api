<?php

namespace App\Service;

class CacheService
{
    public static function getCurrentRankingKey(string $locationId): string
    {
        return "location-{$locationId}-current-ranking";
    }

    public static function getCurrentRankingTimestampKey(string $locationId): string
    {
        return "location-{$locationId}-current-ranking:last-run";
    }

    public static function getAllTimeRankingKey(int $locationId)
    {
        return "location-{$locationId}-all-time-ranking";
    }
}