# Kontrak Matriks Notifikasi (Notification Matrix)

Dokumen ini mendefinisikan seluruh jenis notifikasi, pemicu peristiwa (*event trigger*), target penerima (berdasarkan peran dan partisi wilayah), prioritas, dan saluran transmisi berdasarkan **BRD Lampiran A, Bagian 4.12, 4.16, CUS-21, MIT-21, ADM-13, SA-07, dan SYS-06**.

---

## 1. Saluran Transmisi & Prioritas Notifikasi
- **Tinggi (High Priority)**: Transmisi via Web Push API (VAPID) dengan getar (*vibration*) dan efek suara peringatan (*sound*), serta modal *overlay* di aplikasi jika sedang terbuka.
- **Normal**: Transmisi via Web Push API dan pencatatan di kotak masuk notifikasi dalam aplikasi (*In-App Notification Center*).

---

## 2. Matriks Kejadian, Penerima, & Aturan Wilayah

| Kode Event | Kejadian (*Event Trigger*) | Penerima Notifikasi | Atribusi Wilayah Penerima | Prioritas | Saluran & Efek |
|---|---|---|---|---|---|
| `NOTIF_INSTANT_OFFER` | Tawaran pesanan instan baru (hitung mundur 60 detik) | Mitra kandidat terdekat | Wilayah domisili mitra == wilayah pesanan | **Tinggi** | Web Push + Suara + Getar + Modal hitung mundur 60s |
| `NOTIF_SCHEDULED_AVAILABLE` | Pesanan terjadwal baru tersedia di bursa | Seluruh mitra kandidat yang memenuhi syarat | Wilayah domisili mitra == wilayah pesanan | Normal | Web Push + Masuk menu Pesanan Terjadwal |
| `NOTIF_PARTNER_ASSIGNED` | Mitra menerima / mengambil pesanan | Customer pemesan | Akun Customer pemesan | Normal | Web Push + Update tampilan Pesanan Aktif |
| `NOTIF_PARTNER_OTW` | Mitra menekan OTW | Customer pemesan | Akun Customer pemesan | Normal | Web Push + Tampilan estimasi kedatangan |
| `NOTIF_PARTNER_ARRIVED` | Mitra menekan Tiba di lokasi | Customer pemesan | Akun Customer pemesan | Normal | Web Push + Timer tunggu 15 menit |
| `NOTIF_SERVICE_STARTED` | Mitra menekan Mulai layanan | Customer pemesan | Akun Customer pemesan | Normal | Web Push + Timer durasi sesi aktif |
| `NOTIF_SERVICE_COMPLETED` | Mitra menekan Selesai layanan | Customer pemesan & Mitra | Akun Customer & Akun Mitra | Normal | Web Push + Buka layar rating bintang |
| `NOTIF_ORDER_CANCELLED` | Pesanan dibatalkan (disertai alasan) | Pihak lawan yang dibatalkan | Akun pihak terkait | Normal | Web Push + Rincian alasan pembatalan |
| `NOTIF_ORDER_RESCHEDULED`| Customer melakukan reschedule jadwal | Mitra lama yang sebelumnya ditugaskan | Akun Mitra lama | Normal | Web Push + Rincian jadwal yang diubah |
| `NOTIF_ORDER_NO_PARTNER` | Pesanan instan/terjadwal tidak mendapat mitra hingga batas waktu | Customer pemesan | Akun Customer pemesan | Normal | Web Push + Info pengembalian dana 100% |
| `NOTIF_TOPUP_SUCCESS` | Top-up dompet berhasil dikonfirmasi Xendit | Customer pemesan | Akun Customer pemesan | Normal | Web Push + Update saldo seketika |
| `NOTIF_REFUND_PROCESSED` | Pengembalian dana pesanan selesai diproses | Customer pemesan | Akun Customer pemesan | Normal | Web Push + Saldo bertambah |
| `NOTIF_WITHDRAWAL_UPDATE`| Penarikan saldo diproses / ditolak | Customer atau Mitra penarik | Akun penarik | Normal | Web Push + Rincian mutasi rekening |
| `NOTIF_BALANCE_AVAILABLE`| Saldo Tertahan 24 jam berubah menjadi Saldo Tersedia | Mitra | Akun Mitra | Normal | Web Push + Saldo siap ditarik |
| `NOTIF_VERIFICATION_STATUS`| Berkas verifikasi disetujui atau ditolak (disertai alasan) | Mitra pendaftar | Akun Mitra | Normal | Web Push + Akses online dibuka/form revisi |
| `NOTIF_SP_ISSUED` | Penerbitan SP otomatis atau manual | Pihak penerima SP (Customer/Mitra) | Akun penerima SP | Normal | Web Push + Rincian alasan & masa berlaku |
| `NOTIF_APPEAL_RESULT` | Putusan atas banding SP disetujui atau ditolak | Pengaju banding | Akun pengaju | Normal | Web Push + Perubahan status akun |
| `NOTIF_SCHEDULE_REMINDER`| Pengingat jadwal layanan (H-1 jam sebelum sesi) | Customer dan Mitra | Akun terkait | Normal | Web Push pengingat persiapan |
| `NOTIF_CHAT_MESSAGE` | Pesan teks baru dalam sesi pesanan aktif | Customer atau Mitra lawan bicara | Akun penerima pesan | Normal | Web Push + Indikator pesan belum dibaca |
| `NOTIF_EMERGENCY_TRIGGER`| Tombol darurat ditekan selama sesi | Seluruh Admin Siaga wilayah pesanan | **Admin yang memiliki izin Siaga pada `order.region_id`** | **Tinggi** | Web Push + Sirine Peringatan + Tombol "Tangani Segera" (SLA 5 menit) |
| `NOTIF_EMERGENCY_ESCALATE`| Tombol darurat belum direspons admin siaga dalam waktu eskalasi (2 menit) | **Super Admin** | Pusat (Lintas Wilayah) | **Tinggi** | Web Push + Sirine Peringatan Eskalasi di Dashboard Super Admin |
| `NOTIF_ADMIN_QUEUE` | Berkas verifikasi baru, laporan aduan baru, atau banding SP baru | Admin wilayah terkait | **Admin yang bertugas di `region_id` terkait** | Normal | Notifikasi Admin Web Desktop + Badge antrean |

---

## 3. Aturan Pengiriman Notifikasi Menurut Wilayah Tugas

1. **Notifikasi Operasional Admin**:
   - Sistem **DILARANG** mengirimkan notifikasi antrean verifikasi, aduan, atau darurat secara *broadcast* ke seluruh admin.
   - Pengecekan dilakukan di server: `WHERE admin_region_assignments.region_id == event.target_region_id`.
2. **Kondisi Khusus Tombol Darurat (BRD 4.12, 4.16)**:
   - Tahap 1: Dikirimkan seketika ke seluruh Admin yang memiliki izin `SIAGA_DARURAT` pada wilayah terjadinya pesanan. Admin pertama yang menekan tombol *"Tangani"* mengunci penanganan kasus tersebut (status kasus menjadi `DIPROSES`), dan admin siaga lainnya melihat indikator penanggung jawab.
   - Tahap 2 (Eskalasi): Jika dalam tempo **2 menit** sejak tombol ditekan belum ada admin siaga yang menekan *"Tangani"*, sistem secara otomatis menyiarkan `NOTIF_EMERGENCY_ESCALATE` dengan prioritas tinggi ke seluruh akun **Super Admin**.
