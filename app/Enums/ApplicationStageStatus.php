<?php

namespace App\Enums;

enum ApplicationStageStatus: string
{
    case Pending = 'pending';
    case Aktif = 'aktif';
    case Selesai = 'selesai';
    case Gagal = 'gagal';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Aktif => 'Aktif',
            self::Selesai => 'Selesai',
            self::Gagal => 'Gagal',
        };
    }
}
