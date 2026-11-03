# Kontrak Parameter Sistem yang Dapat Diatur (System Parameters)

Dokumen ini mendefinisikan parameter dinamis yang dapat dikonfigurasi oleh Super Admin tanpa perlu mengubah kode aplikasi berdasarkan **BRD Bagian 8 dan SA-01**.

---

## 1. Aturan Pengelolaan Parameter

1. **Cakupan Global**:
   - Seluruh parameter pada dokumen ini berlaku seragam (*global*) di seluruh wilayah aktif.
   - Pengecualian: Batas geografis poligon, status aktif/nonaktif, dan zona waktu dikelola spesifik per entitas wilayah di menu Master Wilayah (SA-06).
2. **Tidak Berlaku Mundur (*Non-Retroactive*)**:
   - Perubahan nilai parameter (misalnya persentase komisi atau batas penarikan) hanya berlaku untuk transaksi/pesanan baru yang dibuat setelah waktu perubahan disimpan.
3. **Log Audit Wajib (SA-03)**:
   - Setiap modifikasi nilai parameter wajib mencantumkan alasan perubahan dan otomatis dicatat dalam log audit: ID Super Admin pengubah, kunci parameter, nilai lama, nilai baru, alasan perubahan, dan cap waktu (*timestamp*).

---

## 2. Tabel Parameter Dinamis Superadmin (10 Parameter Inti)

Parameter berikut dapat diatur langsung oleh Super Admin melalui antarmuka **/superadmin/parameters**:

| Kelompok | Kunci Parameter (*Key*) | Tipe Data | Nilai Default | Satuan / Format | Keterangan & Rujukan BRD |
|---|---|---|---|---|---|
| **Keuangan & Bagi Hasil** | `platform_commission_percent` | Decimal | `20.00` | Persen (%) | Persentase potongan komisi platform dari tarif pesanan selesai (BRD 4.10, 8). |
| **Keuangan & Bagi Hasil** | `min_withdrawal_amount` | Decimal | `50000.00` | IDR (Rupiah) | Batas minimal penarikan saldo dompet mitra per pencairan (BRD 4.9, 4.10, 8). |
| **Keuangan & Bagi Hasil** | `max_withdrawal_amount` | Decimal | `5000000.00` | IDR (Rupiah) | Batas maksimal penarikan saldo dompet mitra per transaksi pencairan (BRD 4.9, 4.10, 8). |
| **Keuangan & Bagi Hasil** | `withdrawal_fee_amount` | Decimal | `2500.00` | IDR (Rupiah) | Biaya admin per transaksi transfer penarikan ke rekening bank (BRD 4.9, 4.10, 8). |
| **Keuangan & Bagi Hasil** | `customer_wallet_min_topup` | Decimal | `20000.00` | IDR (Rupiah) | Batas minimal pengisian ulang saldo dompet pelanggan (BRD 4.9, 8). |
| **Keuangan & Bagi Hasil** | `customer_wallet_max_balance` | Decimal | `2000000.00` | IDR (Rupiah) | Batas maksimal penampungan saldo dompet pelanggan (BRD 4.9, 8). |
| **Operasional & Jangkauan** | `operational_start_time` | String | `08:00` | Format `HH:mm` | Jam mulai pemesanan dan layanan waktu setempat wilayah (BRD 4.4, 8). |
| **Operasional & Jangkauan** | `operational_end_time` | String | `21:00` | Format `HH:mm` | Jam tutup pemesanan layanan waktu setempat wilayah (BRD 4.4, 8). |
| **Operasional & Jangkauan** | `dispatch_max_radius_km` | Integer | `5` | Kilometer | Radius garis lurus maksimal pencarian kandidat terapis (BRD 4.5, 8). |
| **Privasi Data (UU PDP)** | `chat_and_medical_retention_days` | Integer | `30` | Hari | Batas retensi penghapusan otomatis chat dan data medis sesuai UU PDP (BRD 4.15, 8). |

---

## 3. Parameter Teknis & Konstanta Baku (`config/pijatin.php`)

Parameter teknis perangkat keras (GPS), timer respons aplikasi klien, dan aturan operasional baku lainnya dikelola di `config/pijatin.php`:

- **Aturan Penarikan**: Seluruh penarikan dana mitra diverifikasi manual oleh admin.
- **Algoritma Penugasan**: `instant_offer_timeout_seconds` (60s), `instant_search_timeout_minutes` (30m), `scheduled_search_timeout_hours` (24h), `scheduled_min_advance_hours` (3h), `scheduled_max_advance_days` (7 hari), `partner_order_interval_buffer_minutes` (60m).
- **Aturan Pesanan**: `customer_max_active_orders` (3 pesanan), `order_max_reschedule_count` (2 kali), `reschedule_min_advance_hours` (3h).
- **Pelacakan GPS**: `partner_online_location_interval_minutes` (5m), `partner_otw_location_interval_seconds` (30s), `arrival_geofence_radius_meters` (50m), `arrival_gps_accuracy_tolerance_meters` (150m).
- **Klausul Baku Finansial**: `late_cancel_penalty_percent` (50%), `partner_earning_hold_period_hours` (24h).
- **Klausul Baku Sanksi**: `sp_expiry_months` (6 bulan), `customer_sp2_suspension_days` (3 hari), `partner_appeal_retry_cooldown_days` (7 hari), `sp1_sp3_appeal_deadline_days` (3 hari).
