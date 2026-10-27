# Kontrak Aturan Wilayah & Geospasial (Region Rules)

Dokumen ini mendefinisikan aturan penentuan wilayah, validasi area layanan, penetapan wilayah akun dan pesanan, perpindahan domisili, dan pengelolaan wilayah nonaktif berdasarkan **BRD Bagian 4.16, 6.6, 6.7, CUS-05, CUS-23, MIT-02, MIT-22, SA-06, dan SYS-10**.

---

## 1. Definisi & Struktur Wilayah (Master Region)

Setiap entitas wilayah dikelola oleh Super Admin (SA-06) dengan atribut:
1. `name`: Nama wilayah (misal: "Kota Bandung", "Jakarta Selatan").
2. `boundaries`: Poligon batas geografis tertutup pada peta (tipe MySQL `Polygon` dengan koordinat lintang/bujur, SRID 4326).
3. `timezone`: Zona waktu operasional (`Asia/Jakarta`, `Asia/Makassar`, atau `Asia/Jayapura`). Digunakan untuk jam operasional layanan 08.00–21.00 setempat.
4. `status`: `Aktif` atau `Nonaktif`.

### 1.1 Integritas Batas Geografis (Tanpa Tumpang Tindih)
- Poligon batas antarwilayah **DILARANG BERIRISAN** (*no overlapping boundaries*).
- Setiap titik koordinat di bumi hanya boleh berada di dalam maksimal 1 poligon wilayah yang terdaftar.
- Validasi dilakukan di server menggunakan fungsi MySQL Spatial:
  `ST_Intersects(new_boundary, existing_boundary) = 0`.

---

## 2. Penentuan Wilayah Otomatis & Area Layanan

1. **Penentuan Wilayah Otomatis (SYS-10)**:
   - Pengguna (Customer maupun Mitra) **TIDAK PERNAH** memilih nama wilayah melalui *dropdown* secara manual.
   - Wilayah ditentukan secara mutlak oleh sistem di sisi server berdasarkan titik pin peta (koordinat `Point(latitude, longitude)`) menggunakan query spasial:
     ```sql
     SELECT id, name, timezone, status 
     FROM regions 
     WHERE ST_Contains(boundaries, ST_SRID(Point(longitude, latitude), 4326))
     LIMIT 1;
     ```
2. **Validasi Area Layanan**:
   - Area layanan resmi adalah gabungan seluruh wilayah yang berstatus `Aktif`.
   - Jika koordinat pin peta berada di luar seluruh poligon wilayah atau berada pada wilayah berstatus `Nonaktif`, sistem langsung menolak penyimpanan alamat dengan pesan yang jelas: *"Lokasi Anda berada di luar area layanan aktif Pijat In."*

---

## 3. Penetapan Wilayah Akun & Wilayah Pesanan

### 3.1 Wilayah Mitra (Partner Region)
- Ditentukan dari **alamat domisili terverifikasi** mitra (MIT-02).
- Wilayah ini membatasi secara ketat pesanan apa yang boleh diterima mitra: Mitra hanya dapat melihat dan ditawari pesanan yang beralamat di wilayah domisilinya.

### 3.2 Wilayah Customer (Customer Region)
- Ditentukan dari **Alamat Utama** customer (CUS-05, CUS-23).
- Alamat pertama yang disimpan customer otomatis menjadi Alamat Utama.
- Customer yang belum menyimpan alamat belum memiliki wilayah akun.

### 3.3 Wilayah Pesanan (Order Region)
- Ditentukan dari **alamat fisik layanan** tempat terapi pijat dilakukan saat pesanan dibuat.
- Wilayah pesanan bersifat **permanen** dan tidak pernah berubah meskipun customer atau mitra berpindah wilayah di kemudian hari.
- Wilayah pesanan menentukan admin mana yang berhak memantau, membatalkan pesanan macet, menerima notifikasi darurat, menangani aduan, dan mengelola pembukuan pesanan tersebut.

---

## 4. Aturan Tanpa Pesanan Lintas Wilayah (No Cross-Region Orders)

1. **Syarat Pencocokan Mitra [BRD 4.5, 4.16]**:
   $$\text{order.service\_address.region\_id} == \text{mitra.domicile\_address.region\_id}$$
2. **Mitra di Dekat Batas Wilayah**:
   - Meskipun seorang mitra berada dalam radius 5 km garis lurus dari lokasi pemesanan, jika lokasi pemesanan berada di seberang batas wilayah administratif lain, mitra tersebut **TIDAK BOLEH** ditugaskan atau melihat tawaran tersebut.
3. **Customer Bepergian**:
   - Customer boleh membuat pesanan di wilayah aktif mana pun di luar wilayah alamat utamanya (misal customer domisili Jakarta sedang berada di Surabaya). Pesanan tersebut dilayani oleh mitra Surabaya dan diawasi oleh Admin Surabaya.

---

## 5. Perpindahan Domisili (Relocation Rules)

### 5.1 Perpindahan Domisili Customer [BRD 4.16, 6.7, CUS-23]
- Customer dapat mengubah atau menetapkan Alamat Utama baru di menu Profil.
- Wilayah akun customer berpindah seketika (*instant switch*) setelah alamat baru yang valid di area aktif disimpan.
- Pesanan yang sedang berjalan tidak terpengaruh dan tetap terikat pada wilayah pesanan masing-masing.

### 5.2 Perpindahan Domisili Mitra [BRD 4.16, 6.7, MIT-22]
- Mitra mengajukan perubahan alamat domisili dari menu Profil dengan menyematkan pin peta baru.
- **Pengecekan Prasyarat**: Pengajuan ke wilayah lain hanya dapat dilakukan jika mitra **tidak memiliki pesanan aktif atau berstatus `DIJADWALKAN`**.
- **Kondisi Selama Proses Verifikasi**:
  - Akun mitra dibekukan dari aktivitas operasional: tidak dapat *Online*, tidak dapat menerima tawaran instan maupun mengambil pesanan terjadwal.
  - Penarikan saldo ditahan hingga verifikasi tuntas.
- **Pihak yang Memverifikasi**:
  - Berkas pengajuan masuk ke antrean **Admin Wilayah Tujuan**.
  - Jika disetujui: Wilayah mitra resmi berpindah ke wilayah baru, dan seluruh riwayat (rating, SP, aduan lama) tetap melekat pada profil mitra.
  - Jika ditolak: Mitra tetap berada di wilayah lama dengan alasan penolakan dan dapat mengajukan perbaikan data.
- **Akses Admin Pasca-Pindah**:
  - Admin wilayah baru dapat melihat seluruh riwayat akun mitra tersebut.
  - Admin wilayah lama tidak lagi melihat profil mitra tersebut di daftar aktifnya, namun tetap memiliki akses ke riwayat pesanan/aduan yang pernah terjadi di wilayah lamanya.

---

## 6. Pengelolaan Wilayah Nonaktif (Inactive Regions)

1. **Pengaktifan Wilayah Baru (SA-06, 4.16, 4.18)**:
   - Wilayah baru berstatus awal `Nonaktif`.
   - Super Admin hanya dapat mengaktifkan wilayah jika wilayah tersebut telah memiliki **minimal 1 akun Admin aktif** dan **minimal 1 Admin Siaga Darurat**.
2. **Dampak Penonaktifan Wilayah [BRD 4.16, 430]**:
   - Wilayah yang diubah menjadi `Nonaktif` seketika:
     - Tidak menerima pesanan baru (pembuatan pesanan ditolak sistem).
     - Seluruh mitra di wilayah tersebut tidak dapat menyalakan status *Online*.
     - Alamat di wilayah tersebut ditolak dalam pendaftaran akun baru.
   - **Perlakuan Pesanan Berjalan**:
     - Pesanan yang sudah berstatus `DIJADWALKAN`, `MENUJU_LOKASI`, `TIBA`, atau `SEDANG_BERLANGSUNG` tetap dilanjutkan sampai `SELESAI`.
   - **Aktivitas Admin Wilayah**:
     - Admin wilayah tetap dapat login untuk memproses penyelesaian aduan, banding SP, dan permohonan pencairan saldo yang tersisa.
