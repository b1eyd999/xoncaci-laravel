<?php

namespace App\Support;

class Money
{
    public static function format(float|int|string|null $amount): string
    {
        $amount = (float) $amount;
        $text = fmod($amount, 1.0) == 0.0 ? number_format($amount, 0, '.', ' ') : number_format($amount, 2, '.', ' ');

        return $text . ' ₼';
    }
}
