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
| **S3** | Customer, Alamat Utama, Katalog, & Dompet Top-up | Selesai | 27 Okt 2026 | 28 Okt 2026 | 25/25 tests pass (ServiceCatalogTest, CustomerAddressTest, CustomerCatalogTest, CustomerWalletTest), 100/100 total suite pass, Pint clean; CRUD Katalog Layanan Admin & PWA Customer tanpa add-on selesai; Manajemen Alamat Customer PWA, pin peta Leaflet, validasi spasial ST_Contains wilayah aktif, alamat pertama otomatis jadi alamat utama, dan perpindahan domisili instan selesai; Skema Dompet, mutasi transaksi ledger atomik, pencegahan saldo negatif, top-up mandiri teratribusi wilayah (DEC-02), dan webhook Xendit selesai |
| **S4** | Pemesanan Terjadwal, Matching FCFS, & Jadwal Mitra | Selesai | 28 Okt 2026 | 29 Okt 2026 | 23/23 tests pass (OrderBookingTest: 10/10, PartnerServicePreferenceTest: 4/4, ScheduledOrderMatchingTest: 6/6, OrderTrackingTest: 3/3), 123/123 total suite pass, Pint clean; CUS-04, CUS-06, CUS-07, SYS-08, MIT-08, MIT-06, MIT-07, CUS-10 Selesai Penuh |
| **S5** | Pemesanan Instan, Penawaran Sekuensial 60s, & Lock | Selesai | 29 Okt 2026 | 29 Okt 2026 | 6/6 tests pass (InstantOrderMatchingTest), 131/131 total suite pass, Pint clean; CUS-08, MIT-04, MIT-05, SYS-01 Selesai Penuh |
| **S6** | Pelaksanaan Layanan, Tracking OTW, Tiba 50m, Selesai | Belum Dimulai | - | - | Tergantung S2, S4, S5 |
| **S7** | Pembatalan, Reschedule, Refund, & Potongan Kompensasi| Belum Dimulai | - | - | Tergantung S6 |
| **S8** | Aduan Wilayah, Poin Sanksi, Banding, & Resolusi Masalah | Selesai (Backoffice) | 30 Okt 2026 | 31 Okt 2026 | ADM-08, ADM-07, ADM-06 Selesai Penuh; ADM-09 dikonsolidasikan ke Manajemen Mitra (S11) per DEC-13; Panic Button ditiadakan per DEC-11 |
| **S9** | Saldo Mitra 24 Jam, Penarikan Dana, Keuangan Wilayah | Belum Dimulai | - | - | Tergantung S7, S8 |
| **S10**| Perpindahan Domisili Customer & Mitra Antarwilayah | Belum Dimulai | - | - | Tergantung S2, S3 |
| **S11**| Manajemen Pengguna, Audit, & Pelengkap Sistem | Sedang Berjalan (Backoffice) | 31 Okt 2026 | - | ADM-05 Selesai Penuh (10/10 tests pass, 198/198 total suite pass, Pint clean); Integrasi metrik rating mitra & ulasan pelanggan terpadu, moderasi akun (aktifkan/suspend/blokir) & tabel blacklists |

---

### 3. Slice Aktif Saat Ini

- **Slice**: `S4` (Pemesanan Terjadwal, Matching FCFS, & Jadwal Mitra) $\rightarrow$ **Selesai Penuh (100%)**.
- **Hasil Sesi Ini**:
  1. **Preferensi Layanan Mitra (`MIT-08`)**:
     - Tabel `partner_service_preferences` dengan relasi ke `partner_profiles` dan `services`.
     - Antarmuka preferensi jenis layanan di Profil Mitra (`resources/views/mitra/profile.blade.php`).
     - Default: Seluruh layanan aktif secara otomatis.
     - Validasi: Minimal 1 jenis layanan harus tetap aktif (dicegah menonaktifkan semua layanan).
     - Filter bursa pesanan terintegrasi dengan preferensi layanan aktif mitra.
  2. **Bursa Pesanan Terjadwal & Pengambilan FCFS (`MIT-06`)**:
     - Rute `mitra.orders.scheduled` dan tampilan `resources/views/mitra/orders/scheduled.blade.php`.
     - Filter otomatis: Wilayah operasional sama, sesama jenis kelamin mitra-penerima, jenis layanan aktif, dan bebas bentrok jadwal.
     - Pengambilan pesanan berbasis FCFS (*First Come, First Served*) dengan database transaction lock (`lockForUpdate`) untuk mencegah perebutan pesanan (race conditions).
     - Notifikasi kesalahan jika pesanan telah terisi oleh mitra lain.
  3. **Menu Jadwal Saya & Dekripsi Catatan Medis (`MIT-07`)**:
     - Rute `mitra.orders.my-schedule` dan tampilan `resources/views/mitra/orders/my-schedule.blade.php`.
     - Menampilkan seluruh pesanan berstatus Dijadwalkan yang menjadi tanggung jawab mitra, lengkap dengan waktu jadwal, nama, kontak, dan alamat.
     - Catatan kondisi medis penerima terbuka dan dapat dibaca mitra di layar ini.
  4. **Pelacakan Pesanan Real-Time Pelanggan (`CUS-10`)**:
     - Tampilan pelacakan pesanan di `resources/views/pelanggan/orders/show.blade.php`.
     - Hitung mundur batas waktu pencarian terapis saat status `MencariMitra` (30 menit countdown).
     - Profil terapis penanggung jawab (nama panggilan, inisial, rating 5.0, jumlah layanan selesai, kontak telepon/WA) tampil saat `Dijadwalkan` / `MenujuLokasi` tanpa membocorkan NIK atau data sensitif.
     - Banner status OTW, estimasi kedatangan (~15-20 menit), dan indikator pembaruan posisi GPS setiap 30 detik.
  5. **Pengujian & Kualitas Kode**:
     - 23/23 tests pass untuk modul Slice 4 (`OrderBookingTest`, `PartnerServicePreferenceTest`, `ScheduledOrderMatchingTest`, `OrderTrackingTest`).
     - **123/123 total test suite aplikasi berstatus hijau (pass)** dengan 528 assertions.
     - Format kode bersih dan rapi via Laravel Pint.

- **Slice**: `ADM-04` & `ADM-05` (Pemantauan Pesanan Wilayah & Penanganan Order Nyangkut / Stuck) $\rightarrow$ **Selesai Penuh**.
- **Hasil Sesi Ini**:
  1. **Enums & Skema Database**: Dibuat `OrderStatus` (9 state alur pesanan), `OrderType` (Instan, Terjadwal), tabel `orders` dengan koordinat spasial MySQL `SRID 4326`, serta `order_status_histories`.
  2. **Model Eloquent & Scoping**: Scope wilayah (`inRegions`), scope aktif (`active`), deteksi order macet (`stuck`/`isStuck` >30m instan atau <2 jam terjadwal tanpa mitra), dan relasi linimasa status.
  3. **Antarmuka Pemantauan Pesanan (`admin.orders.index`)**:
     - Filter tab status: *Semua*, *Aktif*, *Macet* (dengan ping indicator animasi), *Selesai*, dan *Dibatalkan*.
     - Header kartu tabel terintegrasi filter multi-wilayah tugas admin dan kotak pencarian real-time (kode pesanan, nama pemesan, penerima, dan mitra).
  4. **Antarmuka Rincian & Linimasa Pesanan (`admin.orders.show`)**:
     - Rincian layanan & finansial, mitra penanggung jawab, pemesan, penerima, dan alamat layanan.
     - Linimasa riwayat status (*status history timeline*) kronologis lengkap dengan identitas aktor (Customer/Mitra/Admin/Sistem) dan catatan.
     - Modal intervensi pembatalan admin dengan alasan wajib diisi (min 5 karakter) dan garansi alokasi refund 100% pelanggan.
     - **Kepatuhan Ketat BRD 5.3**: Tidak ada tombol penugasan manual mitra (matching sepenuhnya otomatis oleh sistem).
  5. **Pengujian Menyeluruh (Pest)**:
     - 15/15 Feature tests di `tests/Feature/Admin/OrderMonitoringTest.php` lulus 100%.
     - **80/80 total test suite aplikasi berstatus hijau (pass)** dengan 296 assertions.
     - Format kode bersih dan rapi via Laravel Pint.

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


- **Slice**: `S8 - Batch 2` (**Pusat Laporan Aduan Pelanggan & Mitra**: `ADM-08` + Master Kategori Super Admin) $\rightarrow$ **Selesai**.
- **Slice**: `S8 - Batch 3 & 4` (**Sistem Poin Sanksi, Monitoring Akun, Peninjauan Banding `ADM-07`, & Penebusan Bintang 5**) $\rightarrow$ **Selesai Penuh**.
- **Hasil Sesi Ini**:
  1. **Transformasi Sistem Poin Sanksi & Penebusan Performa ([DEC-12])**:
     - Menggantikan sistem manual SP dengan akumulasi poin sanksi dinamis. Bobot poin diatur pada master kategori aduan Super Admin (15 - 50 poin), dengan opsi input manual pada kategori "Keluhan Lainnya".
     - Penegakan sanksi otomatis berdasarkan ambang batas (*threshold*):
       - 30 Poin: Peringatan tertulis sistem
       - 60 Poin: Pembekuan sementara (*suspend*) 3 hari kerja
       - 100 Poin: Pembekuan sementara (*suspend*) 7 hari kerja
       - 150 Poin: Pemblokiran akun permanen (*banned*)
     - Buku besar audit trail mutasi poin lengkap pada tabel `penalty_ledgers`.
  2. **Monitoring Panel Poin Sanksi & Akun Pengguna (`ADM-06`)**:
     - Controller `PenaltyMonitoringController` dengan scoping wilayah tugas admin.
     - Metrik ringkasan 1x4, tab filter status (*Semua*, *Peringatan*, *Tersuspend*, *Diblokir*), filter peran, pencarian cepat, dan detail audit pengguna dengan modal penyesuaian poin manual.
  3. **Peninjauan Berkas Banding Sanksi (`ADM-07`)**:
     - Migrasi dan model `penalty_appeals` dengan relasi ke pengguna, wilayah, kasus aduan, dan peninjau.
     - Controller `PenaltyAppealController` (`index`, `show`, `approve`, `reject`).
     - Alur persetujuan banding: Mengembalikan poin pengguna via `PenaltyService::reverseAppeal()`, memulihkan status akun dari pembekuan jika poin kembali di bawah ambang batas sanksi, dan mencatat log audit lengkap.
  4. **Penebusan Poin Otomatis Berbasis Performa (Rating Bintang 5)**:
     - Migrasi dan model `order_ratings`.
     - `OrderRatingService`: Setiap pesanan yang diselesaikan dan diberi rating 5 bintang oleh pelanggan secara otomatis mengurangi 1 poin sanksi mitra (hingga batas bawah 0).
  5. **Pengujian & Kualitas Kode**:
     - Seluruh 134/134 test suite aplikasi berstatus hijau (pass) dengan 553 assertions.
     - Kode terformat bersih via Laravel Pint dan build frontend sukses.

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

- **[DEC-11] Penghapusan Fitur Siaga Darurat (Panic Button / SOS)**: Sesuai arahan pengguna, modul Siaga Darurat (`ADM-13` dan elemen darurat `SA-07`) dihapus. Penanganan keluhan, komplain kualitas, maupun laporan kendala di lapangan dipusatkan melalui modul Pusat Laporan Aduan Pelanggan & Mitra (`ADM-08`).
- **[DEC-12] Sistem Demerit Points & Penebusan Performa Otomatis**: Menggantikan sistem manual Surat Peringatan (SP 1, SP 2, SP 3) dengan akumulasi poin sanksi otomatis terukur (Ambang: 30 = Peringatan, 60 = Suspend 3 Hari, 100 = Suspend 7 Hari, 150 = Banned Permanen). Mitra dapat menebus poin pelanggaran secara adil melalui performa kerja nyata (setiap menyelesaikan pesanan dengan rating 5 bintang memotong 1 poin sanksi). Tersedia mekanisme pengajuan banding (`ADM-07`) dengan pengunggahan surat pernyataan yang ditinjau oleh Admin Wilayah.
- **[DEC-13] Konsolidasi Metrik Rating ke Manajemen Mitra (ADM-05 di S11)**: Sesuai arahan pengguna, menu 'Tinjauan Rating' tersendiri ditiadakan. Mitra berating rendah secara alami telah terfilter oleh preferensi customer serta algoritma matching pesanan instan/terjadwal. Pelanggaran nyata mitra telah terakomodasi secara adil melalui Pusat Aduan (`ADM-08`) dan Sistem Poin Sanksi (`ADM-06`). Seluruh riwayat dan performa rating mitra akan digabungkan pada halaman profil/detail mitra di modul Manajemen Mitra (`ADM-05` pada S11).

- **[DEC-11] Kebebasan Memilih Preferensi Gender Terapis (Opsi A)**: Sesuai kesepakatan produk terbaru, aturan restriksi kaku sesama jenis kelamin (*rigid same-gender lock*) dihapus. Pelanggan kini diberikan kebebasan penuh saat membuat pesanan (`orders/create`) untuk memilih preferensi terapis yang diinginkan (`preferred_gender`):
  1. `pria` (Khusus Terapis Pria)
  2. `wanita` (Khusus Terapis Wanita)
  3. `bebas` (Siapa Saja / Terapis Terdekat yang Tersedia - Paling Cepat)
  Pencarian instan (`InstantOrderMatchingService`) dan bursa pesanan terjadwal (`MitraOrderController@scheduled`) sepenuhnya menyaring kandidat mitra berdasarkan preferensi pelanggan ini, sehingga mitra pria maupun wanita dapat menerima pesanan pelanggan lintas gender jika pelanggan memilih opsi `bebas` atau gender yang bersangkutan.


---

## 5. Hambatan & Blokir (Blockers)
*Tidak ada pemblokir saat ini.*

---

## 6. Hal yang Perlu Diperhatikan Sesi Berikutnya
- **Slice 4 (S4)** & **Slice 5 (S5)** telah selesai sepenuhnya dengan integrasi preferensi gender Opsi A. Total 134/134 test suite aplikasi berstatus hijau (pass) dengan 588 assertions.
- Modul operasional berikutnya yang siap dikerjakan:
  1. **Slice 6 (S6)**: Pelaksanaan Layanan, Tracking OTW, Tiba 50m, Mulai Sesi, & Selesai Layanan (`CUS-11`, `MIT-10`, `MIT-11`, `MIT-12`).

