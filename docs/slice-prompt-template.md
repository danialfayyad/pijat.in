# Panduan & Templat Prompt Per Slice (docs/slice-prompt-template.md)

Dokumen ini menyediakan templat prompt standar yang wajib digunakan saat menginstruksikan AI Coding Agent (seperti Claude Code) untuk mengeksekusi satu slice alur kerja end-to-end, disertai dua contoh terisi lengkap untuk **Slice S2** dan **Slice S4**.

---

## 1. Format Templat Prompt Standar

```markdown
# Instruksi Pengerjaan Slice: [KODE_SLICE] - [NAMA_SLICE]

## 1. Peran & Batasan Agen
Anda adalah AI Coding Agent yang bertugas menyelesaikan satu slice alur end-to-end secara tuntas pada aplikasi Pijat In.
- Kerjakan HANYA slice ini. Dilarang melompat ke slice lain.
- Dilarang mengubah file kontrak di `docs/contracts/` tanpa melapor dan meminta persetujuan.
- Dilarang melemahkan atau menghapus kriteria penerimaan di `features.json`.
- Perubahan skema database HANYA boleh melalui file migrasi baru yang modular. Migrasi lama yang sudah diterapkan DILARANG diedit.

## 2. Tujuan Alur
[Tuliskan tujuan fungsional alur end-to-end dari slice ini secara ringkas dan jelas]

## 3. Rujukan BRD & Kontrak Wajib
- **Rujukan BRD**: [Sebutkan bagian/pasal BRD yang relevan, misal 4.1, 4.16, 6.1]
- **Kontrak Wajib yang Harus Dipatuhi**:
  - `CLAUDE.md`
  - `docs/contracts/[nama-kontrak-1].md`
  - `docs/contracts/[nama-kontrak-2].md`

## 4. Daftar Kebutuhan Fungsional & Kriteria Penerimaan
Buka file `features.json` dan selesaikan kebutuhan berikut:
- [ID-1]: [Deskripsi]
  - Kriteria 1: ...
  - Kriteria 2: ...
- [ID-2]: [Deskripsi]
  - Kriteria 1: ...

## 5. Batasan Khusus & Aturan Teknis
- [Sebutkan batasan teknis spesifik, misal: kompresi gambar di client, batas geofence, isolasi data wilayah di server]

## 6. Syarat Verifikasi Selesai
1. Jalankan linter dan formatter: `vendor/bin/pint --format agent` (PHP) dan `npm run lint`.
2. Jalankan tes unit dan fitur: `php artisan test --compact` (wajib 100% pass).
3. Jalankan tes E2E Playwright: `npx playwright test e2e/[nama-slice].spec.ts` (wajib 100% pass).
4. Hanya setelah seluruh pengujian hijau, ubah nilai `passes: true` pada ID terkait di `features.json`.
5. Perbarui status slice di `docs/progress.md`.

## 7. Instruksi Rencana Dulu (Plan First)
Sebelum menulis atau mengubah kode apa pun, keluarkan rencana implementasi terstruktur:
1. File migrasi database baru apa saja yang akan dibuat.
2. Endpoint API / Controller / Model backend apa saja yang disentuh.
3. Komponen antarmuka PWA/Web apa saja yang dibuat/diubah.
4. Skenario tes unit dan tes E2E yang akan disusun.
Tunggu persetujuan rencana sebelum mulai membuat kode.
```

---

## 2. Contoh Terisi Lengkap: Slice S2 (Onboarding & Verifikasi Mitra)

```markdown
# Instruksi Pengerjaan Slice: S2 - Onboarding, Verifikasi Dokumen, & Profil Mitra

## 1. Peran & Batasan Agen
Anda adalah AI Coding Agent yang bertugas menyelesaikan Slice S2 pada aplikasi Pijat In.
- Kerjakan HANYA alur onboarding, pelengkapan dokumen profil mitra, dan verifikasi oleh admin wilayah domisili mitra.
- Dilarang mengubah file kontrak di `docs/contracts/` tanpa melapor dan meminta persetujuan.
- Dilarang melemahkan kriteria penerimaan di `features.json`.
- Perubahan skema database HANYA boleh melalui file migrasi baru yang modular.

## 2. Tujuan Alur
Memungkinkan calon mitra mendaftar data dasar, melengkapi dokumen identitas (NIK, foto KTP, foto selfie, rekening bank, pin peta domisili, persetujuan SOP & perjanjian kemitraan) di dalam PWA Mitra, serta memungkinkan Admin Wilayah domisili mitra memverifikasi dokumen tersebut (target SLA 24 jam) untuk mengubah status akun menjadi TERVERIFIKASI.

## 3. Rujukan BRD & Kontrak Wajib
- **Rujukan BRD**: Bagian 4.1 (Model Kemitraan & Verifikasi), 4.16 (Wilayah Layanan), 5.2 (MIT-01, MIT-02, MIT-03, MIT-20), 5.3 (ADM-02, ADM-03), 6.1 (Alur Onboarding Mitra).
- **Kontrak Wajib yang Harus Dipatuhi**:
  - `CLAUDE.md`
  - `docs/contracts/region-rules.md` (Penentuan wilayah domisili dari pin peta; penolakan jika di luar wilayah aktif)
  - `docs/contracts/authz-matrix.md` (Admin hanya melihat antrean verifikasi mitra di wilayah tugasnya)
  - `docs/contracts/data-model.md` (Tabel `partner_profiles`, `bank_accounts`, `compliance_agreements`)

## 4. Daftar Kebutuhan Fungsional & Kriteria Penerimaan
Buka file `features.json` dan selesaikan kebutuhan berikut:
- **MIT-01**: Pendaftaran data dasar & verifikasi email $\rightarrow$ status akun menjadi `BELUM_LENGKAP`.
- **MIT-02**: Melengkapi NIK, KTP, selfie, rekening & bukti kepemilikan, pin domisili pada peta Leaflet (validasi poligon wilayah aktif via `ST_Contains`), verifikasi OTP nomor HP, persetujuan SOP & kemitraan dengan pencatatan timestamp dan versi dokumen $\rightarrow$ status akun menjadi `MENUNGGU_VERIFIKASI`.
- **MIT-03**: Login mitra menampilkan status akun dan alasan penolakan (jika ditolak).
- **MIT-20**: Profil mitra dapat memperbarui rekening dan alamat (memicu verifikasi ulang dan pembekuan penarikan saldo sementara).
- **ADM-02**: Admin Wilayah domisili mitra meninjau antrean verifikasi KTP, selfie, NIK, dan kesesuaian nama rekening dengan KTP, lalu menyetujui (`TERVERIFIKASI`) atau menolak dengan alasan baku.
- **ADM-03**: Admin Wilayah memverifikasi bukti rekening bank.

## 5. Batasan Khusus & Aturan Teknis
1. **Kompresi Gambar Sisi Client**: Karena perangkat mitra berspesifikasi RAM 2–3 GB, kompres foto KTP dan selfie di browser sebelum diunggah (maksimal 1 MB per gambar).
2. **Enkripsi NIK & Rekening**: Kolom NIK dan nomor rekening wajib dienkripsi di level aplikasi (`Crypt::encryptString`) sebelum disimpan ke database MySQL.
3. **Isolasi Antrean Verifikasi**: Query antrean verifikasi admin wajib menggunakan scope wilayah tugas admin di server: `WHERE partner_profiles.domicile_region_id IN (:admin_assigned_regions)`.

## 6. Syarat Verifikasi Selesai
1. Jalankan linter: `vendor/bin/pint --format agent` dan `npm run lint`.
2. Jalankan tes Pest: `php artisan test tests/Feature/PartnerOnboardingTest.php` (wajib pass).
3. Jalankan tes E2E Playwright: `npx playwright test e2e/partner-onboarding.spec.ts` (wajib pass multi-role: Mitra PWA daftar $\rightarrow$ Admin Web setujui).
4. Ubah `passes: true` pada `MIT-01`, `MIT-02`, `MIT-03`, `MIT-20`, `ADM-02`, `ADM-03` di `features.json`.
5. Perbarui status Slice S2 di `docs/progress.md`.

## 7. Instruksi Rencana Dulu (Plan First)
Keluarkan rencana langkah implementasi file migrasi, endpoint API, komponen React PWA, dan test case sebelum menulis kode.
```

---

## 3. Contoh Terisi Lengkap: Slice S4 (Pesanan Terjadwal & FCFS Matching)

```markdown
# Instruksi Pengerjaan Slice: S4 - Pemesanan Terjadwal & Matching FCFS

## 1. Peran & Batasan Agen
Anda adalah AI Coding Agent yang bertugas menyelesaikan Slice S4 pada aplikasi Pijat In.
- Kerjakan HANYA alur pemesanan terjadwal oleh customer dan pengambilan pesanan oleh mitra secara FCFS.
- Dilarang mengubah file kontrak di `docs/contracts/` tanpa melapor dan meminta persetujuan.
- Dilarang melemahkan kriteria penerimaan di `features.json`.
- Perubahan skema database HANYA boleh melalui file migrasi baru yang modular.

## 2. Tujuan Alur
Memungkinkan customer membuat pesanan layanan terjadwal (H+3 jam s.d. 7 hari ke depan) dalam jam operasional wilayah (08:00–21:00) dengan penahanan dana di dompet customer, kemudian pesanan tampil di menu Pesanan Terjadwal bagi mitra kandidat yang memenuhi syarat (sesama gender, wilayah domisili sama, radius 5 km, tidak bentrok jadwal), dan mitra pertama yang menekan tombol ambil mendapatkan pesanan secara adil dan aman dari *race condition*.

## 3. Rujukan BRD & Kontrak Wajib
- **Rujukan BRD**: Bagian 4.2 (Durasi & Harga), 4.3 (Aturan Sesama Gender), 4.4 (Jam Operasional & Jenis Pesanan), 4.5 (Aturan Penugasan Terjadwal), 4.6 (Mesin Status), 4.13 (Persetujuan Risiko & Kondisi Medis), 5.1 (CUS-04, CUS-06, CUS-07, CUS-10), 5.2 (MIT-06, MIT-07, MIT-08), 5.5 (SYS-08), 6.3 (Alur Pemesanan Terjadwal).
- **Kontrak Wajib yang Harus Dipatuhi**:
  - `CLAUDE.md`
  - `docs/contracts/order-status-machine.md` (Transisi `[START] -> MENCARI_MITRA -> DIJADWALKAN`)
  - `docs/contracts/region-rules.md` (Pesanan terikat permanen pada region_id alamat layanan; mitra wajib berdomisili sama)
  - `docs/contracts/wallet-ledger.md` (Penahanan dana saldo dompet saat order dibuat)
  - `docs/contracts/parameters.md` (Jeda antar-pesanan mitra 60 menit, radius 5 km garis lurus)

## 4. Daftar Kebutuhan Fungsional & Kriteria Penerimaan
Buka file `features.json` dan selesaikan kebutuhan berikut:
- **CUS-04**: Pemesanan untuk diri sendiri atau orang lain dengan input nama dan gender penerima fisik.
- **CUS-06**: Pemilihan waktu terjadwal (min 3 jam, maks 7 hari, jam selesai <= 21:00 waktu wilayah).
- **CUS-07**: Checkbox persetujuan risiko wajib dicentang; kolom catatan medis tersimpan terenkripsi.
- **CUS-10**: Customer memantau hitung mundur batas pencarian; setelah diambil, profil mitra tampil.
- **MIT-06**: Menu Pesanan Terjadwal mitra menampilkan pesanan yang cocok; pengambilan pesanan FCFS terproteksi database lock.
- **MIT-07**: Pesanan yang diambil masuk ke menu "Jadwal Saya" mitra dan catatan medis terbuka.
- **MIT-08**: Mitra dapat menyaring layanan yang diaktifkan di profilnya.
- **SYS-08**: Pencatatan versi dokumen risiko dan consent timestamp.

## 5. Batasan Khusus & Aturan Teknis
1. **Strict Sesama Gender**: Validasi server-side tegas `mitra.gender === order.recipient_gender`.
2. **Anti-Race Condition**: Gunakan `DB::transaction` dan `SELECT * FROM orders WHERE id = ? FOR UPDATE`. Pastikan jika pesanan sudah diambil oleh mitra A, percobaan mitra B mengembalikan error informatif bahwa pesanan telah terisi.
3. **Pencegahan Bentrok Jadwal (+60 Menit Jeda)**: Mitra tidak boleh mengambil pesanan jika rentang waktu `[jadwal_mulai - 60 menit, jadwal_selesai + 60 menit]` beririsan dengan pesanan lain di jadwalnya.

## 6. Syarat Verifikasi Selesai
1. Jalankan linter: `vendor/bin/pint --format agent` dan `npm run lint`.
2. Jalankan tes Pest: `php artisan test tests/Feature/ScheduledOrderDispatchTest.php` (wajib pass).
3. Jalankan tes E2E Playwright: `npx playwright test e2e/scheduled-order.spec.ts` (wajib pass: Customer booking $\rightarrow$ Mitra ambil $\rightarrow$ Customer tracking).
4. Ubah `passes: true` pada `CUS-04`, `CUS-06`, `CUS-07`, `CUS-10`, `MIT-06`, `MIT-07`, `MIT-08`, `SYS-08` di `features.json`.
5. Perbarui status Slice S4 di `docs/progress.md`.

## 7. Instruksi Rencana Dulu (Plan First)
Keluarkan rencana implementasi komprehensif sebelum membuat kode.
```
