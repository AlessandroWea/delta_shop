<?php

namespace App\Utils;

class Utils
{
    public function toPercent($value, $sum)
    {
        return ($sum > 0) ? (100 * $value) / $sum : 0;
    }
}