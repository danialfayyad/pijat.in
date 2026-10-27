# Kontrak Mesin Status Pesanan (Order Status Machine)

Dokumen ini mendefinisikan seluruh status pesanan, transisi yang sah, aktor pemicu, efek finansial ke dompet/saldo, dan pemicu notifikasi berdasarkan **BRD Bagian 4.6, 4.7, 4.8, dan Lampiran A**.

---

## 1. Daftar Status Pesanan (BRD 4.6)

| Kode Status | Nama Status | Penjelasan Singkat | Sifat Status |
|---|---|---|---|
| `MENUNGGU_PEMBAYARAN` | Menunggu Pembayaran | Saldo dompet kurang dan customer sedang top-up via Xendit (maks. 15 menit). | Transisional |
| `MENCARI_MITRA` | Mencari Mitra | Instan: ditawarkan berurutan (60s/mitra, batas 30m). Terjadwal: tampil di menu Pesanan Terjadwal. | Transisional |
| `DIJADWALKAN` | Dijadwalkan | Mitra telah menerima/mengambil pesanan. Alamat lengkap terbuka bagi mitra. | Aktif |
| `MENUJU_LOKASI` | Menuju Lokasi | Mitra menekan OTW. Customer terkunci tidak dapat membatalkan/reschedule biasa. | Aktif |
| `TIBA` | Tiba | Mitra tiba di lokasi (validasi radius 50 m). Waktu tunggu maksimal 15 menit. | Aktif |
| `SEDANG_BERLANGSUNG`| Sedang Berlangsung | Mitra menekan Mulai. Timer hitung mundur durasi paket berjalan. | Aktif |
| `SELESAI` | Selesai | Mitra menekan Selesai setelah durasi penuh. Rating & jendela aduan 24 jam dibuka. | Status Akhir (*Terminal*) |
| `DIBATALKAN` | Dibatalkan | Pesanan dibatalkan (oleh customer, mitra, admin, atau sistem otomatis). | Status Akhir (*Terminal*) |
| `KEDALUWARSA` | Kedaluwarsa | Pembayaran top-up kekurangan tidak diselesaikan dalam 15 menit. | Status Akhir (*Terminal*) |

---

## 2. Diagram & Matriks Transisi Status

```text
[*] --> MENUNGGU_PEMBAYARAN : Saldo kurang saat order
[*] --> MENCARI_MITRA       : Saldo dompet cukup (Dana Ditahan)

MENUNGGU_PEMBAYARAN --> MENCARI_MITRA : Top-up berhasil (Sistem/Webhook)
MENUNGGU_PEMBAYARAN --> KEDALUWARSA   : Batas 15 menit habis (Sistem)

MENCARI_MITRA --> DIJADWALKAN : Mitra menerima / mengambil pesanan
MENCARI_MITRA --> DIBATALKAN  : Batas pencarian habis / Customer batal

DIJADWALKAN --> MENUJU_LOKASI : Mitra menekan OTW
DIJADWALKAN --> MENCARI_MITRA : Reschedule oleh customer / Mitra membatalkan
DIJADWALKAN --> DIBATALKAN    : Customer batal (< 3 jam atau instan) / Admin membatalkan

MENUJU_LOKASI --> TIBA        : Mitra menekan Tiba (radius <= 50m)
MENUJU_LOKASI --> DIBATALKAN  : Customer batal karena mitra telat > 30 menit / Admin

TIBA --> SEDANG_BERLANGSUNG   : Mitra menekan Mulai
TIBA --> DIBATALKAN           : Customer no-show / Gender tidak sesuai / Medis tidak sesuai

SEDANG_BERLANGSUNG --> SELESAI : Durasi paket terpenuhi & Mitra menekan Selesai
SEDANG_BERLANGSUNG --> DIBATALKAN : Intervensi Admin (Insiden / Kasus Darurat)
```

---

## 3. Rincian Transisi, Pemicu, Efek Finansial, & Notifikasi

### T-01: Inisiasi Pesanan
- **Status Asal**: `[START]` $\rightarrow$ `MENUNGGU_PEMBAYARAN`
- **Pemicu**: Customer mengonfirmasi pesanan saat saldo dompet kurang dari total tarif.
- **Efek Dana**: Tidak ada dana yang ditahan. Tagihan top-up Xendit dibuat selama 15 menit.
- **Efek Notifikasi**: Notifikasi tagihan top-up ke Customer.

### T-02: Top-Up Sukses (Masuk Pencarian)
- **Status Asal**: `MENUNGGU_PEMBAYARAN` $\rightarrow$ `MENCARI_MITRA`
- **Pemicu**: Webhook Xendit konfirmasi top-up berhasil dalam 15 menit.
- **Efek Dana**: Saldo dompet customer bertambah, kemudian nominal penuh tarif pesanan berstatus **Ditahan** (*Escrow*).
- **Efek Notifikasi**: Notifikasi top-up berhasil ke Customer. Notifikasi tawaran instan (prioritas tinggi) atau notifikasi pesanan terjadwal baru ke Mitra terkait.

### T-03: Kedaluwarsa Pembayaran
- **Status Asal**: `MENUNGGU_PEMBAYARAN` $\rightarrow$ `KEDALUWARSA`
- **Pemicu**: Sistem otomatis (timeout 15 menit).
- **Efek Dana**: Tidak ada pengembalian karena tidak ada saldo yang ditahan.
- **Efek Notifikasi**: Notifikasi pesanan kedaluwarsa ke Customer.

### T-04: Penerimaan Pesanan
- **Status Asal**: `MENCARI_MITRA` $\rightarrow$ `DIJADWALKAN`
- **Pemicu**: Mitra menekan "Terima" (instan) atau "Ambil" (terjadwal).
- **Efek Dana**: Dana tetap ditahan di dompet customer.
- **Efek Notifikasi**: Notifikasi "Mitra Ditugaskan" dikirim ke Customer.

### T-05: Mitra Membatalkan Pasca-Menerima
- **Status Asal**: `DIJADWALKAN` $\rightarrow$ `MENCARI_MITRA` (dilempar ulang)
- **Pemicu**: Mitra menekan "Batalkan Pesanan".
- **Efek Dana**: Dana customer tetap ditahan.
- **Sanksi & Aturan**: Mitra dikenai SP (BRD 4.11). Pesanan instan dilempar ke mitra berikutnya dalam sisa batas waktu 30 menit awal; pesanan terjadwal muncul kembali di bursa terjadwal.
- **Efek Notifikasi**: Notifikasi pembatalan mitra & pencarian ulang ke Customer.

### T-06: Customer Batal saat Mencari Mitra
- **Status Asal**: `MENCARI_MITRA` $\rightarrow$ `DIBATALKAN`
- **Pemicu**: Customer menekan "Batalkan".
- **Efek Dana**: Pengembalian dana **100%** dilepas seketika kembali ke Saldo Tersedia Customer.
- **Sanksi**: Tanpa sanksi SP.
- **Efek Notifikasi**: Notifikasi pembatalan & pengembalian dana ke Customer.

### T-07: Batas Waktu Pencarian Habis (Tidak Ada Mitra)
- **Status Asal**: `MENCARI_MITRA` $\rightarrow$ `DIBATALKAN`
- **Pemicu**: Sistem otomatis (30 menit untuk instan; 24 jam / H-2 jam untuk terjadwal).
- **Efek Dana**: Pengembalian dana **100%** otomatis ke Saldo Tersedia Customer.
- **Sanksi**: Tanpa sanksi.
- **Efek Notifikasi**: Notifikasi "Pesanan tidak mendapat mitra" & pengembalian dana ke Customer.

### T-08: Reschedule Pesanan Terjadwal
- **Status Asal**: `DIJADWALKAN` $\rightarrow$ `MENCARI_MITRA`
- **Pemicu**: Customer memilih jadwal baru (hanya boleh jika $\ge$ 3 jam sebelum jadwal lama, maks. 2x).
- **Efek Dana**: Dana customer tetap ditahan.
- **Efek Notifikasi**: Notifikasi reschedule ke mitra lama; notifikasi pesanan terjadwal baru ke kandidat mitra baru.

### T-09: Customer Batal Terjadwal $\ge$ 3 Jam Sebelum Layanan
- **Status Asal**: `DIJADWALKAN` $\rightarrow$ `DIBATALKAN`
- **Pemicu**: Customer membatalkan pesanan terjadwal $\ge$ 3 jam sebelum jadwal.
- **Efek Dana**: Pengembalian dana **100%** ke Saldo Tersedia Customer.
- **Sanksi**: Tanpa sanksi SP.
- **Efek Notifikasi**: Notifikasi pembatalan ke Customer dan Mitra terkait.

### T-10: Customer Batal Terjadwal < 3 Jam Sebelum Layanan (Sebelum OTW)
- **Status Asal**: `DIJADWALKAN` $\rightarrow$ `DIBATALKAN`
- **Pemicu**: Customer membatalkan dengan konfirmasi peringatan potongan 50%.
- **Efek Dana**: 
  - **50%** dikembalikan ke Saldo Tersedia Customer.
  - **50%** potongan dibagi sesuai persentase komisi: bagian mitra masuk ke saldo Tertahan mitra (kompensasi waktu), bagian platform masuk ke pendapatan komisi platform.
- **Sanksi**: Tanpa sanksi SP.
- **Efek Notifikasi**: Notifikasi pembatalan beserta rincian potongan ke Customer dan Mitra.

### T-11: Customer Batal Pesanan Instan (Sebelum OTW)
- **Status Asal**: `DIJADWALKAN` $\rightarrow$ `DIBATALKAN`
- **Pemicu**: Customer membatalkan pesanan instan setelah mitra menerima tetapi sebelum OTW.
- **Efek Dana**: Pengembalian dana **100%** ke Saldo Tersedia Customer (mitra belum bergerak).
- **Sanksi**: Tanpa sanksi SP.
- **Efek Notifikasi**: Notifikasi pembatalan ke Customer dan Mitra.

### T-12: Mitra Menuju Lokasi
- **Status Asal**: `DIJADWALKAN` $\rightarrow$ `MENUJU_LOKASI`
- **Pemicu**: Mitra menekan tombol "OTW".
- **Efek Dana**: Dana tetap ditahan.
- **Efek Notifikasi**: Notifikasi "Mitra Menuju Lokasi" ke Customer; GPS tracking interval 30 detik aktif. Customer terkunci tidak dapat membatalkan/reschedule.

### T-13: Pembatalan Luar Biasa karena Keterlambatan Mitra
- **Status Asal**: `MENUJU_LOKASI` $\rightarrow$ `DIBATALKAN`
- **Pemicu**: Customer menekan batalkan jika mitra belum menekan Tiba melewati estimasi kedatangan + 30 menit.
- **Efek Dana**: Pengembalian dana **100%** ke Saldo Tersedia Customer.
- **Sanksi**: Mitra dikenai sanksi **SP** (BRD 4.7 & 4.11).
- **Efek Notifikasi**: Notifikasi pembatalan & penerbitan SP ke Mitra; notifikasi refund ke Customer.

### T-14: Mitra Tiba di Lokasi
- **Status Asal**: `MENUJU_LOKASI` $\rightarrow$ `TIBA`
- **Pemicu**: Mitra menekan tombol "Tiba" (validasi posisi $\le$ 50 m dari titik alamat layanan).
- **Efek Dana**: Dana tetap ditahan. Timer waktu tunggu mitra 15 menit dimulai.
- **Efek Notifikasi**: Notifikasi "Mitra Tiba" ke Customer.

### T-15: Pembatalan Customer No-Show
- **Status Asal**: `TIBA` $\rightarrow$ `DIBATALKAN`
- **Pemicu**: Mitra menekan "Customer Tidak Ada" setelah menunggu 15 menit dan mencoba menghubungi via chat.
- **Efek Dana**: Potongan 50% (50% dikembalikan ke customer; 50% dibagi untuk mitra dan platform sesuai komisi).
- **Sanksi**: Customer dikenai sanksi **SP** (BRD 4.7 & 4.11).
- **Efek Notifikasi**: Notifikasi pembatalan dan penerbitan SP ke Customer; notifikasi kompensasi ke Mitra.

### T-16: Pembatalan Gender Tidak Sesuai
- **Status Asal**: `TIBA` $\rightarrow$ `DIBATALKAN`
- **Pemicu**: Mitra menekan "Gender penerima tidak sesuai" setelah tiba.
- **Efek Dana**: Potongan 50% (50% dikembalikan ke customer; 50% dibagi untuk mitra dan platform sesuai komisi).
- **Sanksi**: Customer dikenai sanksi **SP**; Mitra **Bebas Sanksi**.
- **Efek Notifikasi**: Notifikasi pembatalan dan penerbitan SP ke Customer; notifikasi kompensasi ke Mitra. Kasus dicatat di Tinjauan Mitra.

### T-17: Pembatalan Kondisi Medis Tidak Sesuai
- **Status Asal**: `TIBA` $\rightarrow$ `DIBATALKAN`
- **Pemicu**: Mitra menekan "Kondisi medis tidak sesuai" dengan alasan wajib tercatat.
- **Efek Dana**: Pengembalian dana **100%** ke Customer.
- **Sanksi**: Mitra **Bebas Sanksi** (BRD 4.7 & 4.13).
- **Efek Notifikasi**: Notifikasi pembatalan & pengembalian dana 100% ke Customer.

### T-18: Memulai Layanan
- **Status Asal**: `TIBA` $\rightarrow$ `SEDANG_BERLANGSUNG`
- **Pemicu**: Mitra menekan "Mulai".
- **Efek Dana**: Dana tetap ditahan. Timer sesi layanan berjalan di perangkat Mitra dan Customer.
- **Efek Notifikasi**: Notifikasi "Layanan Dimulai" ke Customer.

### T-19: Menyelesaikan Layanan
- **Status Asal**: `SEDANG_BERLANGSUNG` $\rightarrow$ `SELESAI`
- **Pemicu**: Mitra menekan "Selesai" (tombol hanya aktif setelah durasi paket terpenuhi 100%).
- **Efek Dana**: 
  - Dana ditahan customer resmi **Dilepas**.
  - Bagian mitra dicatat dan masuk ke **Saldo Tertahan Mitra** selama 24 jam jendela aduan.
  - Bagian platform dicatat sebagai **Pendapatan Komisi Platform**.
- **Efek Notifikasi**: Notifikasi "Layanan Selesai" & pembukaan form rating ke Customer dan Mitra.

### T-20: Pembatalan oleh Admin (Pesanan Macet / Pelanggaran)
- **Status Asal**: `MENCARI_MITRA` / `DIJADWALKAN` / `MENUJU_LOKASI` / `TIBA` / `SEDANG_BERLANGSUNG` $\rightarrow$ `DIBATALKAN`
- **Pemicu**: Admin wilayah pesanan menekan "Batalkan Pesanan" dengan alasan wajib.
- **Efek Dana**: Pengembalian dana **100%** ke Customer.
- **Sanksi**: Sesuai investigasi admin (dapat menerbitkan SP jika ada pelanggaran).
- **Efek Notifikasi**: Notifikasi pembatalan dari Admin ke Customer dan Mitra.
