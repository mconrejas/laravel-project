<?php

namespace Buzzex\Http\Controllers\Main\Traits;

use Carbon\Carbon;

trait Serverable
{
    public function getServerTime()
    {
        $currentDate = Carbon::now();

        return [
            'timestamp' => $currentDate->getTimestamp(),
            'date' => $currentDate->format('Y-m-d'),
            'year' => $currentDate->format('Y'),
            'month' => $currentDate->format('m'),
            'day' => $currentDate->format('d'),
            'hour' => $currentDate->format('H'),
            'minute' => $currentDate->format('i'),
            'timezone' => $currentDate->format('e'),
            'isodate' => $currentDate->format('c'),
        ];
    }
}