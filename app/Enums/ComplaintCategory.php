<?php

namespace App\Enums;

enum ComplaintCategory: string
{
    case LayananTidakSesuai = 'layanan_tidak_sesuai';
    case KeterlambatanParah = 'keterlambatan_parah';
    case PerilakuTidakSopan = 'perilaku_tidak_sopan';
    case KebersihanHigienitas = 'kebersihan_higienitas';
    case TarifLuarAplikasi = 'tarif_luar_aplikasi';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::LayananTidakSesuai => 'Kualitas Layanan Tidak Sesuai',
            self::KeterlambatanParah => 'Keterlambatan Parah (>30 Menit)',
            self::PerilakuTidakSopan => 'Etika / Perilaku Tidak Sopan',
            self::KebersihanHigienitas => 'Standar Higienitas Kurang',
            self::TarifLuarAplikasi => 'Permintaan Tarif di Luar Aplikasi',
            self::Lainnya => 'Keluhan Lainnya',
        };
    }
}
