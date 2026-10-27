# Kontrak Matriks Otorisasi & Wilayah Tugas (AuthZ Matrix)

Dokumen ini mendefinisikan pembagian hak akses peran, menu, pembatasan wilayah tugas di sisi server, serta aturan penanganan kasus lintas wilayah berdasarkan **BRD Bagian 4.16, 5.3, 5.4, 7.3, dan SYS-12**.

---

## 1. Definisi Peran (*Roles*)
1. `CUSTOMER`: Pengguna aplikasi pemesan layanan (Web PWA).
2. `MITRA`: Terapis mandiri penyedia layanan (Web PWA).
3. `ADMIN`: Operator operasional wilayah (Web Desktop). Aksesnya terikat pada kombinasi: **(Menu yang Diizinkan) $\times$ (Wilayah Tugas yang Ditetapkan)**.
4. `SUPER_ADMIN`: Pengelola sistem terpusat (Web Desktop). Memiliki akses global tanpa batasan wilayah ke seluruh menu Admin dan menu khusus Super Admin.

---

## 2. Matriks Akses Peran $\times$ Menu $\times$ Wilayah

| Menu / Modul | Peran yang Sah | Batasan Wilayah | Keterangan Izin Khusus |
|---|---|---|---|
| **Beranda & Profil Customer** | Customer | Wilayah Akun (Alamat Utama) | Mengikuti CUS-01 s.d. CUS-23. |
| **Pemesanan & Dompet Customer** | Customer | Wilayah Alamat Layanan Terpilih | Pemesanan boleh di wilayah aktif mana saja. |
| **Beranda & Jadwal Mitra** | Mitra | Wilayah Domisili Mitra | Hanya pesanan di wilayah domisilinya. |
| **Saldo & Profil Mitra** | Mitra | Wilayah Domisili Mitra | Mengikuti MIT-01 s.d. MIT-22. |
| **Admin: Dashboard** | Admin, Super Admin | Hanya Wilayah Tugas Admin | SA melihat seluruh / filter wilayah. |
| **Admin: Verifikasi Mitra** | Admin, Super Admin | Wilayah Domisili Mitra | Pindah domisili diverifikasi wilayah tujuan. |
| **Admin: Verifikasi Rekening** | Admin, Super Admin | Wilayah Akun Pemilik Rekening | Rekening customer/mitra. |
| **Admin: Pemantauan Pesanan** | Admin, Super Admin | Wilayah Alamat Pesanan | Pembatalan pesanan macet tanpa penugasan manual. |
| **Admin: Kelola Akun Mitra & Customer** | Admin, Super Admin | Wilayah Akun Saat Ini | Blokir perangkat/HP, suspend, aktifkan. |
| **Admin: SP dan Sanksi** | Admin, Super Admin | Wilayah Akun / Wilayah Pesanan | Penerbitan SP manual/otomatis. |
| **Admin: Banding SP** | Admin, Super Admin | Wilayah Akun Saat Banding | Ditinjau admin wilayah akun pengguna. |
| **Admin: Laporan Aduan** | Admin, Super Admin | Wilayah Alamat Pesanan | Ditinjau admin wilayah terjadinya pesanan. |
| **Admin: Tinjauan Mitra & Rating** | Admin, Super Admin | Wilayah Domisili Mitra | Mitra berating rendah di wilayah tugas. |
| **Admin: Keuangan Wilayah** | Admin (Izin Keuangan), Super Admin | Hanya Wilayah Tugas Admin | Memerlukan izin `KEUANGAN` dari Super Admin. |
| **Admin: Siaga Tombol Darurat** | Admin (Izin Siaga), Super Admin | Wilayah Alamat Pesanan | Memerlukan izin `SIAGA_DARURAT` per wilayah. |
| **Super Admin: Kelola Wilayah** | Super Admin | Global | Buat/ubah batas poligon, zona waktu, status. |
| **Super Admin: Kelola Pengguna Admin**| Super Admin | Global | Atur menu, wilayah tugas, siaga, katalog. |
| **Super Admin: Log Audit** | Super Admin | Global | Rekam jejak siapa mengubah apa dan kapan. |
| **Super Admin: Pantau Dana Mengendap**| Super Admin | Global & Agregasi per Wilayah | Rekonsiliasi settlement terpusat Xendit. |
| **Super Admin: Kelola Konten & SOP** | Super Admin | Global | Versi syarat ketentuan, perjanjian kemitraan. |
| **Katalog Layanan (Global)** | Admin (Izin Katalog), Super Admin | Global (Seluruh Wilayah) | Harga & durasi sama di seluruh wilayah. |

---

## 3. Aturan Wilayah Tugas Admin

1. **Prinsip Isolasi Data**:
   - Admin hanya dapat membaca (*read*) dan mengubah (*write*) data operasional yang terkait langsung dengan **Wilayah Tugas** yang diberikan Super Admin.
   - Admin dengan lebih dari satu wilayah tugas melihat data gabungan dari wilayah tugasnya dan disediakan filter *dropdown* pemilih wilayah (ADM-15).
   - Admin tanpa wilayah tugas sama sekali tidak dapat melihat data operasional apa pun (layar kosong/pesan tidak memiliki wilayah tugas).
2. **Kekosongan Admin & Proteksi Wilayah Aktif**:
   - Wilayah baru hanya dapat diaktifkan jika memiliki minimal 1 akun Admin dan minimal 1 Admin Siaga Darurat (SA-06, 4.18).
   - Sistem menolak pencabutan Admin terakhir atau izin Siaga terakhir pada wilayah yang berstatus `Aktif` (SA-02, 6.6).
3. **Super Admin sebagai Admin Pusat**:
   - Super Admin otomatis mewarisi hak akses ke seluruh wilayah dan dapat mengintervensi wilayah yang sedang tidak memiliki admin aktif (SA-07).

---

## 4. Aturan Penanganan Kasus Lintas Wilayah (*Cross-Region Cases*)

Sesuai BRD 4.16 paragraf 7 & 422, bila entitas pengguna berasal dari wilayah yang berbeda dengan lokasi pesanan:

1. **Aturan Pesanan, Aduan, & Tombol Darurat**:
   - Dikelola sepenuhnya oleh **Admin Wilayah Pesanan** (tempat layanan fisik dilakukan).
   - *Contoh*: Customer asal Jakarta memesan layanan saat menginap di Bandung. Pesanan, aduan, insiden darurat, dan pemantauan ditangani oleh Admin Wilayah Bandung.
2. **Akses Data Terbatas untuk Kasus Lintas Wilayah**:
   - Admin Wilayah Pesanan (Bandung) diberikan hak akses sementara membaca profil akun customer Jakarta tersebut **hanya sebatas yang relevan untuk penyelesaian pesanan/kasus tersebut** (nama, kontak, riwayat kasus terkait). Admin Bandung tidak dapat melihat seluruh riwayat pesanan customer yang terjadi di wilayah Jakarta.
3. **Penerbitan Sanksi Lintas Wilayah**:
   - Admin Wilayah Pesanan berhak menerbitkan sanksi/SP kepada customer atau mitra yang melanggar pada pesanan di wilayahnya.
4. **Banding SP Pengguna**:
   - Berkas banding SP yang diajukan oleh pengguna masuk ke antrean **Admin Wilayah Akun Pengguna saat pengajuan** (bukan admin wilayah pesanan), agar relasi pembinaan akun tetap terpusat pada domisili pengguna.

---

## 5. Daftar Tempat Pembatasan yang Wajib Diberlakukan di Server

Sesuai **NFR 7.3, SYS-12, dan BRD 4.16**, pembatasan wilayah **HARUS** diterapkan di lapisan server (Laravel Middleware & Eloquent Query Scopes), bukan sekadar penyaringan di antarmuka frontend (React).

1. **Query Scope Otomatis pada Model Operasional**:
   - Setiap *query* data pada entitas: `Order`, `MitraProfile`, `PartnerVerification`, `Complaint`, `EmergencyAlert`, `Review`, dan `Transaction` wajib menginjeksi filter `WHERE region_id IN (:admin_assigned_regions)`.
2. **API Endpoint Route Guard**:
   - Middleware `VerifyAdminRegionAccess`: Memeriksa apakah `order_id`, `partner_id`, atau `ticket_id` yang diakses berada di dalam daftar `assigned_regions` milik admin yang sedang login. Mengembalikan response `403 Forbidden` jika di luar hak wilayah tugasnya.
3. **Ekspor Laporan (.xlsx & .pdf)**:
   - Generator ekspor laporan Keuangan (ADM-11) dan Operasional (ADM-01) wajib mengambil parameter wilayah tugas dari sesi server, bukan dari parameter request client yang dapat dimanipulasi.
4. **WebSocket / Server-Sent Events (SSE) Room Scoping**:
   - Notifikasi darurat dan live status pesanan hanya disiarkan ke *channel/room* `region-{region_id}`. Token koneksi admin hanya diizinkan *subscribe* ke channel wilayah tugasnya.
5. **Aksi Mutasi Bersifat Terisolasi**:
   - Aksi menyetujui verifikasi mitra (ADM-02), membatalkan pesanan macet (ADM-04), dan memutus aduan (ADM-08) memvalidasi kepemilikan wilayah entitas target sebelum *database update* dilakukan.
