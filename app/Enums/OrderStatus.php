<?php

namespace App\Enums;

enum OrderStatus: string
{
    case MenungguPembayaran = 'menunggu_pembayaran';
    case MencariMitra = 'mencari_mitra';
    case Dijadwalkan = 'dijadwalkan';
    case MenujuLokasi = 'menuju_lokasi';
    case Tiba = 'tiba';
    case SedangBerlangsung = 'sedang_berlangsung';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';
    case Kedaluwarsa = 'kedaluwarsa';

    public function label(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'Menunggu Pembayaran',
            self::MencariMitra => 'Mencari Mitra',
            self::Dijadwalkan => 'Dijadwalkan',
            self::MenujuLokasi => 'Menuju Lokasi',
            self::Tiba => 'Tiba di Lokasi',
            self::SedangBerlangsung => 'Sedang Berlangsung',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
            self::Kedaluwarsa => 'Kedaluwarsa',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'bg-slate-100 text-slate-700 border-slate-200',
            self::MencariMitra => 'bg-blue-50 text-blue-700 border-blue-200',
            self::Dijadwalkan => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::MenujuLokasi => 'bg-purple-50 text-purple-700 border-purple-200',
            self::Tiba => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            self::SedangBerlangsung => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Selesai => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Dibatalkan => 'bg-rose-50 text-rose-700 border-rose-200',
            self::Kedaluwarsa => 'bg-slate-100 text-slate-500 border-slate-200',
        };
    }

    public function dotClass(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'bg-slate-400',
            self::MencariMitra => 'bg-blue-500 animate-pulse',
            self::Dijadwalkan => 'bg-indigo-500',
            self::MenujuLokasi => 'bg-purple-500',
            self::Tiba => 'bg-cyan-500',
            self::SedangBerlangsung => 'bg-amber-500',
            self::Selesai => 'bg-emerald-500',
            self::Dibatalkan => 'bg-rose-500',
            self::Kedaluwarsa => 'bg-slate-400',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Selesai, self::Dibatalkan, self::Kedaluwarsa]);
    }

    public function isActive(): bool
    {
        return in_array($this, [
            self::MencariMitra,
            self::Dijadwalkan,
            self::MenujuLokasi,
            self::Tiba,
            self::SedangBerlangsung,
        ]);
    }
}
