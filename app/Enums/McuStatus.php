<?php

namespace App\Enums;

enum McuStatus: string
{
    case Dijadwalkan = 'dijadwalkan';
    case Selesai = 'selesai';
    case Lulus = 'lulus';
    case TidakLulus = 'tidak_lulus';

    public function label(): string
    {
        return match ($this) {
            self::Dijadwalkan => 'Dijadwalkan',
            self::Selesai => 'Selesai',
            self::Lulus => 'Lulus',
            self::TidakLulus => 'Tidak Lulus',
        };
    }

    public function isPassed(): bool
    {
        return $this === self::Lulus;
    }

    public function isFailed(): bool
    {
        return $this === self::TidakLulus;
    }
}
