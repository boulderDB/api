<?php

namespace App\Helper;

use Carbon\Carbon;

class TimeHelper
{
    public const DATE_FORMAT_DATE = "Y-m-d";
    public const DATE_FORMAT_DATETIME = "Y-m-d H:i:s";
    public const DATE_FORM_TIME = "H:i";

    public const SERVER_TIME_OFFSET = "+2 hours";

    public static function convertToCarbonDate(string $ymd, string $time = null): Carbon
    {
        if (!$time) {
            return Carbon::parse($ymd);
        }

        return Carbon::createFromFormat(self::DATE_FORMAT_DATETIME, "$ymd $time:00");
    }
}
