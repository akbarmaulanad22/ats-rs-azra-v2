<?php

namespace App\Enums;

enum TingkatKemampuanBahasa: string
{
    case Baik = 'baik';
    case Sedang = 'sedang';
    case Kurang = 'kurang';

    public function label(): string
    {
        return match ($this) {
            self::Baik => 'Baik',
            self::Sedang => 'Sedang',
            self::Kurang => 'Kurang',
        };
    }
}
