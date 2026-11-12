<!DOCTYPE html>
@php
    $isLoginPage = request()->routeIs('login');
    $isRegisterPage = request()->routeIs('register');
    $isMitraRegisterPage = request()->routeIs('mitra.register');
    $isAuthPage = $isLoginPage || $isRegisterPage || $isMitraRegisterPage;
    $appName = \App\Models\Parameter::get('app_name', config('app.name', 'Pijat.In'));
    $appLogo = \App\Models\Parameter::get('app_logo');
    $appFavicon = \App\Models\Parameter::get('app_favicon');
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#059669">
        @if ($isAuthPage)
            <meta name="apple-mobile-web-app-capable" content="yes">
            <meta name="apple-mobile-web-app-status-bar-style" content="default">
        @endif

        <title>{{ $isLoginPage ? 'Masuk — ' . $appName : ($isMitraRegisterPage ? 'Daftar Mitra — ' . $appName : ($isRegisterPage ? 'Daftar Pelanggan — ' . $appName : config('app.name', 'Laravel'))) }}</title>

        @if ($appFavicon && file_exists(public_path('storage/' . $appFavicon)))
            <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $appFavicon) }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="{{ $isAuthPage ? 'bg-white text-slate-900 antialiased font-sans' : 'font-sans text-gray-900 antialiased' }}">
        @if ($isAuthPage)
            <div class="min-h-screen lg:grid lg:grid-cols-2 bg-white">
                <!-- Left Showcase Panel on Desktop (50% Width, Spacious & Clean) -->
                <div class="hidden lg:flex bg-slate-900 relative overflow-hidden flex-col justify-between p-12 xl:p-20 text-white">
                    <div class="absolute inset-0 z-0">
                        <img src="{{ asset('image/terapis_1.jpg') }}" alt="{{ $appName }}" class="w-full h-full object-cover opacity-25 filter brightness-90">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/85 to-emerald-950/75"></div>
                    </div>

                    <!-- Top: Brand -->
                    <div class="relative z-10 flex items-center gap-3.5">
                        @if ($appLogo && file_exists(public_path('storage/' . $appLogo)))
                            <img src="{{ asset('storage/' . $appLogo) }}" alt="{{ $appName }}" class="h-10 w-auto object-contain bg-white/10 rounded-xl p-1.5">
                        @elseif ($appFavicon && file_exists(public_path('storage/' . $appFavicon)))
                            <img src="{{ asset('storage/' . $appFavicon) }}" alt="{{ $appName }}" class="w-10 h-10 object-contain rounded-xl">
                        @else
                            <span class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-bold text-lg">
                                {{ mb_substr($appName, 0, 1) }}
                            </span>
                        @endif
                        <span class="text-2xl font-bold tracking-tight text-white">{{ $appName }}</span>
                    </div>

                    <!-- Center: Slogan & Narrative -->
                    <div class="relative z-10 space-y-5 my-auto py-10 max-w-lg">
                        @if ($isMitraRegisterPage)
                            <h2 class="text-3xl xl:text-4xl font-bold text-white tracking-tight leading-snug">
                                Bergabung Menjadi Mitra Terapis Profesional
                            </h2>
                            <p class="text-base text-slate-300 leading-relaxed">
                                Dapatkan penghasilan menarik dengan jadwal kerja yang fleksibel. Bergabunglah bersama jaringan mitra terapis terpercaya Pijat.in dan atur jam kerja Anda sendiri.
                            </p>
                        @elseif ($isRegisterPage)
                            <h2 class="text-3xl xl:text-4xl font-bold text-white tracking-tight leading-snug">
                                Mulai Perawatan Kebugaran Tubuh Berkualitas
                            </h2>
                            <p class="text-base text-slate-300 leading-relaxed">
                                Nikmati kemudahan memesan layanan terapis bersertifikasi langsung ke rumah atau lokasi Anda kapan saja, dengan jaminan kenyamanan, kebersihan, dan tarif transparan.
                            </p>
                        @else
                            <h2 class="text-3xl xl:text-4xl font-bold text-white tracking-tight leading-snug">
                                Layanan Pijat Profesional Langsung ke Tempat Anda
                            </h2>
                            <p class="text-base text-slate-300 leading-relaxed">
                                Nikmati kemudahan memesan layanan relaksasi dan kebugaran tubuh. Mitra terapis bersertifikasi siap hadir ke lokasi Anda dengan perlengkapan lengkap, higienis, dan tarif pasti.
                            </p>
                        @endif
                    </div>

                    <!-- Bottom: Footer -->
                    <div class="relative z-10 text-xs text-slate-400">
                        &copy; {{ date('Y') }} {{ $appName }}. Seluruh hak cipta dilindungi.
                    </div>
                </div>

                <!-- Right Column: Form Container (Spacious padding on Mobile & Desktop) -->
                <div class="flex flex-col justify-center min-h-screen px-6 py-12 sm:px-12 lg:px-16 xl:px-24 bg-white">
                    <div class="w-full max-w-md mx-auto">
                        <div class="mb-8 flex items-center justify-between">
                            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-slate-500 hover:text-emerald-700 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                <span>Kembali ke Beranda</span>
                            </a>
                            <!-- Brand logo on mobile -->
                            <div class="lg:hidden flex items-center gap-2">
                                @if ($appLogo && file_exists(public_path('storage/' . $appLogo)))
                                    <img src="{{ asset('storage/' . $appLogo) }}" alt="{{ $appName }}" class="h-7 w-auto object-contain">
                                @elseif ($appFavicon && file_exists(public_path('storage/' . $appFavicon)))
                                    <img src="{{ asset('storage/' . $appFavicon) }}" alt="{{ $appName }}" class="w-7 h-7 object-contain">
                                @endif
                                <span class="text-base font-bold text-slate-900">{{ $appName }}</span>
                            </div>
                        </div>

                        <div class="mb-8">
                            @if ($isMitraRegisterPage)
                                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Daftar Mitra Terapis</h1>
                                <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">Lengkapi data dasar Anda untuk memulai proses kemitraan.</p>
                            @elseif ($isRegisterPage)
                                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Buat Akun Pelanggan</h1>
                                <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">Daftarkan data diri Anda untuk mulai memesan layanan pijat.</p>
                            @else
                                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Masuk ke Akun</h1>
                                <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">Silakan masukkan email dan kata sandi akun Anda untuk melanjutkan.</p>
                            @endif
                        </div>

                        {{ $slot }}
                    </div>
                </div>
            </div>
        @else
            <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
                <div>
                    <a href="/">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </a>
                </div>

                <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                    {{ $slot }}
                </div>
            </div>
        @endif
    </body>
</html>
