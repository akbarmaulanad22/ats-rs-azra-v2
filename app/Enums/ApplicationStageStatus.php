<?php

namespace App\Enums;

enum ApplicationStageStatus: string
{
    case Pending = 'pending';
    case Aktif = 'aktif';
    case Reserved = 'reserved';
    case Selesai = 'selesai';
    case Gagal = 'gagal';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Aktif => 'Aktif',
            self::Reserved => 'Ditangguhkan',
            self::Selesai => 'Selesai',
            self::Gagal => 'Gagal',
        };
    }

    public function isAdvanceable(): bool
    {
        return $this === self::Aktif || $this === self::Reserved;
    }
}
