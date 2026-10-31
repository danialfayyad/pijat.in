# Pijat.in project context

Read this file before implementing a feature. Update the **Current implementation** section when application behavior or architecture changes. Keep proposed business behavior separate from features that exist in code.

## Product understanding

Pijat.in is presented as a massage and wellness marketplace. The public landing page describes massage services, customers, professional therapists (called **mitra** in the application), and an opportunity for therapists to join. The site copy places operations in Yogyakarta. The copy is product positioning only; it does not establish implemented booking, payments, coverage, or operational policies.

## Current implementation

- Laravel 13.38, PHP 8.4, Blade, Breeze 2.4.2 (Blade auth scaffolding), Tailwind CSS, Alpine.js, and Vite.
- `GET /` renders the public marketing page in `resources/views/welcome.blade.php`.
- Regional Master & Multi-Admin Assignment (S1 / SA-06, ADM-15): 514 Kota/Kabupaten Indonesia GeoJSON boundary presets, spatial MySQL SRID 4326, region assignment with emergency handling duty.
- Partner Onboarding & Document Verification (S2 / MTR-01, ADM-02, ADM-03): NIK, e-KTP, selfie, bank account lock, regional admin verification queue, re-verification safeguards.
- Service Catalog & Pricing (S3 / ADM-10): Services and duration packages (60/90/120 minutes) without add-ons, active/inactive catalog separation, server-side `admin.menu:KATALOG` permission protection.
- Regional Order Monitoring & Intervention (ADM-04, ADM-05): Scoped by admin assigned regions, status filtering (Active, Stuck, Completed, Cancelled), full order details & status history timeline, admin cancellation intervention with mandatory reason & 100% customer refund guarantee. Strictly adheres to BRD 5.3 (no manual partner assignment buttons).
- Custom global Alpine.js confirmation modal component (`<x-confirm-dialog />`) with centered icons, to-the-point copy, and distinct variant themes (`danger`, `warning`, `info`).
- Order Booking & FCFS Scheduled Matching (S4 / CUS-04, CUS-06, CUS-07, MIT-06, MIT-07, MIT-08, CUS-10): Scheduled booking (H+3 jam s.d. 7 hari, 08:00 - 21:00), partner service preferences, FCFS scheduled order exchange with 10 km maximum operating radius validation (orders > 10 km are filtered from bursa and blocked on claim), encrypted medical notes, real-time customer tracking with full-screen background map & Gojek-style draggable bottom sheet, live GPS polling, and SweetAlert2 confirmation dialogs.
- Instant Order Matching & Sequential Dispatch (S5 / CUS-08, MIT-04, MIT-05, SYS-01): Sequential 60-second dispatch based on live coordinate proximity within 5 km, partner active toggle, and auto-fallback to scheduled order if no nearby partner is available.
- Flexible Partner Gender Preference (DEC-11 / Opsi A): Rigid same-gender lock removed; customers choose therapist gender preference (`pria`, `wanita`, or `bebas`) during checkout.
- Partner & Customer Order Tracking with Full Map & Live Road Routing: Both partners (`mitra.orders.show`) and customers (`pelanggan.orders.show`) view interactive full-screen Leaflet maps with live partner & customer pins, OSRM turn-by-turn road route geometries, driving distances, ETAs, and pullable bottom sheets (peek vs expanded) matching Gojek/Grab driver & passenger UX. Live polling (`/pelanggan/orders/{order}/live-tracking`) updates GPS positions every 10 seconds.
- Multi-Wilayah Layanan Mitra (DEC-12 / Opsi 1): Mitra diizinkan melayani hingga 2 wilayah operasional (Wilayah Utama / Domisili tempat pin koordinat berada, dan Wilayah Tambahan opsional). Didukung kolom `secondary_region_id` pada `partner_profiles`, matching order instan $\le$ 5 km pada kedua wilayah, dan bursa pesanan terjadwal $\le$ 10 km pada kedua wilayah.
- Order Execution, Tracking, In-App Chat, & Ratings (Slice 6 / S6):
  - Validasi Radius 50m saat Tiba (MIT-10): Validasi jarak geofence $\le 50$ meter dari alamat layanan dengan dialog konfirmasi manual SweetAlert sebagai jalur cadangan (toleransi GPS drift).
  - Timer Durasi Sesi & Konfirmasi Selesai (MIT-09): Timer hitung mundur visual di layar Mitra dan Pelanggan berdasarkan `service_started_at` dan `duration_minutes`. Tombol selesaikan layanan memicu dialog peringatan jika durasi belum genap.
  - In-App Order Chat Drawer (CUS-11, MIT-13): Percakapan pesan teks dalam aplikasi selama pesanan aktif via `<x-order-chat-drawer />`, nomor kontak asli disembunyikan untuk privasi pengguna, dan riwayat obrolan tersimpan untuk audit investigasi.
  - In-Sheet Rating Bintang 1-5 (CUS-14, MIT-14): Penilaian rating 1-5 bintang pasca-layanan selesai tanpa form teks. Rating 5 bintang memicu pemotongan poin sanksi mitra, dan rating $\le 3$ bintang memicu prompt aduan ke Pusat Bantuan.
  - Idempotensi Aksi Luar Jaringan (SYS-09): Mencegah transisi ganda saat retry akibat gangguan sinyal di lapangan.
- Master Wilayah & Admin Seeders: `MasterWilayahSeeder` populates operational regions (Jember, Yogyakarta, Surabaya, Blitar, Jakarta Selatan, dll.) with official boundary polygons/MultiPolygons from GeoJSON datasets, and `AdminSeeder` creates Superadmin and Regional Admins with automatic region assignments and emergency duty handling (`can_handle_emergency = true`).
- All 200 unit & feature tests in Pest passing (902 assertions) with Pint style formatting compliant.

## Role model and authentication map

| Role value | Label | Current access | Likely product responsibility (proposal, not implemented) |
| --- | --- | --- | --- |
| `superadmin` | Super Admin | `/superadmin/dashboard` | Platform-wide settings, admin accounts, oversight, and cross-platform reporting. |
| `admin` | Admin | `/admin/dashboard`; also allowed at the admin dashboard when role is superadmin | Daily operations: customer/mitra support, mitra onboarding, service catalog, bookings, payment issues, and complaints. |
| `pelanggan` | Pelanggan | `/pelanggan/dashboard` | Find services and mitra, make/manage bookings, pay, track service, and review. |
| `mitra` | Mitra Terapis | `/mitra/dashboard` | Maintain therapist profile and availability, respond to bookings, fulfill services, and review earnings. |

Treat the responsibility column as a starting point for product discussion, not a requirement already encoded in the app. A single role string is stored per user; there is no roles/permissions package or many-to-many role system.

## Key code locations

- `routes/web.php`: landing, role dispatcher, role dashboard routes, profile routes.
- `routes/auth.php`: Breeze guest and authenticated endpoints.
- `app/Enums/Role.php`: supported role values and labels.
- `app/Models/User.php`: auth model, role cast, role predicates, fillable attributes.
- `app/Http/Middleware/EnsureUserHasRole.php`: role check used by the `role` middleware alias registered in `bootstrap/app.php`.
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`: login redirect by role and logout.
- `app/Http/Controllers/Auth/RegisteredUserController.php`: customer-only public registration.
- `app/Http/Requests/Auth/LoginRequest.php`: login validation, authentication, and throttling.
- `app/Http/Controllers/ProfileController.php` and `app/Http/Requests/ProfileUpdateRequest.php`: self-service profile management.
- `database/migrations/0001_01_01_000000_create_users_table.php`: users, password reset tokens, sessions.
- `database/migrations/2026_10_26_135949_add_role_to_users_table.php` and `database/migrations/2026_10_26_155339_add_registration_details_to_users_table.php`: role and customer registration fields on `users`.
- `database/factories/UserFactory.php`, `database/seeders/DatabaseSeeder.php`, and `database/seeders/RoleLoginSeeder.php`: test/development users and demo logins.
- `resources/views/auth/`: authentication screens; `resources/views/layouts/guest.blade.php` selects the custom login shell or the default Breeze guest shell; `resources/views/components/`: shared UI.
- `resources/views/welcome.blade.php`, `resources/css/app.css`, `resources/js/app.js`: public marketing UI, login styling, and frontend assets.

## Data and feature boundaries

The current database domain is only the Laravel user/authentication foundation. There are no models or tables for therapist profiles, services, availability, bookings, addresses, payments, payouts, reviews, or complaints. Create these deliberately when those features are scoped; do not assume their schemas or workflows from landing page copy.

When adding domain features, start with the user journey and ownership rules. Keep each role's data access enforced on the server with middleware/policies and validated queries; hiding navigation links is not authorization. Do not let a user edit their own role through profile forms. Provision privileged roles through a protected admin process or controlled seeder, not public registration.

## Local conventions and checks

- Follow root `AGENTS.md` and the `.ai/rules` path rules if that directory is added (it was absent during this review).
- Check installed package versions before relying on APIs. Laravel Boost is installed; use its documentation/schema/query tools for Laravel-specific work when available.
- Use named routes and `route()` for internal links. Keep Blade conventions and existing layouts/components.
- Generate Laravel artifacts with `php artisan make:* --no-interaction` where appropriate. Do not add dependencies without approval.
- For UI asset changes, run `npm run build`. For PHP changes, run Pint as instructed in `AGENTS.md`; run focused Pest coverage for behavior changes when requested/authorized by the task.
- Existing registration and email-verification scaffolding does not itself prove email verification is enforced as a prerequisite to protected routes. Check `User`'s verification contract and route middleware before depending on that behavior.
