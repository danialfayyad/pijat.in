# Kontrak Model Data & Arsitektur Database (Data Model)

Dokumen ini mendefinisikan entitas, atribut utama, relasi, indeks performa, dan strategi penegakan aturan (*constraints vs application transactions*) untuk MySQL 8.0.16+ (InnoDB, `utf8mb4`). Dokumen ini bukan skema SQL lengkap, melainkan panduan arsitektur model data.

---

## 1. Aturan Teknis Database Wajib

1. **Integritas Spasial**:
   - Seluruh kolom koordinat titik (`Point`) dan poligon wilayah (`Polygon`) wajib menyertakan **SRID 4326** eksplisit (WGS 84): `coordinate POINT NOT NULL /*!80003 SRID 4326 */`.
   - Indeks spasial menggunakan `SPATIAL INDEX`.
2. **Presisi Moneter**:
   - Seluruh kolom nominal uang wajib bertipe `DECIMAL(14, 2)`. Dilarang menggunakan tipe *floating-point*.
3. **Pemisahan Permanen Wilayah**:
   - Kolom `region_id` pada tabel `orders`, `wallet_transactions`, dan `partner_balance_transactions` dicatat permanen saat kejadian (*immutable snapshot*) dan tidak boleh terikat secara dinamis ke wilayah akun saat ini.
4. **Prinsip Imutabilitas Ledger Transaksi**:
   - Tabel `wallet_transactions` dan `partner_balance_transactions` bersifat *append-only*.
5. **Riwayat Status Pesanan Lengkap**:
   - Setiap mutasi status pesanan wajib dicatat ke tabel `order_status_histories` dengan status asal, status tujuan, aktor pemicu, alasan, dan cap waktu.

---

## 2. BAGIAN 1: Tabel Fondasi (Dibekukan di Awal / F0)

Tabel berikut menjadi fondasi sistem dan **DIBEKUKAN**. Perubahan pada tabel ini mengharuskan agen berhenti dan melapor ke pimpinan proyek.

### 2.1 Modul Akun & Peran (Auth & Identity)
- **`users`**:
  - *Atribut Utama*: `id` (ULID/BIGINT UNSIGNED), `name`, `email` (UNIQUE), `phone_number` (UNIQUE), `gender` (`MALE`/`FEMALE`), `password_hash`, `role` (`CUSTOMER`, `MITRA`, `ADMIN`, `SUPER_ADMIN`), `status` (`BELUM_LENGKAP`, `MENUNGGU_VERIFIKASI`, `TERVERIFIKASI`, `DITOLAK`, `DITANGGUHKAN`, `DIBLOKIR`), `device_id`, `email_verified_at`, `phone_verified_at`, `created_at`, `updated_at`.
  - *Indeks*: `INDEX(email)`, `INDEX(phone_number)`, `INDEX(role, status)`.

### 2.2 Modul Wilayah & Penugasan Admin (Geographic & Admin Scoping)
- **`regions`**:
  - *Atribut Utama*: `id`, `name`, `boundaries` (`POLYGON SRID 4326 NOT NULL`), `timezone` (`Asia/Jakarta`, dll), `status` (`AKTIF`, `NONAKTIF`), `created_at`, `updated_at`.
  - *Indeks*: `SPATIAL INDEX(boundaries)`, `INDEX(status)`.
- **`admin_region_assignments`**:
  - *Atribut Utama*: `id`, `admin_user_id` (FK `users.id`), `region_id` (FK `regions.id`), `can_handle_emergency` (BOOLEAN default FALSE), `created_at`.
  - *Indeks*: `UNIQUE(admin_user_id, region_id)`, `INDEX(region_id, can_handle_emergency)`.
- **`admin_menu_permissions`**:
  - *Atribut Utama*: `id`, `admin_user_id` (FK `users.id`), `menu_code` (VARCHAR: `DASHBOARD`, `VERIFIKASI_MITRA`, `PESANAN`, `AKUN`, `SP`, `BANDING`, `ADUAN`, `RATING`, `KATALOG`, `KEUANGAN`), `created_at`.
  - *Indeks*: `UNIQUE(admin_user_id, menu_code)`.

### 2.3 Modul Pesanan & Riwayat Status (Orders Core)
- **`orders`**:
  - *Atribut Utama*: `id`, `order_code` (VARCHAR UNIQUE), `order_type` (`INSTAN`, `TERJADWAL`), `customer_id` (FK `users.id`), `mitra_id` (FK `users.id` NULLABLE), `region_id` (FK `regions.id`, *wilayah fisik layanan*), `service_id` (BIGINT UNSIGNED), `duration_minutes` (INT), `total_price` (DECIMAL(14,2)), `commission_percent` (DECIMAL(5,2)), `recipient_name` (VARCHAR), `recipient_gender` (`MALE`/`FEMALE`), `service_address_text` (TEXT), `service_coordinate` (`POINT SRID 4326 NOT NULL`), `scheduled_start_time` (TIMESTAMP NULLABLE), `status` (ENUM sesuai Kontrak Status), `cancellation_reason` (TEXT NULLABLE), `reschedule_count` (TINYINT default 0), `created_at`, `updated_at`.
  - *Indeks*: `UNIQUE(order_code)`, `INDEX(customer_id, status)`, `INDEX(mitra_id, status)`, `INDEX(region_id, status)`, `SPATIAL INDEX(service_coordinate)`, `INDEX(scheduled_start_time)`.
- **`order_status_histories`**:
  - *Atribut Utama*: `id`, `order_id` (FK `orders.id`), `from_status` (VARCHAR), `to_status` (VARCHAR), `actor_id` (FK `users.id` NULLABLE), `actor_role` (VARCHAR), `note` (TEXT NULLABLE), `created_at`.
  - *Indeks*: `INDEX(order_id, created_at)`.

### 2.4 Modul Dompet & Buku Besar Finansial (Wallets & Ledger)
- **`customer_wallets`**:
  - *Atribut Utama*: `id`, `customer_id` (FK `users.id` UNIQUE), `wallet_balance` (DECIMAL(14,2) default 0.00), `held_balance` (DECIMAL(14,2) default 0.00), `updated_at`.
  - *Constraint*: `CHECK (wallet_balance >= 0)`, `CHECK (held_balance >= 0)`, `CHECK (wallet_balance >= held_balance)`.
- **`partner_balances`**:
  - *Atribut Utama*: `id`, `mitra_id` (FK `users.id` UNIQUE), `pending_balance` (DECIMAL(14,2) default 0.00), `available_balance` (DECIMAL(14,2) default 0.00), `updated_at`.
  - *Constraint*: `CHECK (pending_balance >= 0)`, `CHECK (available_balance >= 0)`.
- **`wallet_transactions` (Append-Only Customer Ledger)**:
  - *Atribut Utama*: `id`, `wallet_id` (FK `customer_wallets.id`), `order_id` (FK `orders.id` NULLABLE), `region_id` (FK `regions.id`, *atribusi wilayah permanen*), `transaction_type` (`TOPUP`, `PAYMENT_HOLD`, `PAYMENT_RELEASE`, `REFUND`, `WITHDRAWAL`), `amount` (DECIMAL(14,2)), `balance_after` (DECIMAL(14,2)), `xendit_reference_id` (VARCHAR NULLABLE), `status` (`SUCCESS`, `PENDING`, `FAILED`), `created_at`.
  - *Indeks*: `INDEX(wallet_id, created_at)`, `INDEX(region_id, created_at)`, `INDEX(xendit_reference_id)`.
- **`partner_balance_transactions` (Append-Only Mitra Ledger)**:
  - *Atribut Utama*: `id`, `partner_balance_id` (FK `partner_balances.id`), `order_id` (FK `orders.id` NULLABLE), `region_id` (FK `regions.id`, *atribusi wilayah permanen*), `transaction_type` (`EARNING_PENDING`, `EARNING_RELEASED`, `EARNING_CANCELLED`, `WITHDRAWAL`), `amount` (DECIMAL(14,2)), `pending_after` (DECIMAL(14,2)), `available_after` (DECIMAL(14,2)), `xendit_disbursement_id` (VARCHAR NULLABLE), `created_at`.
  - *Indeks*: `INDEX(partner_balance_id, created_at)`, `INDEX(region_id, created_at)`.

---

## 3. BAGIAN 2: Tabel yang Dibuat per Slice (Lewat Migrasi Bertahap)

Tabel berikut diimplementasikan secara bertahap saat slice terkait dikerjakan:

- **Slice S1 (Admin & Wilayah)**: `audit_logs` (pencatatan perubahan data, parameter, dan wilayah).
- **Slice S2 (Onboarding Mitra)**: `partner_profiles` (NIK terenkripsi, foto KTP, foto selfie, status verifikasi), `bank_accounts` (nomor rekening terenkripsi, bukti kepemilikan, nama pemilik), `compliance_agreements` (pencatatan versi teks SOP dan persetujuan kemitraan).
- **Slice S3 (Customer & Alamat)**: `customer_addresses` (label, detail, patokan, pin koordinat `POINT SRID 4326`, `region_id`, `is_primary`), `service_catalog` (nama layanan, durasi pilihan, harga paket, `is_active`).
- **Slice S4 & S5 (Pemesanan & Matching)**: `order_medical_notes` (catatan medis sensitif terenkripsi, retensi 30 hari), `instant_order_dispatches` (log penawaran berurutan 60 detik per mitra).
- **Slice S6 (Pelaksanaan & Review)**: `order_chats` (pesan teks antar pengguna, retensi 30 hari), `reviews` (bintang 1–5 dari customer ke mitra dan sebaliknya).
- **Slice S7 (Refund & Reschedule)**: `reschedule_histories` (catatan waktu lama dan baru).
- **Slice S8 (Aduan, Darurat, & Sanksi)**: `complaints` (kategori, bukti, investigasi admin), `emergency_alerts` (log penekanan darurat, admin penanggung jawab, waktu eskalasi), `sanctions_sp` (tingkat SP1/SP2/SP3, alasan, bukti, masa berlaku), `appeals` (surat pernyataan, putusan admin, log audit).
- **Slice S9 (Penarikan Dana)**: `withdrawal_requests` (permohonan penarikan, verifikasi manual, referensi payout Xendit).
- **Slice S10 (Perpindahan Domisili)**: `partner_relocation_requests` (alamat baru, wilayah tujuan, bukti, verifikasi admin tujuan).

---

## 4. Matriks Penegakan Aturan (Constraint Database vs Transaksi Aplikasi)

| Aturan Bisnis | Tempat Penegakan | Mekanisme Teknis Penegakan |
|---|---|---|
| **Saldo Tidak Boleh Negatif** | **Database Constraint** | `CHECK (wallet_balance >= 0)` dan `CHECK (available_balance >= 0)`. |
| **Pemesanan Diambil Tepat 1 Mitra (Anti-Race Condition)** | **Database & Transaksi** | `SELECT * FROM orders WHERE id = ? FOR UPDATE` di dalam `DB::transaction`, disertai validasi status `MENCARI_MITRA`. Jika sudah berubah, lempar *Conflict Exception*. |
| **Batas Wilayah Tidak Tumpang Tindih** | **Database & Transaksi** | Validasi `ST_Intersects` sebelum insert/update poligon wilayah; tabel wilayah divalidasi via *Spatial Query*. |
| **Bentrok Jadwal Mitra (+60 Menit Jeda)** | **Transaksi Aplikasi** | Pengecekan interval bentrok di server: $\text{jadwal\_baru} < \text{jadwal\_lama} + \text{durasi} + 60\text{m}$. |
| **Strict Sesama Gender** | **Transaksi Aplikasi** | Validasi keras di server: `mitra.gender === order.recipient_gender` sebelum tawaran dikirim/diambil. |
| **Radius Kandidat 5 km & Tiba 50 m** | **Database Spatial Query** | Fungsi MySQL `ST_Distance_Sphere(coordinate_a, coordinate_b) <= 5000` (atau `50`). |
| **Imutabilitas Ledger Finansial** | **Aplikasi & Database Role** | Hanya query `INSERT` yang diizinkan pada tabel mutasi. Di level database produksi, hak `UPDATE` dan `DELETE` dicabut dari user database aplikasi pada tabel ledger. |
