<?php

namespace App\Enums;

enum QuestionType: string
{
    case Mc = 'mc';
    case Essay = 'essay';

    public function label(): string
    {
        return match ($this) {
            self::Mc => 'Pilihan Ganda',
            self::Essay => 'Esai',
        };
    }
}
