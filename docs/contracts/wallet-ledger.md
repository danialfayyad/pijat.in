# Kontrak Dompet, Saldo, & Buku Besar Transaksi (Wallet Ledger)

Dokumen ini mendefinisikan aturan integritas finansial, saldo customer, saldo mitra, pembagian komisi, penahanan dana, rekonsiliasi payment gateway (Xendit), dan pemisahan atribusi wilayah transaksi berdasarkan **BRD Bagian 4.7, 4.9, 4.10, 4.16, ADM-11, SA-04, dan SYS-05**.

---

## 1. Prinsip Utama Integritas Finansial

1. **Prinsip Append-Only (Buku Besar Mutasi)**:
   - Tabel mutasi finansial (`wallet_transactions` dan `partner_balance_transactions`) bersifat **hanya-tambah** (*append-only*).
   - Setiap modifikasi saldo **DILARANG KERAS** menggunakan operasi SQL `UPDATE` atau `DELETE` pada baris mutasi lama. Koreksi dilakukan dengan memasukkan baris mutasi penyesuaian (*adjustment entry*).
2. **Pencegahan Saldo Negatif**:
   - Saldo dompet customer dan saldo mitra diproteksi di tingkat database dengan *Check Constraint*: `CHECK (balance >= 0)`.
   - Seluruh mutasi saldo wajib dieksekusi di dalam blok transaksi database atomik (`DB::transaction`) dengan penguncian baris pesimis (`SELECT ... FOR UPDATE`).
3. **Tipe Data Uang**:
   - Seluruh nilai moneter wajib menggunakan tipe data `DECIMAL(14, 2)` (bukan `FLOAT` atau `DOUBLE`) untuk mencegah galat pembulatan desimal.
4. **Prasyarat Alamat Utama Sebelum Transaksi Dompet (Solusi B.2)**:
   - Customer **wajib** memiliki minimal 1 alamat tersimpan (yang otomatis berstatus Alamat Utama dan menetapkan `region_id` akun) sebelum diizinkan melakukan *Top-up* maupun *Penarikan Saldo*.
   - Tidak boleh ada transaksi dompet dengan `region_id IS NULL`.

---

## 2. Struktur Dompet Customer (Customer Wallet)

### 2.1 Komponen Saldo
- `wallet_balance`: Saldo bersih yang dimiliki customer.
- `held_balance`: Bagian dari `wallet_balance` yang sedang berstatus **Ditahan** (*escrow*) untuk pesanan aktif yang belum selesai.
- `available_balance`: Saldo bebas yang dapat digunakan untuk membuat pesanan baru atau ditarik.
  $$\text{available\_balance} = \text{wallet\_balance} - \text{held\_balance}$$

### 2.2 Alur Pergerakan Dana Customer
1. **Top-Up via Xendit**:
   - Customer melakukan top-up melalui payment gateway Xendit.
   - Setelah webhook Xendit terverifikasi valid, `wallet_balance` bertambah, baris mutasi `TOPUP` tercatat dengan atribusi wilayah akun customer saat itu (`account_region_id`).
2. **Penahanan Dana (Escrow)**:
   - Saat pesanan berstatus `MENCARI_MITRA`, `held_balance` bertambah sebesar nominal tarif pesanan. `available_balance` berkurang, tetapi `wallet_balance` total belum berubah.
3. **Pelepasan Dana (Order Selesai)**:
   - Saat status menjadi `SELESAI`, `held_balance` berkurang dan `wallet_balance` berkurang sebesar tarif pesanan. Dana dilepas ke pos komisi platform dan saldo mitra.
4. **Pengembalian Dana (Refund)**:
   - Jika pesanan dibatalkan dengan pengembalian 100%: `held_balance` berkurang dan dana kembali utuh ke `available_balance`.
   - Jika pesanan dibatalkan dengan potongan 50%: `held_balance` berkurang penuh; 50% kembali ke `available_balance` customer, 50% sisanya dipotong dari `wallet_balance` dan didistribusikan ke kompensasi mitra dan komisi platform.

---

## 3. Struktur Saldo Mitra (Partner Balance)

### 3.1 Komponen Saldo Mitra
- `pending_balance` (Saldo Tertahan): Pendapatan dari pesanan yang telah `SELESAI`, tetapi masih berada dalam jendela aduan 24 jam.
- `available_balance` (Saldo Tersedia): Saldo yang telah melewati 24 jam tanpa aduan valid dan siap ditarik ke rekening bank.

### 3.2 Pembagian Komisi & Pelepasan Saldo
1. **Perhitungan Komisi Pesanan Selesai (BRD 4.10)**:
   - Komisi Platform = $\text{Total Tarif} \times \text{Persentase Komisi}$ (default 20%).
   - Pendapatan Mitra = $\text{Total Tarif} - \text{Komisi Platform}$ (default 80%).
   - Persentase komisi dibekukan (*snapshot*) pada baris pesanan saat pesanan dibuat; perubahan parameter global tidak berlaku mundur.
2. **Masa Tahan 24 Jam**:
   - Pendapatan mitra langsung masuk ke `pending_balance`.
   - Sistem otomatis (Cron job / Scheduled Task `SYS-03`) memindahkan dana dari `pending_balance` ke `available_balance` tepat setelah 24 jam berlalu sejak waktu pesanan `SELESAI`.
3. **Penahanan karena Aduan**:
   - Jika customer mengajukan aduan dalam 24 jam dan aduan terbukti valid, pendapatan yang berstatus `pending_balance` dibatalkan (ditarik kembali untuk refund customer).

---

## 4. Penarikan Saldo (Disbursement / Withdrawal)

1. **Penarikan Mitra (MIT-16)**:
   - Hanya dapat ditarik dari `available_balance` ke rekening bank terverifikasi atas nama mitra yang sama dengan KTP.
   - Penarikan ditolak/ditahan bila: akun sedang berstatus `Ditangguhkan` (SP2/investigasi), atau rekening sedang dalam proses verifikasi ulang perubahan data.
   - Nominal di atas ambang peninjauan manual wajib disetujui Admin sebelum diinstruksikan ke API *Disbursement* Xendit.
2. **Penarikan Customer (CUS-17, Should)**:
   - Hanya dapat ditarik ke rekening terverifikasi atas nama yang sama dengan nama akun customer.

---

## 5. Arsitektur Atribusi Wilayah Transaksi (Solusi B.2)

Untuk memastikan konsistensi antara **Laporan Keuangan Admin Wilayah (ADM-11)** dan **Monitoring Saldo Mengendap Super Admin (SA-04)** tanpa selisih rekonsiliasi:

```text
┌────────────────────────────────────────────────────────────────────────┐
│                        SETTLEMENT XENDIT PUSAT                         │
│                    (Dipantau Global oleh Super Admin)                  │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
          ┌─────────────────────────┴─────────────────────────┐
          ▼                                                   ▼
┌───────────────────────────────────┐       ┌───────────────────────────────────┐
│     TRANSAKSI AKUN WILAYAH A      │       │    TRANSAKSI PESANAN WILAYAH B    │
│  (Region = Wilayah Akun Saat Itu) │       │   (Region = Alamat Fisik Layanan) │
├───────────────────────────────────┤       ├───────────────────────────────────┤
│ • Top-up Dompet Customer          │       │ • Pembayaran Pesanan Selesai      │
│ • Penarikan Saldo Customer        │       │ • Komisi Platform dari Pesanan    │
│ • Pencairan Saldo Mitra           │       │ • Pendapatan Mitra dari Pesanan   │
│                                   │       │ • Refund & Potongan Pembatalan    │
└───────────────────────────────────┘       └───────────────────────────────────┘
```

### 5.1 Aturan Atribusi Wilayah per Tipe Transaksi
Setiap baris transaksi mutasi keuangan wajib mencatat `region_id` permanen yang tidak dapat diubah:

| Tipe Transaksi | Penentuan `region_id` yang Dicatat | Keterangan Bisnis |
|---|---|---|
| `TOPUP` | `customer.primary_address.region_id` | Wilayah domisili pemilik saldo. |
| `WITHDRAWAL_CUSTOMER` | `customer.primary_address.region_id` | Wilayah domisili pemilik rekening. |
| `WITHDRAWAL_MITRA` | `mitra.verified_domicile.region_id` | Wilayah domisili mitra penarik dana. |
| `ORDER_PAYMENT` | `order.service_address.region_id` | Wilayah tempat layanan terjadi. |
| `ORDER_REFUND` | `order.service_address.region_id` | Mengikuti wilayah pesanan asal. |
| `PLATFORM_COMMISSION` | `order.service_address.region_id` | Pendapatan operasional wilayah pesanan. |
| `PARTNER_EARNING` | `order.service_address.region_id` | Beban layanan di wilayah pesanan. |

### 5.2 Buku Besar Mutasi Wilayah Admin (ADM-11)
Admin Wilayah menyaring data keuangan berdasarkan `region_id == admin_assigned_region`:
- **Arus Kas Masuk Wilayah**: Total Top-Up customer wilayah tersebut + Total Komisi Platform dari pesanan di wilayah tersebut.
- **Arus Kas Keluar Wilayah**: Total Penarikan customer wilayah tersebut + Total Pencairan mitra wilayah tersebut + Total Refund pesanan di wilayah tersebut.
- **Rekonsiliasi Transaksional Xendit**: Admin mencocokkan status `reference_id` Xendit khusus untuk transaksi dengan `region_id` wilayah tugasnya.

### 5.3 Posisi Saldo Mengendap & Settlement Global (SA-04)
Super Admin memantau posisi dana mengendap secara terpusat:
- **Total Saldo Mengendap Customer**: 
  $$\sum \text{wallet\_balance customer aktif dikelompokkan berdasarkan wilayah domisili saat ini}.$$
- **Total Settlement Xendit Global**: Rekonsiliasi total dana yang masuk ke rekening penampung bank Xendit terhadap seluruh kewajiban saldo dompet aktif.
