<?php

namespace App\Enums;

enum Role: string
{
    case Superadmin = 'superadmin';
    case Admin = 'admin';
    case Pelanggan = 'pelanggan';
    case Mitra = 'mitra';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Pelanggan => 'Pelanggan',
            self::Mitra => 'Mitra Terapis',
        };
    }
}
