<?php

namespace App\Enums;

enum OfferingLetterStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Respon',
            self::Accepted => 'Diterima Kandidat',
            self::Rejected => 'Ditolak Kandidat',
        };
    }
}
