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

- Partner Order Details, Map & Live Road Routing: Partners can view full order details (`mitra.orders.show`), customer recipient info, medical notes, interactive Leaflet map with customer marker & live partner GPS marker, actual road route geometry (turn-by-turn road tracking via OSRM routing engine), driving distance (`X.X km (Jalur Jalan)`), estimated arrival time, Google Maps direct navigation link, and sequential service status updates (`Dijadwalkan` -> `MenujuLokasi` -> `Tiba` -> `SedangBerlangsung` -> `Selesai`).
- Complaints & Demerit Sanction System (S8 / ADM-08, ADM-06, ADM-07, SA-05): Customer & partner complaints handling with full evidence upload and admin resolution actions; dynamic demerit points system with automatic threshold enforcement and performance redemption (5-star ratings shave off demerit points); penalty appeal workflow with formal declaration upload and regional admin review. Standalone rating review (ADM-09) consolidated into Partner Management (ADM-05/S11) per DEC-13.
- User Management & Moderation (S11 / ADM-05): Regional partner and customer directory, tabs, search and status filters; integrated partner rating performance card (5-star distribution bars, average score, customer feedback testimonials); account moderation actions (reactivate, suspend with duration and mandatory reason, permanent block with blacklist table for phone numbers and device IDs); authentication restrictions preventing suspended or blacklisted users from logging in.
- All 198 unit & feature tests in Pest passing (867 assertions) with Pint style formatting compliant.

- Partner & Customer Order Tracking with Full Map & Live Road Routing: Both partners (`mitra.orders.show`) and customers (`pelanggan.orders.show`) view interactive full-screen Leaflet maps with live partner & customer pins, OSRM turn-by-turn road route geometries, driving distances, ETAs, and pullable bottom sheets (peek vs expanded) matching Gojek/Grab driver & passenger UX. Live polling (`/pelanggan/orders/{order}/live-tracking`) updates GPS positions every 10 seconds.
- Multi-Wilayah Layanan Mitra (DEC-12 / Opsi 1): Mitra diizinkan melayani hingga 2 wilayah operasional (Wilayah Utama / Domisili tempat pin koordinat berada, dan Wilayah Tambahan opsional). Didukung kolom `secondary_region_id` pada `partner_profiles`, matching order instan $\le$ 5 km pada kedua wilayah, dan bursa pesanan terjadwal $\le$ 10 km pada kedua wilayah.
- Order Execution, Tracking, In-App Chat, & Ratings (Slice 6 / S6):
  - Validasi Radius 50m saat Tiba (MIT-10): Validasi jarak geofence $\le 50$ meter dari alamat layanan dengan dialog konfirmasi manual SweetAlert sebagai jalur cadangan (toleransi GPS drift).
  - Timer Durasi Sesi & Konfirmasi Selesai (MIT-09): Timer hitung mundur visual di layar Mitra dan Pelanggan berdasarkan `service_started_at` dan `duration_minutes`. Tombol selesaikan layanan memicu dialog peringatan jika durasi belum genap.
  - In-App Order Chat Drawer (CUS-11, MIT-13): Percakapan pesan teks dalam aplikasi selama pesanan aktif via `<x-order-chat-drawer />`, nomor kontak asli disembunyikan untuk privasi pengguna, dan riwayat obrolan tersimpan untuk audit investigasi.
  - In-Sheet Rating Bintang 1-5 (CUS-14, MIT-14): Penilaian rating 1-5 bintang pasca-layanan selesai tanpa form teks. Rating 5 bintang memicu pemotongan poin sanksi mitra, dan rating $\le 3$ bintang memicu prompt aduan ke Pusat Bantuan.
  - Idempotensi Aksi Luar Jaringan (SYS-09): Mencegah transisi ganda saat retry akibat gangguan sinyal di lapangan.
- Order Cancellation, Reschedule, Refund, & Partner Compensation (Slice 7 / S7):
  - Matriks Pembatalan Pelanggan (CUS-12): Pembatalan pesanan terjadwal $\ge 3$ jam sebelum jadwal mengembalikan dana 100%; pembatalan $< 3$ jam sebelum jadwal memotong 50% dana yang otomatis dikreditkan sebagai kompensasi transport mitra ke dompetnya (`partner_compensation`). Pembatalan pada status `MenujuLokasi` dikunci kecuali mitra terdeteksi terlambat parah ($> 30$ menit).
  - Reschedule Pesanan Terjadwal (CUS-13): Pelanggan dapat menjadwalkan ulang pesanan terjadwal maksimal 2 kali per pesanan tanpa biaya, minimal 3 jam sebelum jadwal lama (antara pukul 08:00 s.d 21:00). Setelah reschedule berhasil, pesanan kembali dipublikasikan ke bursa jadwal mitra (`MencariMitra`) secara otomatis.
  - Pembatalan Sepihak Mitra (MIT-12): Mitra membatalkan pesanan terjadwal yang sudah diambil dengan alasan wajib; dikenakan sanksi 15 poin pelanggaran (SP) via `PenaltyService` dan pesanan dilempar kembali ke bursa terbuka.
  - Pembatalan Khusus Mitra di Lokasi (MIT-11, DEC-03): Mitra dapat membatalkan di status `Tiba` karena Customer Tidak Ada di Lokasi (mitra dapat kompensasi 50%, pelanggan disanksi 15 poin), Gender Penerima Tidak Sesuai (mitra dapat kompensasi 50%), dan Kontraindikasi Medis Berbahaya (refund pelanggan 100%, mitra bebas sanksi 100% per DEC-03).
  - Pembatalan Otomatis Sistem (SYS-02): Command `orders:process-cancellations` (terjadwal setiap menit) secara otomatis membatalkan pesanan instan tanpa mitra $> 30$ menit (refund 100%), pesanan terjadwal bursa yang melewati batas H-2 jam (refund 100%), pesanan belum dibayar $> 15$ menit, dan kedaluwarsa invoice top-up saldo $> 15$ menit.
- Master Wilayah & Admin Seeders: `MasterWilayahSeeder` populates operational regions (Jember, Yogyakarta, Surabaya, Blitar, Jakarta Selatan, dll.) with official boundary polygons/MultiPolygons from GeoJSON datasets, and `AdminSeeder` creates Superadmin and Regional Admins with automatic region assignments and emergency duty handling (`can_handle_emergency = true`).
- Partner 24-Hour Held Balance, Payouts & Regional Finance (Slice 9 / S9):
  - 24-Hour Held Balance Escrow (SYS-03, MIT-15): Partner earnings (80%) are held in `held_balance` for 24 hours post-order completion while platform commission (20%) is recorded. Scheduler command `app:release-held-balances` automatically moves funds to `balance` (available) after 24 hours if no active customer complaints exist.
  - Partner Wallet & Withdrawal (MIT-15, MIT-16): PWA screen displaying Saldo Tersedia & Saldo Tertahan separately, 7/30-day mutation filters, withdrawal request flow to verified bank accounts (`bank_accounts`) with anti-concurrency and suspension locks.
  - Customer Wallet Withdrawal (CUS-17): Customer wallet balance withdrawal to verified bank accounts with primary address validation and minimum amount rules.
  - Regional Finance & Approval (ADM-11): Regional Admin finance dashboard, cash flow ledger (top-up, refunds, payouts), withdrawal approval/rejection queue with transfer references, and CSV/print-ready reporting.
  - Superadmin Floating Balance & Cross-Region Reporting (SA-04, SA-08): Centralized monitoring of global floating balance liabilities vs Xendit settlements, and multi-region financial performance comparison with CSV exports.
- Partner Inter-Region Relocation & Audit Preservation (Slice 10 / S10 / MIT-22, ADM-16):
  - Partner relocation request flow (`/mitra/pindah-wilayah`) with interactive Leaflet map pin and address text in any active operating region.
  - Strict prerequisite: Blocked if partner has active or scheduled orders (`dijadwalkan`, `menuju_lokasi`, `tiba`, `sedang_berlangsung`).
- FAQ Management & Regional Live Chat Support Desk:
  - Centralized FAQ Management (`/superadmin/faqs`): Superadmin creates, edits, sorts, and toggles active status for FAQ entries segmented by target audience (`pelanggan`, `mitra`, `semua`) and categories.
  - Regional Live Chat Support Desk (`/admin/support-chats`): Regional Admin support desk strictly scoped to assigned operational regions (`admin.region`), providing real-time bidirectional messaging, auto-polling every 4 seconds, ticket resolution/reopening, user context summary, and integrated system notifications.
  - Customer Help Center & Live Chat (`/pelanggan/bantuan` & `/pelanggan/bantuan/chat`): Customers access categorized FAQ self-service, real-time client search, and escalate unanswered questions to regional admin live chat bound to customer's active region.
  - Partner Help Center & Live Chat (`/mitra/bantuan` & `/mitra/bantuan/chat`): Partners access partner-specific and general FAQs, and escalate field issues or inquiries directly to assigned regional operational admins via dedicated live chat.
  - Account frozen: While pending destination regional admin review, partner cannot toggle online (`is_active = false`) and wallet withdrawals are locked.
  - Destination Regional Admin review tab (`/admin/mitra/verifikasi?tab=relokasi`) with approve/reject actions and mandatory rejection reason.
  - Audit history preserved (ADM-16): When approved, `region_id` and address update seamlessly while historical orders, ratings, and penalty ledger remain intact for full cross-region audit inspection.
- Customer Order Payment Gateway & Xendit Checkout (Slice 3 / CUS-09):
  - Wallet balance instant debit on sufficient balance.
  - Automatic 15-minute deficit top-up invoice generation via Xendit / QRIS on insufficient balance with direct redirect to invoice page (Opsi 1).
  - Atomic auto-settlement on webhook / payment: credits customer wallet, debits order, transitions order to `MencariMitra`, triggers offer dispatch for instant orders, and redirects customer back to live order tracking.
  - 15-minute expiration countdown & auto-cancellation via `orders:process-cancellations`.
- App Branding & Identity Settings:
  - Superadmin dynamic branding control (`/superadmin/branding`): Allows updating application name, slogan/tagline, meta SEO description, custom logo upload, and browser favicon upload.
  - Automatic asset fallback: Seamlessly falls back to default system SVG vector assets when no custom logo or favicon is uploaded, with single-click reset capability.
  - Dynamic shell integration: App name, logo, and favicon dynamically rendered in desktop sidebar (`sidebar.blade.php`), mobile drawer, PWA mobile shell (`pwa.blade.php`), and browser page titles.
  - Audit log integration: Changes to branding parameters are logged in centralized `AuditLog`.
- Master Rekening & Saluran Bank Pencairan:
  - Superadmin bank management (`/superadmin/banks`): Manage supported withdrawal banks and disbursement channels manually via form (no forced seeders).
  - Capabilities: Add new bank (name, code, optional account number/holder for operational settlement, notes, sort order, active status), edit bank details, toggle active/inactive status, and safe deletion (safeguard protects transaction integrity by soft deactivating instead of deleting if withdrawal requests exist).
  - Navigation: Direct menu link in sidebar and mobile drawer under "Tata Kelola & Sistem".
- Automated Audit Trail System & Data Pruning:
  - Trait `LogsActivity` (`app/Traits/LogsActivity.php`): Automatic audit logging for Eloquent models on `created`, `updated` (capturing `old_values` vs `new_values` for changed fields, auto `status_change` label), and `deleted`. Strips sensitive fields (passwords, tokens). Fail-safe execution (does not interrupt business logic).
  - Authentication Event Auditing: Global listeners in `AppServiceProvider` capture `Login` and `Logout` events for all user roles (Superadmin, Admin, Mitra, Pelanggan) recording actor, email, role, IP address, and user-agent.
  - Automated Log Retention Pruning: Integrated into `CleanupExpiredDataCommand` (`app:cleanup-expired-data`) to prune audit logs older than retention period (default 30 days, configurable via parameter) to prevent database bloat.
  - Enhanced Superadmin Log Viewer (`/superadmin/audit-logs`): Comprehensive Indonesian module/action labels and formatted select dropdowns.
- Service Catalog Multiple Photos:
  - Model & Database (`services.images` JSON column): Stores array of image paths for each service. Accessors `first_image_url` and `image_urls` resolve public storage assets with fallback.
  - Admin Catalog Management (`/admin/catalog`): Multi-image upload on create modal with instant client-side preview; existing photo gallery management on edit modal with selective removal and incremental additions (up to 5 photos, max 2MB each). Auto-deletes files on service deletion.
  - Customer Presentation (`/katalog` & `/katalog/{slug}`): Thumbnails with multi-photo count indicators on catalog listing; interactive hero gallery and thumbnail selector on customer service detail view.
- Superadmin National Platform Executive Dashboard (`/superadmin/dashboard`):
  - Controller: `App\Http\Controllers\SuperAdmin\DashboardController` (`superadmin.dashboard`).
  - Macro KPIs: Total GMV, platform commission, completed/active order count, verified online therapist supply & customer ecosystem, operating regions coverage.
  - Strategic Analytics: 12-month national order trend (Chart.js line chart) and Top 5 active regions benchmark (horizontal bar chart).
  - Financial Liquidity & Liabilities: Floating balance liabilities (partner & customer wallet balances), 24-hour held escrow balance, and pending withdrawal payout queue.
  - Governance & Risk Alerts: Orphaned/unassigned operating regions alerts with direct admin assignment CTA, pending national complaints count, and pending partner verification queue.
  - Audit Trail Feed: 5 most recent centralized audit log entries with direct detail viewer navigation.
- All unit & feature tests in Pest passing with Pint style formatting compliant.

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
