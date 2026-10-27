# Laporan Cakupan Kebutuhan & Verifikasi Keterlacakan (Coverage Report)

Dokumen ini membuktikan cakupan penuh (*100% trace coverage*) seluruh kebutuhan fungsional (81 ID), alur proses Bagian 6, dan kebutuhan non-fungsional Bagian 7 terhadap rancangan slice implementasi berdasarkan **BRD Pijat In Versi 2.2**.

---

## 1. Bukti Kuantitatif Integritas Kebutuhan Fungsional

- **Total Kebutuhan Fungsional BRD**: **81 ID** (CUS: 23, MIT: 22, ADM: 16, SA: 8, SYS: 12).
- **Jumlah ID Terdaftar di `features.json`**: **81 ID**.
- **Jumlah ID Tanpa Slice**: **0 ID** (Nol).
- **Jumlah ID di Lebih dari Satu Slice**: **0 ID** (Nol, tepat satu slice per ID).

### Distribusi Alokasi per Slice
| Kode Slice | Nama Slice / Alur | Jumlah ID | Daftar ID Kebutuhan |
|---|---|---|---|
| **F0** | Fondasi Arsitektur, Kontrak, & Skema Inti | *Basis* | Dibekukan untuk seluruh 81 ID |
| **S1** | Setup Master Wilayah & Penugasan Admin | 7 | `ADM-01`, `ADM-12`, `ADM-15`, `SA-02`, `SA-06`, `SYS-10`, `SYS-12` |
| **S2** | Onboarding, Verifikasi Dokumen, & Profil Mitra | 6 | `MIT-01`, `MIT-02`, `MIT-03`, `MIT-20`, `ADM-02`, `ADM-03` |
| **S3** | Customer, Alamat Utama, Katalog, & Dompet | 10 | `CUS-01`, `CUS-02`, `CUS-03`, `CUS-05`, `CUS-09`, `CUS-16`, `CUS-22`, `CUS-23`, `ADM-10`, `SYS-05` |
| **S4** | Pemesanan Terjadwal & Matching FCFS | 8 | `CUS-04`, `CUS-06`, `CUS-07`, `CUS-10`, `MIT-06`, `MIT-07`, `MIT-08`, `SYS-08` |
| **S5** | Pemesanan Instan & Penawaran Sekuensial 60s | 4 | `CUS-08`, `MIT-04`, `MIT-05`, `SYS-01` |
| **S6** | Pelaksanaan Layanan, Chat, & Rating | 9 | `CUS-11`, `CUS-14`, `CUS-15`, `MIT-09`, `MIT-10`, `MIT-13`, `MIT-14`, `MIT-17`, `SYS-09` |
| **S7** | Pembatalan, Reschedule, & Refund | 6 | `CUS-12`, `CUS-13`, `MIT-11`, `MIT-12`, `ADM-04`, `SYS-02` |
| **S8** | Tombol Darurat, Aduan 24 Jam, SP, & Banding | 13 | `CUS-18`, `CUS-19`, `CUS-20`, `MIT-18`, `MIT-19`, `ADM-06`, `ADM-07`, `ADM-08`, `ADM-09`, `ADM-13`, `SA-07`, `SYS-04`, `SYS-11` |
| **S9** | Saldo Mitra 24 Jam, Penarikan, & Keuangan | 7 | `CUS-17`, `MIT-15`, `MIT-16`, `ADM-11`, `SA-04`, `SA-08`, `SYS-03` |
| **S10**| Perpindahan Domisili (Relocation) Mitra | 1 | `MIT-22` |
| **S11**| Pelengkap: Retensi, Anonymize, & Log Audit | 10 | `ADM-05`, `ADM-14`, `ADM-16`, `SA-01`, `SA-03`, `SA-05`, `CUS-21`, `MIT-21`, `SYS-06`, `SYS-07` |
| **TOTAL** | | **81** | **Semua 81 ID Terpetakan Sempurna** |

---

## 2. Tabel Pemetaan Lengkap 81 ID Fungsional

| ID | Peran | Modul | Slice | Rujukan BRD | Status Alokasi |
|---|---|---|---|---|---|
| `CUS-01` | Customer | Autentikasi & Registrasi | S3 | 5.1, 4.3 | Tepat 1 Slice |
| `CUS-02` | Customer | Autentikasi & Login | S3 | 5.1 | Tepat 1 Slice |
| `CUS-03` | Customer | Katalog Layanan | S3 | 5.1, 4.2 | Tepat 1 Slice |
| `CUS-04` | Customer | Pemesanan | S4 | 5.1, 4.3 | Tepat 1 Slice |
| `CUS-05` | Customer | Manajemen Alamat | S3 | 5.1, 4.16 | Tepat 1 Slice |
| `CUS-06` | Customer | Pemesanan | S4 | 5.1, 4.4 | Tepat 1 Slice |
| `CUS-07` | Customer | Pemesanan | S4 | 5.1, 4.13 | Tepat 1 Slice |
| `CUS-08` | Customer | Pemesanan Instan | S5 | 5.1, 4.5 | Tepat 1 Slice |
| `CUS-09` | Customer | Pembayaran Pesanan | S3 | 5.1, 4.6, 4.9 | Tepat 1 Slice |
| `CUS-10` | Customer | Pelacakan Pesanan | S4 | 5.1, 4.1, 4.8 | Tepat 1 Slice |
| `CUS-11` | Customer | Chat Pesanan | S6 | 5.1, 4.15 | Tepat 1 Slice |
| `CUS-12` | Customer | Pembatalan Pesanan | S7 | 5.1, 4.7 | Tepat 1 Slice |
| `CUS-13` | Customer | Reschedule Pesanan | S7 | 5.1, 4.7 | Tepat 1 Slice |
| `CUS-14` | Customer | Rating Layanan | S6 | 5.1, 4.14 | Tepat 1 Slice |
| `CUS-15` | Customer | Riwayat & Nota | S6 | 5.1 | Tepat 1 Slice |
| `CUS-16` | Customer | Dompet Customer | S3 | 5.1, 4.9 | Tepat 1 Slice |
| `CUS-17` | Customer | Penarikan Dompet | S9 | 5.1, 4.9 | Tepat 1 Slice |
| `CUS-18` | Customer | Tombol Darurat | S8 | 5.1, 4.12, 4.16 | Tepat 1 Slice |
| `CUS-19` | Customer | Layanan Aduan | S8 | 5.1, 4.12 | Tepat 1 Slice |
| `CUS-20` | Customer | Sanksi & Banding | S8 | 5.1, 4.11 | Tepat 1 Slice |
| `CUS-21` | Customer | Notifikasi Push | S11 | 5.1, Lampiran A | Tepat 1 Slice |
| `CUS-22` | Customer | Profil & Pengaturan | S3 | 5.1, 4.15 | Tepat 1 Slice |
| `CUS-23` | Customer | Perpindahan Domisili | S3 | 5.1, 4.16, 6.7 | Tepat 1 Slice |
| `MIT-01` | Mitra | Autentikasi & Registrasi | S2 | 5.2, 4.1 | Tepat 1 Slice |
| `MIT-02` | Mitra | Kelengkapan Berkas | S2 | 5.2, 4.1, 4.16 | Tepat 1 Slice |
| `MIT-03` | Mitra | Autentikasi & Login | S2 | 5.2, 4.1 | Tepat 1 Slice |
| `MIT-04` | Mitra | Ketersediaan & Lokasi | S5 | 5.2, 4.1, 4.15 | Tepat 1 Slice |
| `MIT-05` | Mitra | Pesanan Instan | S5 | 5.2, 4.4, 4.5 | Tepat 1 Slice |
| `MIT-06` | Mitra | Pesanan Terjadwal | S4 | 5.2, 4.4, 4.5 | Tepat 1 Slice |
| `MIT-07` | Mitra | Jadwal Saya | S4 | 5.2, 4.4, 4.13 | Tepat 1 Slice |
| `MIT-08` | Mitra | Preferensi Layanan | S4 | 5.2, 4.2 | Tepat 1 Slice |
| `MIT-09` | Mitra | Pelaksanaan Layanan | S6 | 5.2, 4.6, 4.8 | Tepat 1 Slice |
| `MIT-10` | Mitra | Validasi Lokasi Tiba | S6 | 5.2, 4.8 | Tepat 1 Slice |
| `MIT-11` | Mitra | Pembatalan Khusus | S7 | 5.2, 4.3, 4.7, 4.13 | Tepat 1 Slice |
| `MIT-12` | Mitra | Pembatalan Mandiri | S7 | 5.2, 4.7, 4.11 | Tepat 1 Slice |
| `MIT-13` | Mitra | Chat dengan Customer | S6 | 5.2, 4.15 | Tepat 1 Slice |
| `MIT-14` | Mitra | Rating Customer | S6 | 5.2, 4.14 | Tepat 1 Slice |
| `MIT-15` | Mitra | Manajemen Saldo | S9 | 5.2, 4.10 | Tepat 1 Slice |
| `MIT-16` | Mitra | Penarikan Saldo | S9 | 5.2, 4.10 | Tepat 1 Slice |
| `MIT-17` | Mitra | Riwayat & Performa | S6 | 5.2, 4.11, 4.14 | Tepat 1 Slice |
| `MIT-18` | Mitra | Banding SP | S8 | 5.2, 4.11 | Tepat 1 Slice |
| `MIT-19` | Mitra | Tombol Darurat | S8 | 5.2, 4.12 | Tepat 1 Slice |
| `MIT-20` | Mitra | Profil & Pengaturan | S2 | 5.2, 4.1, 4.15 | Tepat 1 Slice |
| `MIT-21` | Mitra | Notifikasi Push | S11 | 5.2, Lampiran A | Tepat 1 Slice |
| `MIT-22` | Mitra | Perpindahan Domisili | S10 | 5.2, 4.16, 6.7 | Tepat 1 Slice |
| `ADM-01` | Admin | Dashboard Wilayah | S1 | 5.3, 4.16 | Tepat 1 Slice |
| `ADM-02` | Admin | Verifikasi Mitra | S2 | 5.3, 4.1, 4.16 | Tepat 1 Slice |
| `ADM-03` | Admin | Verifikasi Rekening | S2 | 5.3, 4.9, 4.10 | Tepat 1 Slice |
| `ADM-04` | Admin | Pemantauan Pesanan | S7 | 5.3, 4.5, 4.6 | Tepat 1 Slice |
| `ADM-05` | Admin | Manajemen Pengguna | S11 | 5.3, 4.11 | Tepat 1 Slice |
| `ADM-06` | Admin | Manajemen SP & Sanksi | S8 | 5.3, 4.11 | Tepat 1 Slice |
| `ADM-07` | Admin | Banding SP | S8 | 5.3, 4.11, 4.16 | Tepat 1 Slice |
| `ADM-08` | Admin | Laporan Aduan | S8 | 5.3, 4.12, 4.16 | Tepat 1 Slice |
| `ADM-09` | Admin | Tinjauan Mitra & Rating | S8 | 5.3, 4.14 | Tepat 1 Slice |
| `ADM-10` | Admin | Katalog Layanan | S3 | 5.3, 4.2 | Tepat 1 Slice |
| `ADM-11` | Admin | Keuangan Wilayah | S9 | 5.3, 4.16, 4.28 | Tepat 1 Slice |
| `ADM-12` | Admin | Otorisasi Wilayah Tugas | S1 | 5.3, 4.16, SYS-12 | Tepat 1 Slice |
| `ADM-13` | Admin | Siaga Tombol Darurat | S8 | 5.3, 4.12, 4.16 | Tepat 1 Slice |
| `ADM-14` | Admin | Profil & Notifikasi | S11 | 5.3 | Tepat 1 Slice |
| `ADM-15` | Admin | Filter Multi-Wilayah | S1 | 5.3, 4.16 | Tepat 1 Slice |
| `ADM-16` | Admin | Audit Riwayat Lintas Wilayah | S11 | 5.3, 4.16 | Tepat 1 Slice |
| `SA-01` | Super Admin | Manajemen Parameter | S11 | 5.4, 8.0 | Tepat 1 Slice |
| `SA-02` | Super Admin | Kelola Akun Admin | S1 | 5.4, 4.16 | Tepat 1 Slice |
| `SA-03` | Super Admin | Log Audit Terpusat | S11 | 5.4, 4.16, 7.3 | Tepat 1 Slice |
| `SA-04` | Super Admin | Monitoring Saldo Mengendap | S9 | 5.4, 4.9, 4.16 | Tepat 1 Slice |
| `SA-05` | Super Admin | Kelola Konten & Dokumen Legal | S11 | 5.4, 4.1, 4.13 | Tepat 1 Slice |
| `SA-06` | Super Admin | Master Wilayah | S1 | 5.4, 4.16 | Tepat 1 Slice |
| `SA-07` | Super Admin | Akses Admin Pusat & Eskalasi | S8 | 5.4, 4.16 | Tepat 1 Slice |
| `SA-08` | Super Admin | Laporan Lintas Wilayah | S9 | 5.4, 4.16 | Tepat 1 Slice |
| `SYS-01` | Sistem | Matching Pesanan Instan | S5 | 5.5, 4.5 | Tepat 1 Slice |
| `SYS-02` | Sistem | Pembatalan & Kedaluwarsa Otomatis | S7 | 5.5, 4.4, 4.6 | Tepat 1 Slice |
| `SYS-03` | Sistem | Otomasi Saldo & Komisi | S9 | 5.5, 4.10 | Tepat 1 Slice |
| `SYS-04` | Sistem | Otomasi Surat Peringatan (SP) | S8 | 5.5, 4.11 | Tepat 1 Slice |
| `SYS-05` | Sistem | Integritas Webhook & Dompet | S3 | 5.5, 4.9, 7.3 | Tepat 1 Slice |
| `SYS-06` | Sistem | Penyebaran Notifikasi Terisolasi | S11 | 5.5, Lampiran A, 4.16 | Tepat 1 Slice |
| `SYS-07` | Sistem | Pembersihan Data Otomatis | S11 | 5.5, 4.13, 4.15 | Tepat 1 Slice |
| `SYS-08` | Sistem | Audit Versi Persetujuan | S4 | 5.5, 4.1, 4.13 | Tepat 1 Slice |
| `SYS-09` | Sistem | Idempotensi Aksi Luar Jaringan | S6 | 5.5, 7.1 | Tepat 1 Slice |
| `SYS-10` | Sistem | Penentuan Wilayah Spasial | S1 | 5.5, 4.16 | Tepat 1 Slice |
| `SYS-11` | Sistem | Eskalasi Darurat Otomatis | S8 | 5.5, 4.12, 4.16 | Tepat 1 Slice |
| `SYS-12` | Sistem | Partisi Data Server-Side Admin | S1 | 5.5, 4.16, 7.3 | Tepat 1 Slice |

---

## 3. Pemetaan Alur Proses Bagian 6 BRD ke Slice

| No | Alur Proses BRD (Bagian 6) | Slice Penanggung Jawab | Keterangan Verifikasi Alur |
|---|---|---|---|
| **6.1** | Onboarding Mitra | **S2** (Onboarding & Verifikasi Mitra) | Pendaftaran dasar $\rightarrow$ lengkapi berkas $\rightarrow$ verifikasi admin wilayah $\rightarrow$ Terverifikasi. |
| **6.2** | Pemesanan Instan | **S5** (Pemesanan Instan) | Cek mitra online $\rightarrow$ penahanan dana $\rightarrow$ tawaran berurutan 60s $\rightarrow$ Mitra menerima $\rightarrow$ Dijadwalkan. |
| **6.3** | Pemesanan Terjadwal | **S4** (Pemesanan Terjadwal) | Pilih waktu (H+3 jam - 7 hari) $\rightarrow$ dana ditahan $\rightarrow$ bursa FCFS mitra $\rightarrow$ Mitra ambil $\rightarrow$ Dijadwalkan. |
| **6.4** | Pelaksanaan Layanan | **S6** (Pelaksanaan Layanan, Chat, Rating) | OTW (GPS 30s) $\rightarrow$ Tiba (radius 50m) $\rightarrow$ Mulai (timer) $\rightarrow$ Selesai $\rightarrow$ rating & jendela aduan 24 jam. |
| **6.5** | Aduan dan Darurat | **S8** (Aduan, Darurat, SP, Banding) | Tombol darurat SLA 5m (eskalasi 2m) $\rightarrow$ aduan 24 jam $\rightarrow$ investigasi admin $\rightarrow$ refund / SP. |
| **6.6** | Pengaturan Wilayah dan Admin | **S1** (Setup Master Wilayah & Admin) | Buat poligon tanpa tumpang tindih $\rightarrow$ tetapkan admin & siaga $\rightarrow$ aktivasi wilayah. |
| **6.7** | Perpindahan Domisili | **S3** (Customer) & **S10** (Mitra) | Customer ubah alamat utama (instan); Mitra ajukan alamat baru $\rightarrow$ verifikasi admin tujuan. |

---

## 4. Pemetaan Kebutuhan Non-Fungsional Bagian 7 BRD

| Klausul NFR | Deskripsi Kebutuhan Non-Fungsional | Fondasi / Slice Penanggung Jawab | Solusi Implementasi Arsitektural |
|---|---|---|---|
| **7.1** | Performa: Halaman awal <= 3 detik pada RAM 2-3GB; Update status <= 2 detik; Push instan <= 5 detik. | **F0, S5, S6** | PWA Vite + React mobile-first ringan; kompresi bundle aset; WebSocket / Server-Sent Events untuk live tracking; Web Push VAPID prioritas tinggi. |
| **7.1** | Ketahanan Offline: Aksi penting (Mulai, Selesai) tidak hilang saat sinyal terputus; dikirim ulang. | **S6 (SYS-09)** | Client-side queue (IndexedDB) + API Header `Idempotency-Key` di backend agar retry tidak menduplikasi state. |
| **7.2** | Ketersediaan: Uptime bulanan >= 99,5%. | **F0 / Hosting Infra** | Health check monitoring; worker queue Redis terpisah dari HTTP traffic; stateless API containers. |
| **7.3** | Keamanan: Autentikasi token; Otorisasi per menu & per wilayah tugas di server pada setiap request. | **F0, S1 (SYS-12, ADM-12)** | Middleware Laravel `VerifyAdminRegionAccess` & Eloquent Global Scope memfilter seluruh query di database. |
| **7.3** | Keamanan: Enkripsi data sensitif (NIK, KTP, rekening, catatan kondisi medis). | **S2, S4 (CUS-07)** | Kolom sensitif dienkripsi simetris AES-256 (`Crypt::encryptString`); file dokumen KTP/selfie disimpan di storage terproteksi non-public. |
| **7.3** | Keamanan: Verifikasi webhook Xendit & rate limiting login. | **S3 (SYS-05)** | Validasi token callback Xendit; pembatasan percobaan login salah maksimal 5 kali. |
| **7.4** | Perlindungan Data Pribadi: Hak hapus akun & retensi data transaksi. | **S3, S11 (CUS-22, SYS-07)** | Fitur hapus akun mengaburkan identitas (*anonymization/PII scrubbing*) namun mempertahankan referensi foreign key transaksi audit pajak. |
| **7.4** | Retensi: Otomasi pembersihan riwayat chat dan catatan medis setelah 30 hari. | **S11 (SYS-07)** | Scheduled task harian `PurgeOldChatAndMedicalData` membersihkan teks chat dan catatan medis > 30 hari. |
| **7.5** | Kompatibilitas & Kemudahan: PWA installable; Screen Wake Lock API pada mitra online; bahasa sederhana. | **F0, S5 (MIT-04)** | PWA Manifest & Service Worker; integrasi Browser Screen Wake Lock API saat status mitra Online. |
