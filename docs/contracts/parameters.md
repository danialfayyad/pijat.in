# Kontrak Parameter Sistem yang Dapat Diatur (System Parameters)

Dokumen ini mendefinisikan seluruh parameter dinamis yang dapat dikonfigurasi oleh Super Admin tanpa perlu mengubah kode aplikasi berdasarkan **BRD Bagian 8 dan SA-01**.

---

## 1. Aturan Pengelolaan Parameter

1. **Cakupan Global**:
   - Seluruh parameter pada dokumen ini berlaku seragam (*global*) di seluruh wilayah aktif.
   - Pengecualian: Batas geografis poligon, status aktif/nonaktif, dan zona waktu dikelola spesifik per entitas wilayah di menu Master Wilayah (SA-06).
2. **Tidak Berlaku Mundur (*Non-Retroactive*)**:
   - Perubahan nilai parameter (misalnya persentase komisi atau denda pembatalan) hanya berlaku untuk transaksi/pesanan baru yang dibuat setelah waktu perubahan disimpan. Pesanan atau transaksi lama tetap mengacu pada nilai *snapshot* saat pesanan dibuat.
3. **Log Audit Wajib (SA-03)**:
   - Setiap modifikasi nilai parameter wajib dicatat dalam log audit: ID Super Admin pengubah, kunci parameter, nilai lama, nilai baru, alasan perubahan, dan cap waktu (*timestamp*).

---

## 2. Tabel Parameter dan Nilai Awal

| Kelompok | Kunci Parameter (*Key*) | Tipe Data | Nilai Awal Default | Satuan / Format | Keterangan & Rujukan BRD |
|---|---|---|---|---|---|
| **Jam Operasional** | `operational_start_time` | String | `08:00` | Format `HH:mm` | Jam mulai layanan waktu setempat wilayah (BRD 4.4, 8). |
| **Jam Operasional** | `operational_end_time` | String | `21:00` | Format `HH:mm` | Jam tutup layanan waktu setempat wilayah (BRD 4.4, 8). |
| **Penugasan** | `dispatch_max_radius_km` | Integer | `5` | Kilometer | Radius garis lurus pencarian kandidat mitra (BRD 4.5, 8). |
| **Penugasan** | `instant_offer_timeout_seconds` | Integer | `60` | Detik | Batas waktu respon mitra atas tawaran instan (BRD 4.5, 8). |
| **Penugasan** | `instant_search_timeout_minutes`| Integer | `30` | Menit | Batas waktu maksimal pencarian mitra instan (BRD 4.4, 8). |
| **Penugasan** | `scheduled_search_timeout_hours`| Integer | `24` | Jam | Batas waktu pencarian terjadwal sejak dipesan (BRD 4.4, 8). |
| **Penugasan** | `scheduled_search_deadline_before_hours`| Integer | `2` | Jam | Batas pencarian terjadwal sebelum waktu layanan (BRD 4.4, 8). |
| **Penugasan** | `scheduled_min_advance_hours` | Integer | `3` | Jam | Batas minimal waktu pemesanan terjadwal di awal (BRD 4.4, 8). |
| **Penugasan** | `scheduled_max_advance_days` | Integer | `7` | Hari | Batas maksimal pemesanan terjadwal ke depan (BRD 4.4, 8). |
| **Penugasan** | `partner_order_interval_buffer_minutes`| Integer | `60` | Menit | Jeda istirahat/perjalanan antar pesanan mitra (BRD 4.5, 8). |
| **Pesanan** | `customer_max_active_orders` | Integer | `3` | Pesanan | Batas maksimal pesanan aktif per customer (BRD 4.4, 8). |
| **Pesanan** | `order_max_reschedule_count` | Integer | `2` | Kali | Batas maksimal reschedule per pesanan terjadwal (BRD 4.7, 8). |
| **Pesanan** | `reschedule_min_advance_hours`| Integer | `3` | Jam | Batas minimal pengajuan reschedule sebelum jadwal (BRD 4.7, 8). |
| **Lokasi** | `partner_online_location_interval_minutes`| Integer | `5` | Menit | Interval pengiriman lokasi mitra saat Online (BRD 4.15, 8). |
| **Lokasi** | `partner_location_stale_timeout_minutes` | Integer | `10` | Menit | Batas kedaluwarsa lokasi mitra untuk instan (BRD 4.5, 8). |
| **Lokasi** | `partner_otw_location_interval_seconds` | Integer | `30` | Detik | Interval update lokasi mitra saat OTW (BRD 4.8, 8). |
| **Lokasi** | `arrival_geofence_radius_meters`| Integer | `50` | Meter | Radius validasi tombol Tiba di lokasi (BRD 4.8, 8). |
| **Lokasi** | `arrival_gps_accuracy_tolerance_meters` | Integer | `150` | Meter | Batas toleransi deviasi akurasi sinyal GPS (BRD 4.8, 8). |
| **Waktu Tunggu** | `payment_expiry_timeout_minutes` | Integer | `15` | Menit | Batas waktu pembayaran top-up kekurangan (BRD 4.6, 8). |
| **Waktu Tunggu** | `customer_noshow_wait_minutes` | Integer | `15` | Menit | Waktu tunggu mitra sebelum aksi customer tidak ada (BRD 4.8, 8). |
| **Waktu Tunggu** | `partner_late_arrival_threshold_minutes`| Integer | `30` | Menit | Keterlambatan mitra dari estimasi sebelum customer boleh batal (BRD 4.6, 8). |
| **Waktu Tunggu** | `complaint_window_hours` | Integer | `24` | Jam | Jendela waktu pengajuan aduan setelah selesai (BRD 4.12, 8). |
| **Waktu Tunggu** | `emergency_first_response_sla_minutes` | Integer | `5` | Menit | Target SLA respon pertama tombol darurat (BRD 4.12, 8). |
| **Waktu Tunggu** | `emergency_escalation_timeout_minutes` | Integer | `2` | Menit | Waktu eskalasi darurat ke Super Admin (BRD 4.16, 8). |
| **Waktu Tunggu** | `partner_verification_sla_hours`| Integer | `24` | Jam | SLA penyelesaian verifikasi mitra oleh admin (BRD 4.1, 8). |
| **Finansial** | `platform_commission_percent` | Decimal | `20.00`| Persen (%) | Persentase potongan komisi platform dari pesanan (BRD 4.10, 8). |
| **Finansial** | `late_cancel_penalty_percent` | Decimal | `50.00`| Persen (%) | Potongan pembatalan terjadwal < 3 jam (BRD 4.7, 8). |
| **Finansial** | `customer_wallet_min_topup` | Decimal | `20000.00`| IDR (Rupiah) | Nominal minimal transaksi top-up dompet (BRD 4.9, 8). |
| **Finansial** | `customer_wallet_max_balance` | Decimal | `2000000.00`| IDR (Rupiah) | Batas maksimal penampungan saldo dompet (BRD 4.9, 8). |
| **Finansial** | `customer_wallet_daily_topup_limit` | Decimal | `5000000.00`| IDR (Rupiah) | Batas akumulasi top-up harian customer (BRD 4.9, 8). |
| **Finansial** | `min_withdrawal_amount` | Decimal | `50000.00`| IDR (Rupiah) | Batas minimal penarikan saldo (BRD 4.9, 4.10, 8). |
| **Finansial** | `withdrawal_fee_amount` | Decimal | `2500.00`| IDR (Rupiah) | Biaya admin per transaksi penarikan (BRD 4.9, 4.10, 8). |
| **Finansial** | `withdrawal_manual_review_threshold` | Decimal | `1000000.00`| IDR (Rupiah) | Ambang batas penarikan wajib tinjauan admin (BRD 4.9, 4.10, 8). |
| **Finansial** | `partner_earning_hold_period_hours` | Integer | `24` | Jam | Masa tahan pendapatan mitra sebelum jadi Tersedia (BRD 4.10, 8). |
| **Sanksi** | `sp_expiry_months` | Integer | `6` | Bulan | Masa berlaku masa aktif surat peringatan (BRD 4.11, 8). |
| **Sanksi** | `customer_sp2_suspension_days` | Integer | `3` | Hari | Durasi penangguhan akun customer terkena SP2 (BRD 4.11, 8). |
| **Sanksi** | `partner_appeal_retry_cooldown_days` | Integer | `7` | Hari | Jeda pengajuan ulang banding jika ditolak (BRD 4.11, 8). |
| **Sanksi** | `sp1_sp3_appeal_deadline_days` | Integer | `3` | Hari | Batas waktu hak pengajuan banding SP1 dan SP3 (BRD 4.11, 8). |
| **Sanksi** | `low_rating_threshold` | Decimal | `4.00` | Skala Bintang | Ambang batas rating masuk Tinjauan Mitra (BRD 4.14, 8). |
| **Sanksi** | `low_rating_min_orders` | Integer | `10` | Pesanan | Jumlah minimal pesanan sebelum evaluasi rating (BRD 4.14, 8). |
| **Privasi Data** | `chat_and_medical_retention_days`| Integer | `30` | Hari | Retensi otomatis riwayat chat dan catatan medis (BRD 4.15, 8). |
