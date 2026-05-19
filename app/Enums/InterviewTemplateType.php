<?php

namespace App\Enums;

enum InterviewTemplateType: string
{
    case KriteriaPenilaian = 'kriteria_penilaian';
    case Kesiapan = 'kesiapan';

    public function label(): string
    {
        return match ($this) {
            self::KriteriaPenilaian => 'Kriteria Penilaian',
            self::Kesiapan => 'Kesiapan',
        };
    }
}
