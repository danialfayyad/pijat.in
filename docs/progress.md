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
| **S1** | Setup Master Wilayah & Penugasan Admin | Selesai | 27 Okt 2026 | 27 Okt 2026 | 10/10 tests pass (RegionAndAdminScopingTest), 41/41 total suite pass, Pint clean, 514 Kota/Kabupaten GeoJSON preset terintegrasi |
| **S2** | Onboarding, Verifikasi Dokumen, & Profil Mitra | Selesai | 27 Okt 2026 | 27 Okt 2026 | 11/11 tests pass (PartnerOnboardingAndVerificationTest), 53/53 total suite pass, Pint clean, registrasi NIK/KTP/selfie, pin domisili Leaflet di wilayah aktif, antrean verifikasi admin wilayah, proteksi rekening & safeguard re-verifikasi selesai |
| **S3** | Customer, Alamat Utama, Katalog, & Dompet Top-up | Siap Dikerjakan | - | - | Tergantung S1 (Prasyarat S1 selesai) |
| **S4** | Pemesanan Terjadwal, Matching FCFS, & Jadwal Mitra | Belum Dimulai | - | - | Tergantung S2, S3 |
| **S5** | Pemesanan Instan, Penawaran Sekuensial 60s, & Lock | Belum Dimulai | - | - | Tergantung S2, S3 |
| **S6** | Pelaksanaan Layanan, Tracking OTW, Tiba 50m, Selesai | Belum Dimulai | - | - | Tergantung S2, S4, S5 |
| **S7** | Pembatalan, Reschedule, Refund, & Potongan Kompensasi| Belum Dimulai | - | - | Tergantung S6 |
| **S8** | Tombol Darurat, Aduan 24 Jam, SP Bertingkat, Banding | Belum Dimulai | - | - | Tergantung S6, S7 |
| **S9** | Saldo Mitra 24 Jam, Penarikan Dana, Keuangan Wilayah | Belum Dimulai | - | - | Tergantung S7, S8 |
| **S10**| Perpindahan Domisili Customer & Mitra Antarwilayah | Belum Dimulai | - | - | Tergantung S2, S3 |
| **S11**| Pelengkap: Retensi Data 30 Hari, Anonymization, Audit | Belum Dimulai | - | - | Tergantung S8, S9 |

---

## 3. Slice Aktif Saat Ini
- **Slice**: `S2` Selesai $\rightarrow$ Siap lanjut ke `S3` (Customer, Alamat Utama, Katalog Layanan, & Dompet Top-up)
- **Tujuan Sesi Ini**: Menyelesaikan onboarding mitra lengkap (NIK, KTP, selfie, rekening, SOP, pin domisili di wilayah aktif), antrean verifikasi berkas per wilayah admin, aksi setujui/tolak dengan alasan baku, dashboard mitra (toggle aktif/istirahat), dan profil mitra dengan safeguard verifikasi ulang.

---

## 4. Daftar Keputusan Arsitektur Terkini (Decisions Log)
- **[DEC-01] Penegasan Add-on Katalog**: Sesuai konfirmasi pengguna, fitur add-on ditiadakan sepenuhnya pada versi 2. Katalog layanan hanya memuat jenis layanan, pilihan durasi, dan harga tetap.
- **[DEC-02] Solusi Atribusi Wilayah Dompet (B.2)**: Customer diwajibkan menyimpan minimal 1 alamat (Alamat Utama) sebelum dapat melakukan top-up dompet. Tidak ada transaksi tanpa `region_id`. Laporan Keuangan Admin Wilayah (ADM-11) memuat mutasi arus kas berdasarkan `region_id` permanen, sedangkan Super Admin (SA-04) memantau saldo mengendap berdasarkan domisili terkini customer dan settlement global Xendit.
- **[DEC-03] Pengecualian Sanksi SP Mitra**: Pembatalan pesanan karena alasan kondisi medis atau ketidakcocokan gender penerima ditegaskan bebas sanksi SP bagi mitra.
- **[DEC-04] Otentikasi Admin Sederhana**: Menggunakan email & password standar dengan pembatasan percobaan login (rate limiting) tanpa sistem 2FA kompleks.
- **[DEC-05] Tema Visual Dominan Hijau-Putih**: Sesuai arahan pengguna, antarmuka visual (PWA Customer/Mitra dan Web Admin) menggunakan palet dominan hijau dan putih (Emerald/Forest Green & Clean White) bernuansa segar, bersih, higienis, dan menenangkan khas layanan wellness/pijat profesional.
- **[DEC-06] Preset Master Wilayah 514 Kota/Kabupaten Indonesia**: Batas wilayah resmi BPS/BIG untuk seluruh 514 kabupaten dan kota di 34+ provinsi di Indonesia diintegrasikan langsung ke dalam sistem (`storage/app/geojson/`). Superadmin dapat memilih wilayah via dropdown bertingkat (Provinsi $\rightarrow$ Kota) atau fitur pencarian cepat tanpa perlu menggambar poligon manual. Kolom spasial MySQL `boundaries` mendukung tipe geometri fleksibel `Polygon` & `MultiPolygon` SRID 4326 dengan sinkronisasi otomatis zona waktu (`WIB`, `WITA`, `WIT`).
- **[DEC-07] Unifikasi Admin Bertugas & Admin Siaga**: Sesuai arahan pengguna, peran "Admin Bertugas" dan "Admin Siaga" disatukan menjadi 1 entitas: **Admin Wilayah**. Setiap admin yang ditugaskan ke suatu wilayah otomatis memegang tanggung jawab penuh operasional dan penanganan darurat di wilayah tersebut. Syarat aktivasi wilayah disederhanakan menjadi **minimal 1 Admin Bertugas**, dan antarmuka Super Admin dibersihkan dari toggle ganda yang membingungkan.

---

## 5. Hambatan & Blokir (Blockers)
*Tidak ada pemblokir saat ini.*

---

## 6. Hal yang Perlu Diperhatikan Sesi Berikutnya
- Pilihan pengerjaan slice berikutnya:
  - **S2 (Onboarding, Verifikasi Dokumen, & Profil Mitra)**: PWA Mitra, registrasi NIK/KTP/SKCK, verifikasi admin wilayah, dan toggle status aktif/istirahat.
  - ATAU **S3 (Customer, Alamat Utama, Katalog Layanan, & Dompet Top-up)**: PWA Customer, Alamat Utama via reverse geocoding/Leaflet, katalog layanan tanpa add-on, dan ledger dompet top-up.
