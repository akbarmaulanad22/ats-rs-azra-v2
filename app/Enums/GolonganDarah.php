<?php

namespace App\Enums;

enum GolonganDarah: string
{
    case A = 'A';
    case B = 'B';
    case AB = 'AB';
    case O = 'O';

    public function label(): string
    {
        return $this->value;
    }
}
