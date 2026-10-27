# Pijat In - Panduan Agen Koding (CLAUDE.md)

## 1. Gambaran Produk
Pijat In (v2.2) adalah platform marketplace on-demand layanan pijat ke rumah yang mempertemukan customer dengan mitra terapis mandiri secara realtime tanpa perantara admin. Menjamin keamanan dan kenyamanan melalui penegakan ketat layanan sesama gender, isolasi operasional berbasis batas poligon wilayah, pembayaran dompet tertutup (closed-loop) via Xendit, serta sistem reputasi, sanksi SP, dan tombol darurat.

## 2. Stack Teknologi & Arsitektur
- **Backend**: Laravel 11/12 (PHP 8.3+) REST API & WebSocket/SSE.
- **Database**: MySQL 8.0.16+ (InnoDB, utf8mb4, SRID 4326 untuk Geometri Spasial).
- **Frontend**: Vite + React PWA (Customer & Mitra PWA mobile-first; Admin & Super Admin Desktop).
- **Integrasi**: Xendit (Payment Gateway & Payout), Leaflet + OpenStreetMap (Peta), Email SMTP (OTP), Web Push API VAPID (Push Notification).
- **Testing**: Pest PHP (Unit & Feature Test), Playwright (End-to-End Multi-Role).
- **Arsitektur Repositori**: Monorepo.

## 3. Struktur Direktori
```text
├── app/                  # Laravel Backend Core (Models, Controllers, Services)
├── bootstrap/            # Laravel Bootstrap
├── config/               # Konfigurasi Aplikasi & Service
├── database/             # Migrasi, Factory, & Seeder
│   └── migrations/       # Migrasi MySQL berurutan (Fondasi & Slice)
├── docs/                 # Kontrak Arsitektur, Spesifikasi, & Rencana Slice
│   └── contracts/        # Kontrak Bersama yang Dibekukan (FONDASI)
├── frontend/             # Monorepo Frontend (Vite + React)
│   ├── apps/
│   │   ├── customer-pwa/ # PWA Customer (Mobile-first)
│   │   ├── mitra-pwa/    # PWA Mitra (Mobile-first, ramah RAM 2-3GB)
│   │   └── admin-web/    # Web Desktop Admin & Super Admin
│   └── packages/         # Shared UI & API Client
├── tests/                # Uji Backend (Pest PHP)
├── e2e/                  # Uji Alur End-to-End (Playwright)
├── features.json         # Single Source of Truth Kebutuhan Fungsional
└── CLAUDE.md             # Panduan Utama Agen Koding
```

## 4. Perintah Operasional
- **Install & Setup**: `composer install && cd frontend && npm install`
- **Build Frontend**: `cd frontend && npm run build`
- **Jalankan Dev Server**: `php artisan serve` dan `cd frontend && npm run dev`
- **Migrasi Database**: `php artisan migrate`
- **Linter & Formatter**: `vendor/bin/pint --format agent` (PHP) dan `npm run lint` (JS/React)
- **Unit & Feature Test**: `php artisan test --compact` atau `vendor/bin/pest`
- **End-to-End Test**: `npx playwright test`

## 5. Konvensi Penamaan & Commit
- **PHP**: PascalCase untuk Class/Enum, camelCase untuk method/variabel, snake_case untuk kolom database.
- **TypeScript/React**: PascalCase untuk Component/Type, camelCase untuk fungsi/hook, kebab-case untuk nama berkas.
- **Git Commit**: Mengikuti Conventional Commits: `<tipe>(<slice-id>): <deskripsi singkat>` (contoh: `feat(S2): implementasi verifikasi KTP mitra`).

## 6. Aturan Lintas Modul yang TIDAK Boleh Dilanggar
1. **Strict Sesama Gender [BRD 4.3]**: Layanan hanya boleh antara mitra dan penerima layanan berjenis kelamin sama. Jenis kelamin melekat pada penerima fisik, bukan pemesan.
2. **Isolasi Wilayah & Tanpa Pesanan Lintas Wilayah [BRD 4.16]**: Mitra hanya melayani pesanan di wilayah domisilinya. Alamat di luar wilayah aktif ditolak.
3. **Otorisasi Server-Side Admin [BRD 4.16, 5.3, SYS-12]**: Seluruh data operasional Admin dikunci di server berdasarkan wilayah tugas. Jangan hanya mengandalkan filter frontend.
4. **Dompet Tertutup & Saldo Tidak Negatif [BRD 4.9, SYS-05]**: Saldo tidak boleh negatif (`balance >= 0` check constraint). Top-up wajib mensyaratkan alamat utama tersimpan.
5. **Ledger Append-Only [BRD 4.10, 4.16]**: Catatan mutasi dompet hanya boleh di-INSERT, tidak boleh di-UPDATE atau di-DELETE. Wilayah pesanan dan wilayah akun dicatat permanen saat kejadian.
6. **Masa Tahan Pendapatan Mitra [BRD 4.10]**: Pendapatan pesanan selesai berstatus `Tertahan` selama 24 jam jendela aduan sebelum berubah menjadi `Tersedia`.
7. **Durasi & Harga Tetap [BRD 4.2]**: Durasi paket dan harga tidak dapat diubah di tempat. Tidak ada fitur add-on pada versi 2.
8. **Disiplin Sanksi SP [BRD 4.11]**: Pemicu sanksi otomatis dicatat di audit log. Pembatalan karena kondisi medis atau gender tidak sesuai adalah pengecualian yang bebas SP mitra.

## 7. Aturan Kerja Agen Koding
- **Satu Sesi, Satu Slice**: Dilarang mengerjakan lebih dari 1 slice dalam satu sesi.
- **Rencana Dulu**: Awali setiap sesi dengan membuat rencana implementasi sebelum menyentuh kode.
- **Kontrak adalah Hukum**: Dilarang mengubah file di `docs/contracts/` tanpa berhenti dan melapor ke pimpinan proyek/pengguna.
- **Perubahan Database**: Skema hanya dibuat/diubah lewat file migrasi baru yang modular. Migrasi yang sudah diterapkan DILARANG diedit. Perubahan pada tabel fondasi mengharuskan agen berhenti dan melapor.
- **Integritas Kriteria**: Dilarang menghapus atau melemahkan kriteria penerimaan di `features.json`.
- **Verifikasi Sebelum Lolos**: Nilai `passes: true` hanya boleh disetel setelah seluruh tes unit, fitur, dan skenario E2E diverifikasi lulus.

## 8. Ritual Awal Sesi
1. Buka dan baca `docs/progress.md` untuk mengetahui slice aktif dan catatan sesi sebelumnya.
2. Periksa `features.json` untuk melihat kriteria penerimaan slice yang sedang dikerjakan.
3. Jalankan `git status` dan tes dasar (`php artisan test --compact`) untuk memastikan lingkungan kerja bersih dan hijau sebelum memulai.
