<?php

namespace App\Enums;

enum UserStatus: string
{
    case BelumLengkap = 'belum_lengkap';
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Terverifikasi = 'terverifikasi';
    case Ditolak = 'ditolak';
    case Ditangguhkan = 'ditangguhkan';
    case Diblokir = 'diblokir';

    public function label(): string
    {
        return match ($this) {
            self::BelumLengkap => 'Belum Lengkap',
            self::MenungguVerifikasi => 'Menunggu Verifikasi',
            self::Terverifikasi => 'Terverifikasi',
            self::Ditolak => 'Ditolak',
            self::Ditangguhkan => 'Ditangguhkan',
            self::Diblokir => 'Diblokir',
        };
    }
}
