# Catatan Progres & Status Pengerjaan (docs/progress.md)

Dokumen ini adalah *live document* yang wajib diperbarui pada setiap akhir sesi kerja agen dan dibaca pada setiap awal sesi sebelum memulai pekerjaan.

---

## 1. Ritual Awal Sesi (Wajib Dijalankan Agen)
1. **Baca Progres**: Periksa bagian **Slice Aktif** dan **Hal yang Perlu Diperhatikan Sesi Ini** di bawah.
2. **Periksa Kriteria**: Buka `features.json` dan temukan ID kebutuhan yang termasuk ke dalam slice aktif. Pastikan memahami kriteria penerimaan dan skenario verifikasinya.
3. **Pemeriksaan Lingkungan**:
   - Jalankan `git status` untuk memastikan repositori dalam kondisi bersih.
   - Jalankan `php artisan test --compact` untuk memastikan seluruh tes unit/fitur yang sudah ada berstatus hijau (*pass*).

---

## 2. Status Slice Saat Ini

| Slice ID | Nama Slice / Alur | Status | Tanggal Mulai | Tanggal Selesai | Catatan Verifikasi |
|---|---|---|---|---|---|
| **F0** | Fondasi Arsitektur, Kontrak, & Skema Inti | Selesai | 27 Okt 2026 | 27 Okt 2026 | Seluruh kontrak dibekukan, CLAUDE.md dan features.json dibuat |
| **S1** | Setup Master Wilayah & Penugasan Admin | Siap Dikerjakan | - | - | Menunggu giliran eksekusi |
| **S2** | Onboarding, Verifikasi Dokumen, & Profil Mitra | Belum Dimulai | - | - | Tergantung S1 |
| **S3** | Customer, Alamat Utama, Katalog, & Dompet Top-up | Belum Dimulai | - | - | Tergantung S1 |
| **S4** | Pemesanan Terjadwal, Matching FCFS, & Jadwal Mitra | Belum Dimulai | - | - | Tergantung S2, S3 |
| **S5** | Pemesanan Instan, Penawaran Sekuensial 60s, & Lock | Belum Dimulai | - | - | Tergantung S2, S3 |
| **S6** | Pelaksanaan Layanan, Tracking OTW, Tiba 50m, Selesai | Belum Dimulai | - | - | Tergantung S4, S5 |
| **S7** | Pembatalan, Reschedule, Refund, & Potongan Kompensasi| Belum Dimulai | - | - | Tergantung S6 |
| **S8** | Tombol Darurat, Aduan 24 Jam, SP Bertingkat, Banding | Belum Dimulai | - | - | Tergantung S6, S7 |
| **S9** | Saldo Mitra 24 Jam, Penarikan Dana, Keuangan Wilayah | Belum Dimulai | - | - | Tergantung S7, S8 |
| **S10**| Perpindahan Domisili Customer & Mitra Antarwilayah | Belum Dimulai | - | - | Tergantung S2, S3 |
| **S11**| Pelengkap: Retensi Data 30 Hari, Anonymization, Audit | Belum Dimulai | - | - | Tergantung S8, S9 |

---

## 3. Slice Aktif Saat Ini
- **Slice**: `F0` (Fondasi Sistem & Kontrak Arsitektur)
- **Tujuan**: Menyiapkan seluruh kontrak arsitektur bersama yang dibekukan, aturan otorisasi server, model data fondasi, dan daftar kebutuhan `features.json`.

---

## 4. Daftar Keputusan Arsitektur Terkini (Decisions Log)
- **[DEC-01] Penegasan Add-on Katalog**: Sesuai konfirmasi pengguna, fitur add-on ditiadakan sepenuhnya pada versi 2. Katalog layanan hanya memuat jenis layanan, pilihan durasi, dan harga tetap.
- **[DEC-02] Solusi Atribusi Wilayah Dompet (B.2)**: Customer diwajibkan menyimpan minimal 1 alamat (Alamat Utama) sebelum dapat melakukan top-up dompet. Tidak ada transaksi tanpa `region_id`. Laporan Keuangan Admin Wilayah (ADM-11) memuat mutasi arus kas berdasarkan `region_id` permanen, sedangkan Super Admin (SA-04) memantau saldo mengendap berdasarkan domisili terkini customer dan settlement global Xendit.
- **[DEC-03] Pengecualian Sanksi SP Mitra**: Pembatalan pesanan karena alasan kondisi medis atau ketidakcocokan gender penerima ditegaskan bebas sanksi SP bagi mitra.
- **[DEC-04] Otentikasi Admin Sederhana**: Menggunakan email & password standar dengan pembatasan percobaan login (rate limiting) tanpa sistem 2FA kompleks.

---

## 5. Hambatan & Blokir (Blockers)
*Tidak ada pemblokir saat ini. Fondasi kontrak telah selesai dibekukan.*

---

## 6. Hal yang Perlu Diperhatikan Sesi Berikutnya
- Saat mengerjakan **S1 (Setup Master Wilayah & Penugasan Admin)**:
  - Pastikan ekstensi spasial MySQL berfungsi normal (`ST_Contains`, `ST_Distance_Sphere`, SRID 4326).
  - Terapkan middleware otorisasi server-side `VerifyAdminRegionAccess` agar data admin terisolasi sejak awal.
