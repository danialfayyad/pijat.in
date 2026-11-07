# Pijat.In — Definisi Aplikasi & Panduan Desain PWA Modern

Dokumen ini memuat definisi inti platform **Pijat.In** beserta 2 konsep arah desain antarmuka Progressive Web App (PWA) agar tampilan visual aplikasi tidak kaku, lebih luwes (*fluid*), modern, dan memiliki pengalaman pengguna (*UX*) kelas dunia.

---

## 1. Definisi & Arsitektur Produk

### 1.1 Apa itu Pijat.In?
> **Pijat.In** adalah platform marketplace *on-demand wellness & professional home-service massage* berbasis Progressive Web App (PWA) *mobile-first*. Platform ini menghubungkan **Pelanggan** yang membutuhkan layanan relaksasi/pemulihan kesehatan di rumah atau hotel dengan **Mitra Terapis Profesional** terdekat secara *real-time*.

### 1.2 Persona & Alur Pengguna Utama
1. **Pelanggan (*Customer*)**:
   - **Pemesanan Instan**: Algoritma *sequential dispatch* 60 detik mencari terapis online terdekat dalam radius $\le$ 5 km.
   - **Pemesanan Terjadwal**: Pesanan dipublikasikan ke Bursa Jadwal Mitra (radius $\le$ 10 km) dengan sistem FCFS (*First-Come, First-Served*).
   - **Pelacakan Live GPS**: Tampilan peta jalan nyata (*OSRM turn-by-turn routing*) dengan *draggable bottom sheet* ala Gojek/Grab.
   - **Dompet & Transaksi**: Pembayaran otomatis via saldo dompet atau tagihan QRIS/Xendit 15 menit.
   - **Komunikasi & Evaluasi**: In-app live chat dan penilaian bintang 1–5 pasca-layanan.

2. **Mitra Terapis (*Therapist*)**:
   - **Manajemen Kesiapan**: Toggle *Online/Offline* dengan validasi GPS dan *Screen Wake Lock*.
   - **Penerimaan Pesanan**: Notifikasi masuk berprioritas tinggi dengan hitung mundur 60 detik (*audio chime* + getar).
   - **Eksekusi Layanan**: Navigasi Google Maps terintegrasi, verifikasi radius tiba 50 meter, dan timer durasi sesi pijat.
   - **Keuangan**: Dompet mitra dengan *held balance* 24 jam dan pencairan dana ke rekening bank terverifikasi.

---

## 2. Konsep Desain 1: "Organic Serenity & Warm Wellness"

### 2.1 Filosofi Desain
Mengadopsi nuansa **spa premium, ketenangan (*zen*), dan relaksasi holistik**. Menghilangkan kesan kaku/birokratis dengan mengganti kartu bersegi tajam menjadi bentuk melengkung organik (*rounded-3xl*), palet warna bumi (*earth tones*), tipografi ramah, serta bayangan lembut (*soft ambient shadows*).

### 2.2 Karakteristik Visual & Token Desain
- **Palet Warna**:
  - *Primary*: Sage / Deep Emerald (`#0E7A5A`, `#059669`)
  - *Secondary / Accent*: Warm Sand & Oat (`#FBFBF9`, `#F5F3EF`)
  - *Surface / Card*: Pure White (`#FFFFFF`) dengan *soft inner border* (`rgba(226, 232, 240, 0.6)`)
  - *Text*: Muted Slate (`#0F172A`, `#64748B`)
- **Bentuk & Sudut**:
  - Container / Kartu: `rounded-3xl` (24px s.d. 28px)
  - Chips / Badges: `rounded-full` (kapsul lonjong)
  - Floating Action Buttons: `rounded-2xl`
- **Pencahayaan & Elevasi**:
  - Bayangan sangat halus: `box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.06)`
  - Header & Bottom Sheet: *Frosted glass* (`backdrop-blur-md bg-white/90`)

### 2.3 Prompt Siap Pakai (Figma / v0.dev / AI UI Generator)
```text
Mobile PWA UI/UX design for "Pijat.In", an on-demand professional home wellness and massage service app.
Aesthetic & Style: Warm Organic Minimalist, Zen luxury wellness, soft rounded aesthetic (border-radius: 28px), subtle glassmorphism header, clean breathable whitespace, tactile micro-shadows.
Color Palette: Calming Sage Green (#0E7A5A, #2DD4BF), Warm Oat/Cream background (#FBFBF9), Soft Muted Slate for typography (#1E293B, #64748B), Soft Amber accents (#F59E0B).
Key UI Elements:
- Mobile PWA viewport (390x844px), sticky blur header with rounded pill greeting and modern bell notification icon with pulsing unread dot.
- Fluid Wallet Balance card with smooth organic gradient mesh and quick "Isi Saldo" action pill.
- Service Catalog carousel with high-quality warm photography, duration badges (60/90/120 Min), and pill-shaped category chips.
- Active Order Tracking: Full-bleed interactive map with pastel custom map pins (therapist & customer), floating frosted-glass status pill, and a smooth draggable bottom sheet with rounded drag handle, therapist avatar, progress stepper, and direct chat button.
- Floating bottom dock bar with pill active indicators and soft glow.
Typography: Plus Jakarta Sans or Figtree, rounded, friendly, high readability.
```

---

## 3. Konsep Desain 2: "Sleek Neo-Bento & Tactile Utility"

### 3.1 Filosofi Desain
Mengadopsi gaya **aplikasi teknologi modern generasi baru** (terinspirasi dari *Airbnb, Apple Health, dan Uber Eats terbaru*). Menekankan tata letak modular **Bento Grid**, kontras tajam nan elegan, komponen interaktif yang padat informasi namun tetap lapang, serta *micro-animations* yang responsif.

### 3.2 Karakteristik Visual & Token Desain
- **Palet Warna**:
  - *Primary*: Deep Forest Green (`#064E3B`, `#047857`)
  - *Secondary / Energy*: Vibrant Mint (`#34D399`, `#10B981`)
  - *Canvas*: High-contrast Light Slate (`#F8FAFC`)
  - *Card Surface*: Ultra White (`#FFFFFF`) dengan *crisp hairline border* (`1px border-slate-200/80`)
  - *Accent / Alert*: Coral Rose (`#F43F5E`) & Warm Amber (`#F59E0B`)
- **Bentuk & Sudut**:
  - Modular Bento Cards: `rounded-2xl` (16px s.d. 20px)
  - Dynamic Action Pills: `rounded-xl`
  - Floating Sheet: `rounded-t-3xl`
- **Pencahayaan & Elevasi**:
  - Kontras tinggi dengan elevasi berlapis (*layered elevation*)
  - Badge dan pill menggunakan efek *neon pulse* lembut

### 3.3 Prompt Siap Pakai (Figma / v0.dev / AI UI Generator)
```text
High-end modern Mobile PWA UI design for "Pijat.In", an on-demand on-call massage booking and therapist platform.
Aesthetic & Style: Neo-Bento grid layout, modern dynamic utility, crisp contrast, 24px border-radius, subtle hairline borders (1px border-slate-200/80), layered elevation, interactive micro-states.
Color Palette: Deep Emerald Slate (#064E3B, #059669), Clean Crisp White (#FFFFFF), Ultra-light Slate canvas (#F8FAFC), Vibrant Mint (#34D399), Alert Coral (#F43F5E).
Key UI Elements:
- Dynamic Top Bar: Seamless app bar with user profile thumbnail, location selector pill with drop arrow, and notification bell with badge counter.
- Bento Dashboard Grid:
  * Large Hero Bento: Compact digital wallet card with monospace bold balance, quick top-up CTA, and quick address switcher.
  * Split Bento Grid: "Pesan Instan (Terapis Dekat)" with a live radar pulse icon vs "Jadwalkan Layanan (Bursa)" with calendar badge.
  * Live Order Floating Card: Compact pill-shaped widget showing live status ("Terapis Menuju Lokasi - 8 Menit"), mini turn-by-turn road icon, and quick action drawer.
- Therapist Mode Dashboard: Prominent status toggle (Online/Offline) with glowing neon status pill, 60-second radial timer modal for incoming order dispatch with tactile "Terima" and "Tolak" buttons.
- Bottom Floating Dock: Floating island dock with glassmorphism backdrop blur and minimal vector icons.
Typography: Inter or SF Pro Display, geometric, sharp hierarchy, clean numbers.
```

---

## 4. Panduan Implementasi Komponen Bebas Kaku

Agar PWA tidak terkesan kaku saat diterapkan di kode Blade & Tailwind:

| Elemen Kaku Lama | Solusi Modern & Luwes | Kelas Tailwind Disarankan |
| :--- | :--- | :--- |
| Garis border hitam/abu tebal | Hilangkan border tebal; gunakan kontras background + bayangan lembut | `border border-slate-100 shadow-sm shadow-slate-200/50` |
| Sudut tajam kotak (`rounded-md`) | Sudut melengkung lembut | `rounded-2xl` atau `rounded-3xl` |
| Filter tombol kotak | Chips kapsul lonjong | `rounded-full px-4 py-1.5 text-xs font-semibold` |
| Bottom sheet statis | Bottom sheet geser berkontur halus dengan handle bar | `rounded-t-3xl transition-all duration-300 ease-out` |
| Teks panjang tanpa visual | Gabungkan icon chip + angka monospaced | `font-mono tracking-tight font-black` |
| Transisi klik mendadak | Micro-interaction tekan membal | `active:scale-[0.98] transition-transform duration-150` |
| Notifikasi native browser popup | Modal beranimasi tema serasi | SweetAlert2 (`swal-admin-theme`) & In-app Toasts |

