<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case Menunggu = 'menunggu';
    case Diproses = 'diproses';
    case Selesai = 'selesai';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Aduan Baru',
            self::Diproses => 'Sedang Diproses',
            self::Selesai => 'Selesai',
            self::Ditolak => 'Ditolak / Diabaikan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Menunggu => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Diproses => 'bg-sky-50 text-sky-700 border-sky-200',
            self::Selesai => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Ditolak => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    public function dotClass(): string
    {
        return match ($this) {
            self::Menunggu => 'bg-amber-500',
            self::Diproses => 'bg-sky-500',
            self::Selesai => 'bg-emerald-500',
            self::Ditolak => 'bg-slate-400',
        };
    }
}
