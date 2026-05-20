<?php

namespace App\Enums;

enum McuStatus: string
{
    case Lulus = 'lulus';
    case Ditangguhkan = 'ditangguhkan';
    case TidakLulus = 'tidak_lulus';

    public function label(): string
    {
        return match ($this) {
            self::Lulus => 'Lulus',
            self::Ditangguhkan => 'Ditangguhkan',
            self::TidakLulus => 'Tidak Lulus',
        };
    }
}
