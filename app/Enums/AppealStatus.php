<?php

namespace App\Enums;

enum AppealStatus: string
{
    case Menunggu = 'menunggu';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu Peninjauan',
            self::Disetujui => 'Banding Disetujui',
            self::Ditolak => 'Banding Ditolak',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Menunggu => 'bg-amber-50 text-amber-800 border border-amber-200',
            self::Disetujui => 'bg-emerald-50 text-emerald-800 border border-emerald-200',
            self::Ditolak => 'bg-rose-50 text-rose-800 border border-rose-200',
        };
    }
}
