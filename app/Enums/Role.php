<?php

namespace App\Enums;

enum Role: string
{
    case HrAdmin = 'hr_admin';
    case HrManager = 'hr_manager';
    case UnitHead = 'unit_head';
    case Director = 'director';
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::HrAdmin => 'Admin HR',
            self::HrManager => 'Manajer HR',
            self::UnitHead => 'Kepala Unit',
            self::Director => 'Direktur',
            self::Employee => 'Karyawan',
        };
    }
}
