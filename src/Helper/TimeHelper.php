<?php

namespace App\Helper;

use Carbon\Carbon;

class TimeHelper
{
    public const DAYS = [
        1 => "monday",
        2 => "tuesday",
        3 => "wednesday",
        4 => "thursday",
        5 => "friday",
        6 => "saturday",
        7 => "sunday"
    ];

    public static function getDayNumber(string $name): int
    {
        return array_search(strtolower($name), self::DAYS);
    }

    public const DATE_FORMAT_DATE = "Y-m-d";
    public const DATE_FORMAT_DATETIME = "Y-m-d H:i:s";
    public const DATE_FORM_TIME = "H:i";

    public static function convertToCarbonDate(string $ymd, string $time = null, int $day = null): Carbon
    {
        if (!$time) {
            return Carbon::parse($ymd);
        }

        $carbon = Carbon::createFromFormat(self::DATE_FORMAT_DATETIME, "$ymd $time:00");

        if ($day) {
            $carbon->setDay($day);
        }

        return $carbon;
    }


}
