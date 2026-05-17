<?php

namespace App\Enums;

enum MbtiPole: string
{
    case E = 'E';
    case I = 'I';
    case S = 'S';
    case N = 'N';
    case T = 'T';
    case F = 'F';
    case J = 'J';
    case P = 'P';

    public function label(): string
    {
        return match ($this) {
            self::E => 'Extraversion (Ekstrovert)',
            self::I => 'Introversion (Introvert)',
            self::S => 'Sensing (Penginderaan)',
            self::N => 'Intuition (Intuisi)',
            self::T => 'Thinking (Pemikiran)',
            self::F => 'Feeling (Perasaan)',
            self::J => 'Judging (Terstruktur)',
            self::P => 'Perceiving (Fleksibel)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::E => 'Ekstrovert',
            self::I => 'Introvert',
            self::S => 'Penginderaan',
            self::N => 'Intuisi',
            self::T => 'Pemikiran',
            self::F => 'Perasaan',
            self::J => 'Terstruktur',
            self::P => 'Fleksibel',
        };
    }

    public function opposite(): self
    {
        return match ($this) {
            self::E => self::I,
            self::I => self::E,
            self::S => self::N,
            self::N => self::S,
            self::T => self::F,
            self::F => self::T,
            self::J => self::P,
            self::P => self::J,
        };
    }
}
