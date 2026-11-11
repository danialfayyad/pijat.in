# UI/UX Design System & Anti-Slop Cheat Sheet
> **Panduan Standar Desain Antarmuka Backoffice & Dashboard (Tailwind CSS)**  
> File ini dapat langsung disalin ke proyek lain atau dimasukkan ke dalam prompt AI agent (`AGENTS.md`, `CLAUDE.md`, `.cursorrules`) sebagai acuan baku desain.

---

## 1. Prinsip Utama (Anti-AI Slop Philosophy)

1. **Dilarang Menambahkan Teks Subtitle Klise / Filler**:
   - ❌ *Slop:* `<p>Menampilkan seluruh pesanan di bawah wilayah tugas operasional Anda.</p>`
   - ❌ *Slop:* `<p>Halaman ini digunakan untuk mengelola data akun pengguna pada sistem.</p>`
   - ✅ *Solusi:* Cukup gunakan judul yang tenang (`text-base font-bold text-slate-800`) dan counter data di kanan (`text-xs text-slate-400 font-medium 12 data`). Hapus semua teks penjelas yang menyatakan hal yang sudah jelas.

2. **Dilarang Menaruh Animasi & Titik Warna Hiasan yang Tidak Fungsional**:
   - ❌ *Slop:* Menaruh dot pulsing kuning/merah acak di dalam tab atau judul hanya agar terlihat "canggih".
   - ✅ *Solusi:* Gunakan badge angka bulat yang rapi (`px-1.5 py-0.5 rounded-full text-[10px] font-bold`) yang hanya muncul jika memang ada antrean data (misal: pesanan macet $> 0$).

3. **Gunakan Single-Form Toolbar (Jangan Bikin Form Terpisah-Pisah)**:
   - ❌ *Slop:* Input search ada di `<form>` sendiri, lalu dropdown filter di `<form>` lain dengan hidden input yang saling tumpuk.
   - ✅ *Solusi:* Satukan semua kontrol (Search, Dropdown Wilayah, Dropdown Status, Reset) dalam satu `<form method="GET">` dengan urutan baca alami (kiri ke kanan).

4. **Label Ringkas & Tidak Redundan**:
   - ❌ *Slop:* `Semua Pesanan`, `Pesanan Aktif`, `Pesanan Macet`, `Pesanan Selesai`.
   - ✅ *Solusi:* `Semua`, `Aktif`, `Macet`, `Selesai`, `Dibatalkan`.

---

## 2. Token Desain & Utility Class Tailwind Standar

### A. Palet Warna (Color System)
- **Background Utama Aplikasi**: `bg-slate-50 text-slate-800`
- **Surface / Card**: `bg-white border-slate-200/80`
- **Warna Aksen Utama (Brand)**:
  - Background aktif: `bg-emerald-600 hover:bg-emerald-700 text-white`
  - Teks aksen/link: `text-emerald-600 hover:text-emerald-700`
  - Ring/Focus: `focus:ring-emerald-500 focus:border-emerald-500`
- **Warna Status Indikator**:
  - Sukses / Selesai: `text-emerald-700 bg-emerald-50 border-emerald-200`
  - Perhatian / Menunggu: `text-amber-700 bg-amber-50 border-amber-200`
  - Berjalan / Aktif: `text-blue-700 bg-blue-50 border-blue-200`
  - Bahaya / Ditolak / Diblokir: `text-rose-700 bg-rose-50 border-rose-200`

### B. Skala Radius (Border Radius Hierarchy)
- **Kontrol Kecil (Input, Select, Button, Tab Pill)**: `rounded-xl`
- **Container / Card Tabel / Card Metrik**: `rounded-2xl`
- **Modal Dialog & Alert Popup**: `rounded-3xl`
- **Badge Counter / Avatar**: `rounded-full`

### C. Skala Bayangan (Shadows)
- **Card Kontainer**: `shadow-xs` (halus dan modern, jangan pakai `shadow-lg` atau `shadow-xl` untuk card biasa).
- **Elemen Tidak Aktif**: `shadow-2xs`
- **Modal Pop-up**: `shadow-2xl`

### D. Skala Tipografi
- **Judul Halaman Topbar**: `text-xl font-bold text-slate-800`
- **Judul Card Tabel**: `text-base font-bold text-slate-800`
- **Nilai Angka Metrik (KPI)**: `text-2xl font-bold text-slate-800`
- **Tab Navigasi Luar**: `text-xs font-bold`
- **Input & Dropdown Select**: `text-xs font-semibold`
- **Header Kolom Tabel (`<th>`)**: `text-[11px] font-bold uppercase tracking-wider text-slate-500`
- **Isi Kolom Tabel (`<td>`)**: `text-xs text-slate-600`
- **Badge / Counter Kecil**: `text-[10px] font-bold`

---

## 3. Pola Arsitektur Halaman Tabel Backoffice (2-Tier Pattern)

Pola ini diterapkan konsisten pada menu Manajemen Pengguna, Pesanan Wilayah, Poin & Sanksi, Banding Sanksi, dan Live Chat.

```html
<!-- TIER 1: Tab Filter Entitas / Peran Utama (Di Luar Card) -->
<div class="flex items-center gap-2 overflow-x-auto pb-0.5 scrollbar-none">
    <!-- Tab Aktif -->
    <a href="?role=" 
       class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap bg-emerald-600 text-white shadow-xs">
        Semua Peran
    </a>
    <!-- Tab Tidak Aktif -->
    <a href="?role=pelanggan" 
       class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs">
        Pelanggan
    </a>
    <a href="?role=mitra" 
       class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs">
        Mitra
    </a>
</div>

<!-- TIER 2: Card Tabel Utama -->
<div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
    
    <!-- Header Card: Judul Bersih + Single Toolbar Form -->
    <div class="p-5 border-b border-slate-100 space-y-3.5">
        <!-- Baris Atas: Judul Tenang & Counter -->
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-base">
                Daftar Pesanan Wilayah
            </h3>
            <span class="text-xs text-slate-400 font-medium">
                128 data
            </span>
        </div>

        <!-- Baris Bawah: Formulir Filter Terpadu (Single Form) -->
        <form method="GET" action="" class="flex flex-wrap items-center gap-3">
            <!-- Hidden Input Preservasi Tab Luar -->
            <input type="hidden" name="role" value="semua">

            <!-- 1. Input Pencarian -->
            <div class="relative w-56 sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="" 
                       placeholder="Cari kode, nama..." 
                       class="w-full text-xs border border-slate-200 rounded-xl pl-9 pr-3.5 py-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs placeholder:text-xs placeholder-slate-400 bg-white">
            </div>

            <!-- 2. Dropdown Wilayah (Jika multi-wilayah) -->
            <div class="flex items-center space-x-1.5 shrink-0">
                <label class="text-xs font-semibold text-slate-600 shrink-0">Wilayah:</label>
                <select name="region_id" onchange="this.form.submit()"
                        class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                    <option value="">Semua Wilayah</option>
                    <option value="1">Yogyakarta</option>
                </select>
            </div>

            <!-- 3. Dropdown Status (Atribut Data) -->
            <div class="flex items-center space-x-1.5 shrink-0">
                <label class="text-xs font-semibold text-slate-600 shrink-0">Status:</label>
                <select name="status" onchange="this.form.submit()"
                        class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                    <option value="semua">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>

            <!-- 4. Tombol Reset Filter [✕] (Muncul bila ada filter aktif) -->
            <a href="?" 
               title="Reset Filter"
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
        </form>
    </div>

    <!-- Tabel Data -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                <tr>
                    <th class="px-5 py-3.5">Kode / Identitas</th>
                    <th class="px-5 py-3.5">Nama</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="px-5 py-3.5 font-bold font-mono text-slate-800">TRX-001</td>
                    <td class="px-5 py-3.5 font-semibold text-slate-800">John Doe</td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Selesai</span>
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="#" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                            Detail &rarr;
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

---

## 4. Pola Kartu Ringkasan Metrik (KPI Summary Cards)

Gunakan grid 4-kolom yang ringkas tanpa card tumpuk berlebihan:

```html
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
        <div class="flex items-center justify-between text-slate-500">
            <span class="text-xs font-medium">Total Akun</span>
            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
        </div>
        <div class="text-2xl font-bold text-slate-800">1,240</div>
        <div class="text-[11px] text-slate-400">Total terdaftar aktif</div>
    </div>
</div>
```

---

## 5. Modal Konfirmasi Tindakan & Logout (Universal Modal Pattern)

Ciri khas modal bersih:
- Menggunakan `rounded-3xl` dengan padding `p-6 sm:p-7`.
- Ikon melingkar di tengah dengan cincin aksen halus (`ring-8 ring-amber-50` atau `ring-8 ring-rose-50`).
- Dua tombol aksi berjarak rata (`grid grid-cols-2 gap-2.5 mt-5`).
- Backdrop gelap dengan efek blur lembut: `bg-slate-900/60 backdrop-blur-xs`.

```html
<!-- Cuplikan Tombol Aksi di Modal -->
<div class="grid grid-cols-2 gap-2.5 mt-5">
    <button type="button" class="w-full py-2.5 px-3 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200/80 rounded-xl transition">
        Batal
    </button>
    <button type="button" class="w-full py-2.5 px-3 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition">
        Ya, Lanjutkan
    </button>
</div>
```

---

## 6. Pengelompokan Sidebar Navigasi (4 Kategori Logis)

Untuk mencegah daftar menu menjadi "tembok link yang menumpuk", bagi menu backoffice ke dalam 4 kategori dengan label uppercase kecil (`text-[10px] font-bold uppercase tracking-wider text-white/80`):

1. **Operasional Harian / Platform**:
   - Dashboard
   - Monitoring Pesanan / Antrean
   - Notifikasi & Penugasan
2. **Mitra & Pengguna**:
   - Verifikasi Mitra / Akun
   - Manajemen Pengguna
   - Poin & Sanksi / Banding
3. **Pusat Bantuan & Komplain**:
   - Live Chat Bantuan
   - Laporan Aduan / Tiket
4. **Keuangan & Konfigurasi**:
   - Keuangan / Settlement
   - Katalog Layanan / Parameter Sistem

---

## 7. Prompt Cepat untuk Disalin ke AI Proyek Baru

Kamu cukup copy-paste instruksi berikut ke instruksi AI di proyek lain:

```markdown
PANDUAN GAYA DESAIN UI (ANTI-SLOP BACKOFFICE):
1. Dilarang membuat teks penjelas/subtitle klise pada card tabel (contoh: "Menampilkan data..."). Cukup judul tenang (text-base font-bold) dan counter jumlah data.
2. Gunakan single-form toolbar pada tabel: Input pencarian (w-56 sm:w-64) disusul dropdown filter (py-2 rounded-xl text-xs) dan tombol reset [✕] di baris yang sama.
3. Tab navigasi luar khusus untuk entitas peran/tipe utama dengan styling: px-4 py-2 rounded-xl text-xs font-bold.
4. Container card wajib menggunakan: bg-white rounded-2xl border border-slate-200/80 shadow-xs.
5. Modal popup wajib menggunakan: rounded-3xl p-6 bg-white border border-slate-200/90 shadow-2xl dengan backdrop-blur-xs.
6. Hindari animasi hiasan (pulsing dot) yang tidak memiliki arti status fungsional; gunakan badge angka bersih (text-[10px] font-bold).
```

