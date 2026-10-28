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
| **S3** | Customer, Alamat Utama, Katalog, & Dompet Top-up | Sedang Berjalan (ADM-10, CUS-03, CUS-05, CUS-23 Selesai) | 27 Okt 2026 | - | 24/24 tests pass (ServiceCatalogTest + CustomerAddressTest + CustomerCatalogTest), 79/79 total suite pass, Pint clean; CRUD Katalog Layanan Admin & PWA Customer tanpa add-on selesai; Manajemen Alamat Customer PWA, pin peta Leaflet, validasi spasial ST_Contains wilayah aktif, alamat pertama otomatis jadi alamat utama, dan perpindahan domisili instan selesai |
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
- **Slice**: `S3` (Porsi Customer: `CUS-03` Katalog Layanan PWA, `CUS-05` Manajemen Alamat, & `CUS-23` Perpindahan Domisili) $\rightarrow$ **Selesai**.
- **Hasil Sesi Ini**:
  1. **Katalog Layanan PWA Customer (`CUS-03`)**:
     - Controller `CustomerDashboardController` & `CatalogController` (`index`, `show`).
     - Tampilan Katalog PWA Pelanggan (`resources/views/pelanggan/catalog/index.blade.php` dan `show.blade.php`): Daftar layanan aktif, pilihan durasi paket 60/90/120 menit dengan tarif flat tanpa biaya transportasi tambahan dan tanpa opsi add-on (sesuai DEC-01).
     - Dashboard Pelanggan dinamis (`resources/views/pelanggan/dashboard.blade.php`) terhubung ke database katalog layanan aktif.
     - 5/5 Feature tests di `tests/Feature/Pelanggan/CustomerCatalogTest.php` lulus 100%.
  2. **Manajemen Alamat & Perpindahan Domisili (`CUS-05`, `CUS-23`)**:
     - Migrasi `users.region_id` dan tabel `customer_addresses` dengan tipe spasial `Point` SRID 4326 dan spatial index.
     - Model `CustomerAddress` dan relasi ke `User` dan `Region`.
     - `CustomerAddressService`: Validasi koordinat spasial terhadap wilayah aktif (`ST_Contains`), otomatisasi penandaan alamat pertama sebagai alamat utama (`is_primary`), dan mekanisme perpindahan domisili akun customer seketika saat mengganti alamat utama (`CUS-23`).
     - Antarmuka PWA Pelanggan (`resources/views/pelanggan/addresses/`): Daftar alamat tersimpan, form tambah/ubah dengan peta Leaflet interaktif, pencarian chip kota operasional.
     - 8/8 Feature tests di `tests/Feature/Pelanggan/CustomerAddressTest.php` lulus 100%.
  3. Seluruh 79 test suite aplikasi berstatus hijau (pass), format kode bersih via Laravel Pint.

---

## 4. Daftar Keputusan Arsitektur Terkini (Decisions Log)
- **[DEC-01] Penegasan Add-on Katalog**: Sesuai konfirmasi pengguna, fitur add-on ditiadakan sepenuhnya pada versi 2. Katalog layanan hanya memuat jenis layanan, pilihan durasi, dan harga tetap.
- **[DEC-02] Solusi Atribusi Wilayah Dompet (B.2)**: Customer diwajibkan menyimpan minimal 1 alamat (Alamat Utama) sebelum dapat melakukan top-up dompet. Tidak ada transaksi tanpa `region_id`. Laporan Keuangan Admin Wilayah (ADM-11) memuat mutasi arus kas berdasarkan `region_id` permanen, sedangkan Super Admin (SA-04) memantau saldo mengendap berdasarkan domisili terkini customer dan settlement global Xendit.
- **[DEC-03] Pengecualian Sanksi SP Mitra**: Pembatalan pesanan karena alasan kondisi medis atau ketidakcocokan gender penerima ditegaskan bebas sanksi SP bagi mitra.
- **[DEC-04] Otentikasi Admin Sederhana**: Menggunakan email & password standar dengan pembatasan percobaan login (rate limiting) tanpa sistem 2FA kompleks.
- **[DEC-05] Tema Visual Dominan Hijau-Putih**: Sesuai arahan pengguna, antarmuka visual (PWA Customer/Mitra dan Web Admin) menggunakan palet dominan hijau dan putih (Emerald/Forest Green & Clean White) bernuansa segar, bersih, higienis, dan menenangkan khas layanan wellness/pijat profesional.
- **[DEC-06] Preset Master Wilayah 514 Kota/Kabupaten Indonesia**: Batas wilayah resmi BPS/BIG untuk seluruh 514 kabupaten dan kota di 34+ provinsi di Indonesia diintegrasikan langsung ke dalam sistem (`storage/app/geojson/`). Superadmin dapat memilih wilayah via dropdown bertingkat (Provinsi $\rightarrow$ Kota) atau fitur pencarian cepat tanpa perlu menggambar poligon manual. Kolom spasial MySQL `boundaries` mendukung tipe geometri fleksibel `Polygon` & `MultiPolygon` SRID 4326 dengan sinkronisasi otomatis zona waktu (`WIB`, `WITA`, `WIT`).
- **[DEC-07] Unifikasi Admin Bertugas & Admin Siaga**: Sesuai arahan pengguna, peran "Admin Bertugas" dan "Admin Siaga" disatukan menjadi 1 entitas: **Admin Wilayah**. Setiap admin yang ditugaskan ke suatu wilayah otomatis memegang tanggung jawab penuh operasional dan penanganan darurat di wilayah tersebut. Syarat aktivasi wilayah disederhanakan menjadi **minimal 1 Admin Bertugas**, dan antarmuka Super Admin dibersihkan dari toggle ganda yang membingungkan.
- **[DEC-08] Pemisahan Tampilan Katalog Aktif & Nonaktif**: Halaman katalog memisahkan data layanan aktif (tersedia dipesan) dan nonaktif (diarsipkan/ditutup sementara). Navigasi dilakukan melalui stat card interaktif dan tombol redirect cepat (`Lihat Katalog Nonaktif` dan `Kembali ke Katalog Aktif`) dengan badge penghitung layanan secara real-time.
- **[DEC-09] Modal Dialog Konfirmasi Interaktif Global (`<x-confirm-dialog />`)**: Menggantikan `window.confirm()` browser bawaan dengan modal dialog Alpine.js custom yang elegan dan konsisten. Desain terpusat (*centered circular icon with soft ring accent*), teks *to-the-point* tanpa badge berulang, serta mendukung varian kondisi `danger` (merah), `warning` (kuning/amber), dan `info/success` (hijau). Diterapkan pada Hapus Layanan, Nonaktifkan Layanan, Nonaktifkan Paket Durasi, Cabut Wilayah Penugasan Admin, dan Nonaktifkan Wilayah Layanan.
- **[DEC-10] Standarisasi Header Bersih & Filter Wilayah Tabel**: Menghapus teks teknis modul (seperti `Modul Verifikasi Mitra (ADM-02)`) dan banner duplikasi data pada header dashboard admin. Filter multi-wilayah dipindahkan langsung ke header card tabel masing-masing agar relevan dan ergonomis.

---

## 5. Hambatan & Blokir (Blockers)
*Tidak ada pemblokir saat ini.*

---

## 6. Hal yang Perlu Diperhatikan Sesi Berikutnya
- Porsi **Manajemen Alamat (`CUS-05`, `CUS-23`)**, **Katalog Layanan PWA Pelanggan (`CUS-03`)**, dan **Katalog Layanan Admin (`ADM-10`)** telah selesai dengan 79/79 test berstatus hijau.
- Modul S3 berikutnya yang siap dikerjakan:
  1. **CUS-16 / SYS-05 (Dompet Pelanggan & Transaksi Ledger Atomik)**: Tabel dompet, ledger mutasi saldo, proteksi saldo negatif, dan prasyarat kepemilikan Alamat Utama sebelum top-up (DEC-02).
  2. **CUS-09 / SYS-05 (Integrasi Top-up & Simulasi Webhook Xendit)**: Form top-up saldo di PWA, invoice pending maks 15 menit, verifikasi signature webhook, dan penambahan saldo otomatis.

