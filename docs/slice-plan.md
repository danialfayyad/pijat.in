# Rencana Fondasi & Slice Implementasi (Slice Plan)

Dokumen ini mendefinisikan pembagian kerja bertahap berbasis *end-to-end slice*, dependensi antar-slice, kontrak yang disentuh, kriteria selesai, dan skenario pengujian E2E Playwright berdasarkan **BRD Pijat In Versi 2.2**.

---

## 1. Diagram Dependensi Slice (Teks)

```text
[F0] Fondasi Arsitektur, Kontrak Bersama, & Skema Inti (Dibekukan)
 │
 ├──► [S1] Setup Master Wilayah & Penugasan Admin
 │     │
 │     ├──► [S2] Onboarding, Verifikasi Dokumen, & Profil Mitra
 │     │     │
 │     ├──► [S3] Customer, Alamat Utama, Katalog Layanan, & Dompet Top-Up
 │     │     │
 │     │     ├────────────────────────┬────────────────────────┐
 │     │     ▼                        ▼                        │
 │     │   [S4] Pesanan Terjadwal    [S5] Pesanan Instan       │
 │     │   (FCFS Matching)           (Sekuensial 60s)          │
 │     │     │                        │                        │
 │     │     └───────────┬────────────┘                        │
 │     │                 ▼                                     │
 │     │               [S6] Pelaksanaan Layanan, Chat, Rating   │
 │     │                 │                                     │
 │     │                 ├────────────────────────┐            │
 │     │                 ▼                        ▼            │
 │     │               [S7] Batal, Reschedule   [S8] Aduan,    │
 │     │                    & Refund                 Darurat & │
 │     │                 │                           Sanksi SP │
 │     │                 └───────────┬────────────┘            │
 │     │                             ▼                         │
 │     │                           [S9] Saldo Mitra, Penarikan, │
 │     │                                & Keuangan Wilayah     │
 │     │                                                       │
 │     ├───────────────────────────────────────────────────────┤
 │     ▼                                                       ▼
 │   [S10] Perpindahan Domisili (Relocation)       [S11] Pelengkap: Retensi,
 └───► (Mitra ke Wilayah Lain)                           Anonymize, Log Audit
```

### 1.1 Urutan Pengerjaan & Aturan Paralelisme
1. **Fase 1 (Wajib Berurutan)**:
   - `F0` (Fondasi) $\rightarrow$ `S1` (Wilayah & Admin) $\rightarrow$ `S2` (Mitra) dan `S3` (Customer & Dompet).
2. **Paralelisme yang Aman**:
   - `S2` dan `S3` dapat dikerjakan paralel karena menyentuh modul data pengguna yang terpisah.
   - `S4` (Terjadwal) dan `S5` (Instan) dapat dikerjakan paralel setelah `S2` dan `S3` selesai.
   - `S7` (Batal & Refund) dan `S8` (Aduan & Darurat) dapat dikerjakan paralel setelah `S6` selesai.
   - `S10` (Perpindahan Domisili) dapat dikerjakan kapan saja setelah `S2` dan `S3` selesai.
3. **Fase Akhir**:
   - `S9` (Keuangan & Payout) dikerjakan setelah siklus pesanan, pembatalan, dan aduan stabil.
   - `S11` (Pelengkap) menjadi tahap pembersihan dan audit menyeluruh sebelum peluncuran.

---

## 2. Rincian Fondasi & Slice Implementasi

---

### [F0] Fondasi Arsitektur, Kontrak Bersama, & Skema Inti
- **Tujuan**: Membekukan kontrak status, matriks otorisasi wilayah, struktur ledger append-only, model data tabel fondasi, dan panduan coding agent.
- **Peran Terlibat**: Seluruh Peran (Customer, Mitra, Admin, Super Admin, Sistem).
- **ID Kebutuhan**: Fondasi untuk seluruh 81 ID fungsional.
- **Rujukan BRD**: 1.5, 3.2, 4.0, 7.0, Lampiran A & B.
- **Depends On**: Tidak ada (Root).
- **Kontrak Bersama**: `CLAUDE.md`, `docs/contracts/*`, `features.json`.
- **Kriteria Selesai**:
  1. Seluruh dokumen kontrak di `docs/contracts/` telah dibekukan.
  2. Migrasi tabel fondasi (`users`, `regions`, `admin_region_assignments`, `orders`, `order_status_histories`, `customer_wallets`, `partner_balances`, `wallet_transactions`, `partner_balance_transactions`) berhasil dieksekusi di MySQL.
  3. Tes unit model Eloquent dan integritas Check Constraint berhasil lulus.
- **Skenario E2E Utama**: N/A (Tahap Fondasi Backend).
- **Risiko**: Perubahan di kemudian hari pada tabel fondasi berpotensi merusak rantai seluruh slice.

---

### [S1] Setup Master Wilayah & Penugasan Admin
- **Tujuan**: Memungkinkan Super Admin mengelola poligon wilayah layanan (tidak tumpang tindih) dan menugaskan akun Admin dengan partisi wilayah server-side.
- **Peran Terlibat**: Super Admin, Admin.
- **ID Kebutuhan**: `ADM-01`, `ADM-12`, `ADM-15`, `SA-02`, `SA-06`, `SYS-10`, `SYS-12`.
- **Rujukan BRD**: 4.16, 5.3, 5.4, 5.5, 6.6.
- **Depends On**: `F0`.
- **Kontrak Bersama**: `authz-matrix.md`, `region-rules.md`.
- **Kriteria Selesai**:
  1. Super Admin dapat menggambar poligon wilayah baru pada peta Leaflet.
  2. Sistem menolak poligon yang beririsan dengan wilayah aktif lain.
  3. Super Admin dapat membuat akun Admin dan menugaskan 1 atau lebih wilayah tugas.
  4. Middleware server menolak akses Admin terhadap data di luar wilayah tugasnya (HTTP 403).
  5. Admin dengan multi-wilayah dapat memfilter data dengan dropdown pemilih wilayah.
- **Skenario E2E Utama**:
  Super Admin login $\rightarrow$ buat Wilayah "Bandung" dan "Cimahi" $\rightarrow$ buat akun Admin A untuk Bandung $\rightarrow$ Admin A login dan membuka dashboard $\rightarrow$ verifikasi hanya data Bandung yang tampil $\rightarrow$ coba akses endpoint wilayah Cimahi via API (terblokir 403).
- **Risiko**: Kegagalan fungsi spasial MySQL `ST_Intersects` jika versi MySQL di bawah 8.0.16.

---

### [S2] Onboarding, Verifikasi Dokumen, & Profil Mitra
- **Tujuan**: Mitra dapat mendaftar, mengisi data lengkap (KTP, selfie, NIK, pin domisili, rekening, SOP), dan diverifikasi oleh Admin Wilayah domisili dalam SLA 24 jam.
- **Peran Terlibat**: Mitra, Admin.
- **ID Kebutuhan**: `MIT-01`, `MIT-02`, `MIT-03`, `MIT-20`, `ADM-02`, `ADM-03`.
- **Rujukan BRD**: 4.1, 4.16, 5.2, 5.3, 6.1.
- **Depends On**: `S1`.
- **Kontrak Bersama**: `region-rules.md`, `data-model.md`.
- **Kriteria Selesai**:
  1. Pendaftaran dasar mitra menghasilkan status `BELUM_LENGKAP` setelah verifikasi email.
  2. Form kelengkapan data memvalidasi pin peta domisili (wajib di wilayah aktif).
  3. Dokumen KTP, selfie, rekening, dan persetujuan SOP tersimpan dengan status `MENUNGGU_VERIFIKASI`.
  4. Admin Wilayah domisili melihat antrean berkas dan dapat menyetujui (`TERVERIFIKASI`) atau menolak dengan alasan baku.
- **Skenario E2E Utama**:
  Mitra daftar akun $\rightarrow$ verifikasi email $\rightarrow$ isi NIK, unggah KTP/selfie, sematkan pin domisili Bandung $\rightarrow$ Admin Bandung login $\rightarrow$ periksa berkas mitra $\rightarrow$ klik Setujui $\rightarrow$ Mitra login kembali dan status berubah menjadi Terverifikasi.
- **Risiko**: Ukuran file foto KTP/selfie membebani perangkat mitra RAM rendah (wajib kompresi gambar di sisi client sebelum upload).

---

### [S3] Customer, Alamat Utama, Katalog Layanan, & Dompet Top-Up
- **Tujuan**: Customer mendaftar, menyimpan alamat utama dengan pin peta, melihat katalog layanan, dan mengisi saldo dompet melalui payment gateway Xendit.
- **Peran Terlibat**: Customer, Admin (Katalog), Sistem.
- **ID Kebutuhan**: `CUS-01`, `CUS-02`, `CUS-03`, `CUS-05`, `CUS-09`, `CUS-16`, `CUS-22`, `CUS-23`, `ADM-10`, `SYS-05`.
- **Rujukan BRD**: 4.2, 4.9, 4.16, 5.1, 5.3, 5.5, 6.7.
- **Depends On**: `S1`.
- **Kontrak Bersama**: `wallet-ledger.md`, `region-rules.md`, `parameters.md`.
- **Kriteria Selesai**:
  1. Registrasi customer mewajibkan gender, email, dan verifikasi OTP email/nomor HP.
  2. Customer wajib menyimpan alamat (otomatis menjadi Alamat Utama dan menetapkan `region_id` customer).
  3. Top-up dompet via invoice Xendit (mock di testing) berhasil menambah `wallet_balance` dengan atribusi `region_id` alamat utama.
  4. Top-up tanpa alamat utama ditolak sistem.
  5. Admin berizin Katalog dapat mengelola daftar layanan, durasi, dan harga tetap (tanpa add-on).
- **Skenario E2E Utama**:
  Customer mendaftar $\rightarrow$ simpan alamat pin peta di Bandung (berhasil jadi alamat utama) $\rightarrow$ buka dompet $\rightarrow$ top-up Rp 100.000 via Xendit $\rightarrow$ simulasi webhook callback Xendit sukses $\rightarrow$ saldo bertambah menjadi Rp 100.000 dengan catatan transaksi ber-atribusi wilayah Bandung.
- **Risiko**: Kerentanan transaksi ganda jika webhook Xendit tidak divalidasi dengan idempotency key dan signature token.

---

### [S4] Pemesanan Terjadwal, Matching FCFS, & Jadwal Mitra
- **Tujuan**: Customer dapat memesan layanan terjadwal (H+3 jam s.d. 7 hari); pesanan tampil di menu Pesanan Terjadwal mitra kandidat sesama gender; mitra mengambil pesanan secara siapa-cepat-dia-dapat (*first-come first-served*).
- **Peran Terlibat**: Customer, Mitra, Sistem.
- **ID Kebutuhan**: `CUS-04`, `CUS-06`, `CUS-07`, `CUS-10`, `MIT-06`, `MIT-07`, `MIT-08`, `SYS-08`.
- **Rujukan BRD**: 4.3, 4.4, 4.5, 4.6, 5.1, 5.2, 5.5, 6.3.
- **Depends On**: `S2`, `S3`.
- **Kontrak Bersama**: `order-status-machine.md`, `region-rules.md`, `wallet-ledger.md`.
- **Kriteria Selesai**:
  1. Validasi jam operasional wilayah (08:00–21:00) dan jeda waktu minimal 3 jam.
  2. Dana pesanan berstatus Ditahan (*held_balance*) saat pesanan berstatus `MENCARI_MITRA`.
  3. Pesanan hanya tampil pada mitra dengan gender sama, wilayah domisili sama, radius 5 km dari alamat domisili mitra, dan tidak bentrok jadwal (+60 menit jeda).
  4. Pengambilan pesanan terlindungi transaksi penguncian pesimis (hanya 1 mitra berhasil mengambil).
  5. Pesanan masuk ke menu "Jadwal Saya" mitra dan status berubah menjadi `DIJADWALKAN`.
- **Skenario E2E Utama**:
  Customer wanita buat pesanan terjadwal untuk besok pukul 10:00 di Bandung $\rightarrow$ saldo ditahan $\rightarrow$ Mitra wanita Bandung buka menu Pesanan Terjadwal $\rightarrow$ klik Ambil Pesanan $\rightarrow$ pesanan berhasil diambil dan masuk ke Jadwal Saya $\rightarrow$ Customer melihat status berubah menjadi Dijadwalkan beserta profil mitra.
- **Risiko**: *Race condition* jika dua mitra menekan tombol ambil secara bersamaan (dicegah via `SELECT FOR UPDATE`).

---

### [S5] Pemesanan Instan, Penawaran Sekuensial 60s, & Lock
- **Tujuan**: Pemesanan instan dengan pengecekan ketersediaan awal, penawaran bergilir 60 detik per mitra terdekat, dan konfirmasi penerimaan.
- **Peran Terlibat**: Customer, Mitra, Sistem.
- **ID Kebutuhan**: `CUS-08`, `MIT-04`, `MIT-05`, `SYS-01`.
- **Rujukan BRD**: 4.4, 4.5, 4.6, 5.1, 5.2, 5.5, 6.2.
- **Depends On**: `S2`, `S3`.
- **Kontrak Bersama**: `order-status-machine.md`, `region-rules.md`, `parameters.md`.
- **Kriteria Selesai**:
  1. Sistem memeriksa keberadaan mitra Online sebelum checkout instan.
  2. Tawaran dikirim ke mitra terdekat ke-1 dengan hitung mundur 60 detik dan notifikasi prioritas tinggi.
  3. Jika ditolak atau timeout 60 detik, tawaran berpindah ke mitra terdekat ke-2.
  4. Begitu mitra menekan Terima, pesanan terkunci berstatus `DIJADWALKAN`, alamat lengkap terbuka, dan batas waktu 30 menit dihentikan.
- **Skenario E2E Utama**:
  Mitra A (1 km) dan Mitra B (3 km) Online di Bandung $\rightarrow$ Customer order instan $\rightarrow$ Mitra A menerima pop-up tawaran 60s $\rightarrow$ Mitra A menekan Tolak $\rightarrow$ tawaran seketika masuk ke Mitra B $\rightarrow$ Mitra B menekan Terima $\rightarrow$ pesanan berhasil Dijadwalkan untuk Mitra B.
- **Risiko**: Latensi jaringan atau layar mati pada perangkat mitra (mitigasi: fallback timeout otomatis di server).

---

### [S6] Pelaksanaan Layanan, Chat, & Rating
- **Tujuan**: Menjalankan siklus layanan dari OTW (pelacakan GPS), Tiba (radius 50 m), Mulai (timer berjalan), Selesai (durasi penuh), chat interaktif, dan rating timbal balik 1–5 bintang.
- **Peran Terlibat**: Customer, Mitra, Sistem.
- **ID Kebutuhan**: `CUS-11`, `CUS-14`, `CUS-15`, `MIT-09`, `MIT-10`, `MIT-13`, `MIT-14`, `MIT-17`, `SYS-09`.
- **Rujukan BRD**: 4.6, 4.8, 4.14, 4.15, 5.1, 5.2, 5.5, 6.4.
- **Depends On**: `S4` atau `S5`.
- **Kontrak Bersama**: `order-status-machine.md`, `wallet-ledger.md`.
- **Kriteria Selesai**:
  1. Mitra menekan OTW: status `MENUJU_LOKASI`, lokasi mitra di-update tiap 30 detik, customer tidak bisa membatalkan.
  2. Mitra menekan Tiba: divalidasi geofence radius <= 50 meter dari alamat layanan; status menjadi `TIBA`.
  3. Mitra menekan Mulai: status `SEDANG_BERLANGSUNG`, timer hitung mundur aktif di layar kedua pihak.
  4. Tombol Selesai hanya aktif setelah durasi paket terpenuhi 100%; saat Selesai ditekan, dana dilepas (komisi tercatat, saldo mitra masuk `pending_balance`).
  5. Form rating 1–5 bintang terbuka (tanpa ulasan teks); chat dapat digunakan selama pesanan berjalan.
- **Skenario E2E Utama**:
  Mitra tekan OTW $\rightarrow$ jalan ke lokasi $\rightarrow$ tekan Tiba di radius 40 m (berhasil) $\rightarrow$ tekan Mulai $\rightarrow$ timer berjalan $\rightarrow$ durasi selesai $\rightarrow$ tekan Selesai $\rightarrow$ kedua pihak saling memberi rating bintang 5 $\rightarrow$ status pesanan Selesai tercatat di riwayat.
- **Risiko**: Masalah akurasi GPS pada perangkat mitra (mitigasi: batas toleransi akurasi GPS 150 m dan opsi Minta Konfirmasi Lokasi).

---

### [S7] Pembatalan, Reschedule, Refund, & Potongan Kompensasi
- **Tujuan**: Menegakkan seluruh matriks pembatalan (sebelum OTW, H-3 jam potongan 50%, no-show 50%, keterlambatan mitra > 30m, batas pencarian habis) dan reschedule pesanan terjadwal (maks. 2x).
- **Peran Terlibat**: Customer, Mitra, Admin, Sistem.
- **ID Kebutuhan**: `CUS-12`, `CUS-13`, `MIT-11`, `MIT-12`, `ADM-04`, `SYS-02`.
- **Rujukan BRD**: 4.6, 4.7, 5.1, 5.2, 5.3, 5.5.
- **Depends On**: `S6`.
- **Kontrak Bersama**: `order-status-machine.md`, `wallet-ledger.md`.
- **Kriteria Selesai**:
  1. Customer membatalkan terjadwal < 3 jam sebelum jadwal: refund 50%, potongan 50% dibagi untuk mitra dan platform.
  2. Mitra Tiba menunggu 15 menit, lalu klik "Customer Tidak Ada": customer dikenai sanksi SP dan potongan 50%.
  3. Pembatalan khusus mitra: "Gender tidak sesuai" (customer kena SP & potongan 50%; mitra bebas sanksi) dan "Kondisi medis tidak sesuai" (refund 100%, mitra bebas sanksi).
  4. Mitra membatalkan pesanan dijadwalkan: pesanan dilempar ulang, mitra terkena SP otomatis.
  5. Admin membatalkan pesanan macet: refund 100% dengan alasan wajib.
  6. Reschedule terjadwal (maks. 2x, minimal H-3 jam) mengembalikan pesanan ke status Mencari Mitra.
- **Skenario E2E Utama**:
  Customer membatalkan pesanan terjadwal pada H-2 jam $\rightarrow$ modal rincian potongan 50% tampil $\rightarrow$ customer konfirmasi $\rightarrow$ saldo dompet customer menerima refund 50% $\rightarrow$ saldo tertahan mitra bertambah 80% dari potongan $\rightarrow$ komisi platform mencatat 20% dari potongan.
- **Risiko**: Inkonsistensi pembagian kas potongan pembatalan (dicegah via unit test tabel refund di Pest PHP).

---

### [S8] Tombol Darurat, Aduan 24 Jam, SP Bertingkat, & Banding
- **Tujuan**: Menangani insiden keselamatan via tombol darurat (SLA 5 menit, eskalasi 2 menit ke Super Admin), pelaporan aduan 24 jam (pembekuan saldo tertahan), sistem sanksi SP1/SP2/SP3, dan peninjauan banding.
- **Peran Terlibat**: Customer, Mitra, Admin, Super Admin, Sistem.
- **ID Kebutuhan**: `CUS-18`, `CUS-19`, `CUS-20`, `MIT-18`, `MIT-19`, `ADM-06`, `ADM-07`, `ADM-08`, `ADM-09`, `ADM-13`, `SA-07`, `SYS-04`, `SYS-11`.
- **Rujukan BRD**: 4.11, 4.12, 4.14, 4.16, 5.1, 5.2, 5.3, 5.4, 5.5, 6.5.
- **Depends On**: `S6`, `S7`.
- **Kontrak Bersama**: `notification-matrix.md`, `authz-matrix.md`, `parameters.md`.
- **Kriteria Selesai**:
  1. Tombol darurat ditekan: notifikasi darurat masuk seketika ke Admin Siaga wilayah pesanan.
  2. Jika dalam 2 menit tidak ada admin siaga yang menekan Tangani, notifikasi eskalasi berbunyi di Super Admin.
  3. Pengajuan aduan dalam 24 jam membekukan pelepasan pendapatan pesanan tersebut di saldo mitra.
  4. Admin menyetujui aduan customer: refund 100% ditarik dari saldo tertahan mitra.
  5. Akumulasi SP memicu sanksi otomatis: SP1 peringatan, SP2 akun dibekukan/ditangguhkan 3 hari, SP3 blokir permanen nomor HP dan perangkat.
  6. Pengajuan banding SP2 mitra ditinjau oleh Admin Wilayah Akun mitra; log audit mencatat penerbit, peninjau, dan keputusan.
- **Skenario E2E Utama**:
  Customer tekan tombol darurat saat layanan $\rightarrow$ alarm berbunyi di Admin Siaga Bandung $\rightarrow$ Admin Bandung klik Tangani $\rightarrow$ status tiket menjadi Diproses $\rightarrow$ pasca-layanan customer ajukan aduan perilaku tidak pantas $\rightarrow$ Admin verifikasi aduan valid $\rightarrow$ terbitkan SP ke mitra dan lakukan refund penuh ke customer.
- **Risiko**: Keterlambatan eskalasi WebSocket darurat (mitigasi: pooling heartbeat fallback pada admin console).

---

### [S9] Saldo Mitra 24 Jam, Penarikan Dana, & Keuangan Wilayah
- **Tujuan**: Otomasi pemindahan saldo Tertahan ke Tersedia setelah 24 jam, pengajuan penarikan dana ke bank via Xendit Payout, dan pembukuan keuangan wilayah admin serta monitoring settlement global Super Admin.
- **Peran Terlibat**: Customer, Mitra, Admin (Keuangan), Super Admin, Sistem.
- **ID Kebutuhan**: `CUS-17`, `MIT-15`, `MIT-16`, `ADM-11`, `SA-04`, `SA-08`, `SYS-03`.
- **Rujukan BRD**: 4.9, 4.10, 4.16, 5.1, 5.2, 5.3, 5.4, 5.5.
- **Depends On**: `S7`, `S8`.
- **Kontrak Bersama**: `wallet-ledger.md`, `parameters.md`.
- **Kriteria Selesai**:
  1. Scheduler sistem memindahkan saldo pesanan selesai dari `pending_balance` ke `available_balance` tepat 24 jam setelah pesanan Selesai.
  2. Mitra mengajukan penarikan dari saldo tersedia ke rekening terverifikasi (nominal > ambang batas masuk verifikasi admin).
  3. Menu Keuangan Admin Wilayah menampilkan mutasi arus kas ber-atribusi `region_id` wilayah tugasnya.
  4. Super Admin memantau saldo mengendap customer berdasarkan wilayah domisili saat ini dan mencocokkan total settlement bank Xendit.
  5. Ekspor laporan keuangan wilayah dan laporan konsolidasi Super Admin ke format .xlsx dan .pdf.
- **Skenario E2E Utama**:
  Pesanan selesai kemarin $\rightarrow$ scheduler 24 jam berjalan $\rightarrow$ saldo tertahan mitra Rp 160.000 berpindah ke saldo tersedia $\rightarrow$ mitra ajukan penarikan Rp 150.000 $\rightarrow$ diproses via payout Xendit $\rightarrow$ mutasi penarikan tercatat di Keuangan Admin Wilayah $\rightarrow$ Super Admin memantau settlement Xendit berkurang.
- **Risiko**: Selisih angka pembulatan desimal (dicegah via penggunaan ketat tipe `DECIMAL(14,2)`).

---

### [S10] Perpindahan Domisili (Relocation) Antarwilayah
- **Tujuan**: Customer mengubah alamat utama secara instan, dan mitra mengajukan perpindahan domisili ke wilayah lain dengan verifikasi ulang oleh admin wilayah tujuan.
- **Peran Terlibat**: Customer, Mitra, Admin.
- **ID Kebutuhan**: `MIT-22`.
- **Rujukan BRD**: 4.16, 5.2, 6.7.
- **Depends On**: `S2`, `S3`.
- **Kontrak Bersama**: `region-rules.md`, `authz-matrix.md`.
- **Kriteria Selesai**:
  1. Customer mengubah alamat utama di profil $\rightarrow$ `customer.region_id` berpindah seketika.
  2. Mitra mengajukan alamat baru di wilayah lain $\rightarrow$ sistem memvalidasi tidak ada pesanan aktif/dijadwalkan.
  3. Selama pengajuan, mitra tidak dapat Online dan penarikan saldo ditahan sementara.
  4. Pengajuan masuk ke antrean Admin Wilayah Tujuan.
  5. Setelah disetujui Admin Wilayah Tujuan, wilayah mitra berpindah, dan riwayat lama (rating, SP, pesanan) tetap utuh dapat dilihat admin wilayah baru.
- **Skenario E2E Utama**:
  Mitra Bandung ajukan pindah domisili ke Jakarta $\rightarrow$ status mitra terkunci tidak bisa Online $\rightarrow$ Admin Jakarta login $\rightarrow$ buka antrean verifikasi pindah domisili $\rightarrow$ periksa alamat baru $\rightarrow$ klik Setujui $\rightarrow$ wilayah mitra berubah menjadi Jakarta dan dapat kembali Online di Jakarta.
- **Risiko**: Kebocoran pesanan jika mitra masih memiliki jadwal aktif saat pengajuan (dicegah via validasi ketat status pesanan).

---

### [S11] Pelengkap: Retensi Data 30 Hari, Anonymization, & Log Audit
- **Tujuan**: Menjalankan penghapusan otomatis data sensitif (chat & catatan medis) setelah 30 hari, penanganan hapus akun (anonymization PII), log audit terpusat, dan manajemen parameter dinamis.
- **Peran Terlibat**: Admin, Super Admin, Sistem.
- **ID Kebutuhan**: `ADM-05`, `ADM-14`, `ADM-16`, `SA-01`, `SA-03`, `SA-05`, `CUS-21`, `MIT-21`, `SYS-06`, `SYS-07`.
- **Rujukan BRD**: 4.11, 4.13, 4.15, 5.1, 5.2, 5.3, 5.4, 5.5, 7.3, 7.4, 8.0.
- **Depends On**: `S8`, `S9`.
- **Kontrak Bersama**: `parameters.md`, `data-model.md`.
- **Kriteria Selesai**:
  1. Scheduler membersihkan data teks chat dan catatan medis pesanan yang berumur > 30 hari.
  2. Permohonan hapus akun menganonimkan data pribadi (nama, email, no HP, NIK) tanpa menghapus baris transaksi keuangan di ledger.
  3. Log audit merekam seluruh mutasi parameter, sanksi, dan penugasan admin.
  4. Pengaturan parameter Bagian 8 di Super Admin berfungsi dinamis dan tidak berlaku mundur.
- **Skenario E2E Utama**:
  Super Admin ubah parameter denda pembatalan di dashboard $\rightarrow$ verifikasi entri tercatat di log audit $\rightarrow$ jalankan cron job retensi data $\rightarrow$ verifikasi chat pesanan 35 hari lalu terhapus sementara nota transaksi tetap utuh $\rightarrow$ customer ajukan hapus akun $\rightarrow$ data identitas ter-anonymize.
- **Risiko**: Kehilangan jejak audit jika anonymization menghapus kunci relasi (dicegah dengan mempertahankan User ID dan hanya mengaburkan field PII).
