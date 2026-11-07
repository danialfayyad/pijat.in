<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandingSettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan identitas & branding aplikasi.
     */
    public function index(): View
    {
        $appName = Parameter::get('app_name', config('app.name', 'Pijat.In'));
        $appTagline = Parameter::get('app_tagline', 'Layanan Pijat & Relaksasi Profesional');
        $appDescription = Parameter::get('app_description', 'Platform pemesanan terapis pijat dan relaksasi profesional terpercaya langsung ke lokasi Anda.');
        $appLogo = Parameter::get('app_logo', '');
        $appFavicon = Parameter::get('app_favicon', '');

        return view('superadmin.branding.index', compact(
            'appName',
            'appTagline',
            'appDescription',
            'appLogo',
            'appFavicon'
        ));
    }

    /**
     * Simpan pembaruan identitas, logo, favicon, dan teks branding.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:50'],
            'app_tagline' => ['nullable', 'string', 'max:150'],
            'app_description' => ['nullable', 'string', 'max:500'],
            'app_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'app_favicon' => ['nullable', 'image', 'mimes:png,ico,svg,webp', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string', 'max:255'],
        ], [
            'app_name.required' => 'Nama aplikasi wajib diisi.',
            'app_logo.image' => 'File logo harus berupa gambar yang valid (PNG, JPG, SVG, WebP).',
            'app_logo.max' => 'Ukuran file logo maksimal 2 MB.',
            'app_favicon.image' => 'File favicon harus berupa gambar/ikon yang valid (PNG, ICO, SVG).',
            'app_favicon.max' => 'Ukuran file favicon maksimal 1 MB.',
        ]);

        $actor = $request->user();
        $reason = ! empty($validated['reason']) ? $validated['reason'] : 'Pembaruan identitas dan branding aplikasi';

        // 1. Teks Identitas
        Parameter::set('app_name', $validated['app_name'], $reason, $actor);
        Parameter::set('app_tagline', $validated['app_tagline'] ?? '', $reason, $actor);
        Parameter::set('app_description', $validated['app_description'] ?? '', $reason, $actor);

        // 2. Logo Utama
        $currentLogo = Parameter::get('app_logo', '');
        if ($request->boolean('remove_logo')) {
            if ($currentLogo && Storage::disk('public')->exists($currentLogo)) {
                Storage::disk('public')->delete($currentLogo);
            }
            Parameter::set('app_logo', '', $reason, $actor);
        } elseif ($request->hasFile('app_logo')) {
            if ($currentLogo && Storage::disk('public')->exists($currentLogo)) {
                Storage::disk('public')->delete($currentLogo);
            }
            $logoPath = $request->file('app_logo')->store('branding', 'public');
            Parameter::set('app_logo', $logoPath, $reason, $actor);
        }

        // 3. Favicon
        $currentFavicon = Parameter::get('app_favicon', '');
        if ($request->boolean('remove_favicon')) {
            if ($currentFavicon && Storage::disk('public')->exists($currentFavicon)) {
                Storage::disk('public')->delete($currentFavicon);
            }
            Parameter::set('app_favicon', '', $reason, $actor);
        } elseif ($request->hasFile('app_favicon')) {
            if ($currentFavicon && Storage::disk('public')->exists($currentFavicon)) {
                Storage::disk('public')->delete($currentFavicon);
            }
            $faviconPath = $request->file('app_favicon')->store('branding', 'public');
            Parameter::set('app_favicon', $faviconPath, $reason, $actor);
        }

        Parameter::clearCache();

        return redirect()->route('superadmin.branding.index')
            ->with('status', 'Identitas dan branding aplikasi berhasil diperbarui.');
    }
}
