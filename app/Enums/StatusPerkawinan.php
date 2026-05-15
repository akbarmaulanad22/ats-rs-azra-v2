<?php

namespace App\Enums;

enum StatusPerkawinan: string
{
    case BelumMenikah = 'belum_menikah';
    case Menikah = 'menikah';
    case Duda = 'duda';
    case Janda = 'janda';

    public function label(): string
    {
        return match ($this) {
            self::BelumMenikah => 'Belum Menikah',
            self::Menikah => 'Menikah',
            self::Duda => 'Duda',
            self::Janda => 'Janda',
        };
    }
}
