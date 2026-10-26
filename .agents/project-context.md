# Pijat.in project context

Read this file before implementing a feature. Update the **Current implementation** section when application behavior or architecture changes. Keep proposed business behavior separate from features that exist in code.

## Product understanding

Pijat.in is presented as a massage and wellness marketplace. The public landing page describes massage services, customers, professional therapists (called **mitra** in the application), and an opportunity for therapists to join. The site copy places operations in Yogyakarta. The copy is product positioning only; it does not establish implemented booking, payments, coverage, or operational policies.

## Current implementation

- Laravel 13.38, PHP 8.4, Blade, Breeze 2.4.2 (Blade auth scaffolding), Tailwind CSS, Alpine.js, and Vite.
- `GET /` renders the public marketing page in `resources/views/welcome.blade.php`. It describes six massage services and the customer/therapist value proposition. Many calls to action are anchor links or placeholders; there is no booking flow or mobile app integration in this repository yet.
- Breeze email/password authentication is shared across roles. The login page uses a PWA-safe, mobile-first custom layout based on the supplied 430 × 932 reference; the other guest auth pages keep the Breeze layout. Login throttles after five attempts. Password reset, email verification endpoints, password confirmation, logout, and account profile editing are scaffolded.
- Public registration collects NIK, name, gender, optional date of birth, email, phone, and password; it assigns `pelanggan` on the server, signs the new user in, then redirects to the customer dashboard. NIK is stored as a unique 16-digit string. Apply the registration-details migration before using the expanded form. Never accept a privileged role from registration input.
- Successful login redirects to a role-specific dashboard. `/dashboard` also dispatches an authenticated user based on their role.
- Role dashboards are placeholders rendered by `resources/views/role-dashboard.blade.php`; they are not separate feature areas yet.
- Role middleware returns HTTP 403 when an authenticated user lacks an allowed role. `admin.dashboard` currently allows both `admin` and `superadmin`; the other three dashboard routes each allow only their named role.
- Authenticated users can update name/email, change password, and delete their own account. Profile validation does not expose role changes.
- `DatabaseSeeder` creates `Test User` (`test@example.com`) and calls `RoleLoginSeeder`. The latter provisions local/testing demo accounts for all four roles; it intentionally does nothing in other environments.
- Demo login email addresses are `superadmin@pijatin.test`, `admin@pijatin.test`, `pelanggan@pijatin.test`, and `mitra@pijatin.test`. The shared local/testing demo password is `password`.

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
