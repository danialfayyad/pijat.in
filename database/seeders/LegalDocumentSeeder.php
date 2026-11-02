<?php

namespace Database\Seeders;

use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::where('role', 'superadmin')->first();

        $documents = [
            [
                'type' => 'sop_kemitraan',
                'title' => 'Standar Operasional Prosedur (SOP) Kemitraan Terapis',
                'version' => '2.2',
                'summary' => 'Pedoman operasional, kebersihan, etika profesi, dan penegakan tata tertib layanan mitra terapis.',
                'content' => "STANDAR OPERASIONAL PROSEDUR KEMITRAAN PIJAT.IN (VERSI 2.2)\n\n1. KETENTUAN UMUM\nMitra terapis wajib mematuhi seluruh kode etik layanan wellness profesional, mengenakan pakaian rapi, menjaga kebersihan alat kerja, dan hadir tepat waktu sesuai jadwal pesanan.\n\n2. KEAMANAN & INTEGRITAS\nDilarang keras menawarkan atau menerima layanan di luar koridor pijat kebugaran resmi (anti-asusila). Setiap indikasi pelanggaran berat akan langsung diproses sanksi pemblokiran permanen (Blacklist).\n\n3. PEMBATALAN & KETERLAMBATAN\nMitra wajib memberikan konfirmasi cepat jika mengalami kendala darurat di jalan. Keterlambatan lebih dari batas toleransi tanpa kabar akan dikenakan poin sanksi operasional.\n\n4. PERSETUJUAN SISTEM\nDengan mengaktifkan status Siaga (Online), mitra menyatakan tunduk dan patuh pada seluruh aturan SOP Kemitraan ini.",
                'updated_by' => $superadmin?->id,
            ],
            [
                'type' => 'terms_and_conditions',
                'title' => 'Syarat dan Ketentuan Pengguna Layanan',
                'version' => '1.0',
                'summary' => 'Ketentuan pemesanan, hak dan kewajiban pelanggan, tarif tetap, dan kebijakan pembatalan.',
                'content' => "SYARAT DAN KETENTUAN PENGGUNA LAYANAN PIJAT.IN (VERSI 1.0)\n\n1. PENDAFTARAN AKUN\nPelanggan wajib mendaftar menggunakan nomor telepon aktif dan identitas yang valid. Satu akun hanya boleh digunakan oleh satu entitas pengguna.\n\n2. PEMESANAN & PEMBAYARAN\nSeluruh pesanan menggunakan tarif flat yang transparan tanpa biaya tersembunyi. Pembayaran dilakukan melalui saldo dompet resmi Pijat.In.\n\n3. LOKASI & LINGKUNGAN LAYANAN\nPelanggan wajib menyediakan tempat yang aman, higienis, dan kondusif untuk pelaksanaan terapi pijat kebugaran.\n\n4. PEMBATALAN & PENGEMBALIAN DANA\nPembatalan pesanan diatur sesuai batas waktu yang berlaku. Pembatalan mendadak dapat dikenakan biaya kompensasi mitra.",
                'updated_by' => $superadmin?->id,
            ],
            [
                'type' => 'privacy_policy',
                'title' => 'Kebijakan Privasi & Perlindungan Data Pribadi',
                'version' => '1.0',
                'summary' => 'Kepatuhan terhadap UU PDP, retensi data chat/medis maksimal 30 hari, dan enkripsi data sensitif.',
                'content' => "KEBIJAKAN PRIVASI PIJAT.IN (VERSI 1.0)\n\n1. PRINSIP PERLINDUNGAN DATA PRIBADI (UU PDP)\nPijat.In berkomitmen menjaga kerahasiaan seluruh data identitas, nomor telepon, dan lokasi pengguna. NIK dan nomor rekening bank disimpan menggunakan enkripsi tingkat tinggi.\n\n2. RETENSI CATATAN MEDIS & PERCAKAPAN\nSesuai standar privasi, seluruh riwayat percakapan in-app chat dan catatan kondisi medis penerima layanan akan dibersihkan/dihapus secara otomatis oleh sistem setelah 30 hari kalender sejak pesanan selesai.\n\n3. PENGGUNAAN LOKASI GEOGRAFIS\nTitik koordinat GPS hanya dikumpulkan saat proses pencarian dan pelacakan pesanan berlangsung demi akurasi penugasan.",
                'updated_by' => $superadmin?->id,
            ],
            [
                'type' => 'risk_statement',
                'title' => 'Pernyataan Risiko & Kelaikan Kesehatan Fisik',
                'version' => '1.0',
                'summary' => 'Penafian medis, kontraindikasi pijat, dan persetujuan kondisi fisik sebelum sesi dimulai.',
                'content' => "PERNYATAAN RISIKO & KELAIKAN KESEHATAN FISIK (VERSI 1.0)\n\n1. PENAFIAN MEDIS\nLayanan pijat Pijat.In adalah terapi kebugaran relaksasi tradisional, bukan tindakan medis kuratif atau pengganti pengobatan dokter.\n\n2. KONTRAINDIKASI\nLayanan tidak disarankan untuk pelanggan dengan demam tinggi, patah tulang akut, luka terbuka luas, infeksi kulit menular, atau penyakit kardiovaskular berat tanpa persetujuan dokter.\n\n3. TANGGUNG JAWAB PENGGUNA\nPelanggan wajib memberikan informasi kondisi fisik/medis yang jujur pada form pemesanan demi keselamatan bersama.",
                'updated_by' => $superadmin?->id,
            ],
        ];

        foreach ($documents as $doc) {
            LegalDocument::updateOrCreate(
                ['type' => $doc['type']],
                $doc
            );
        }
    }
}
