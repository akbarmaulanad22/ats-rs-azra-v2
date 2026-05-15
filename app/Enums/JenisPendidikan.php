<?php

namespace App\Enums;

enum JenisPendidikan: string
{
    case SD = 'sd';
    case SMP = 'smp';
    case SMAAtauSMK = 'sma_smk';
    case D1 = 'd1';
    case D2 = 'd2';
    case D3 = 'd3';
    case D4AtauS1 = 'd4_s1';
    case S2 = 's2';
    case S3 = 's3';

    public function label(): string
    {
        return match ($this) {
            self::SD => 'SD',
            self::SMP => 'SMP',
            self::SMAAtauSMK => 'SMA/SMK',
            self::D1 => 'D1',
            self::D2 => 'D2',
            self::D3 => 'D3',
            self::D4AtauS1 => 'D4/S1',
            self::S2 => 'S2',
            self::S3 => 'S3',
        };
    }
}
