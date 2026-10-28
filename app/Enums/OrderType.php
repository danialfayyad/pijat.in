<?php

namespace App\Enums;

enum OrderType: string
{
    case Instan = 'instan';
    case Terjadwal = 'terjadwal';

    public function label(): string
    {
        return match ($this) {
            self::Instan => 'Layanan Instan',
            self::Terjadwal => 'Layanan Terjadwal',
        };
    }
}
