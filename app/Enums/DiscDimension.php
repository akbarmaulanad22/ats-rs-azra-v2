<?php

namespace App\Enums;

enum DiscDimension: string
{
    case D = 'D';
    case I = 'I';
    case S = 'S';
    case C = 'C';

    public function label(): string
    {
        return match ($this) {
            self::D => 'Dominance (Dominan)',
            self::I => 'Influence (Berpengaruh)',
            self::S => 'Steadiness (Stabil)',
            self::C => 'Conscientiousness (Teliti)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::D => 'Dominan',
            self::I => 'Berpengaruh',
            self::S => 'Stabil',
            self::C => 'Teliti',
        };
    }
}
