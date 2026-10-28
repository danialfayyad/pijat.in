<!DOCTYPE html>
@php($isLoginPage = request()->routeIs('login'))
@php($isRegisterPage = request()->routeIs('register', 'mitra.register'))
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ $isLoginPage ? '#006a68' : '#ffffff' }}">
        @if ($isLoginPage || $isRegisterPage)
            <meta name="apple-mobile-web-app-capable" content="yes">
            <meta name="apple-mobile-web-app-status-bar-style" content="{{ $isLoginPage ? 'black-translucent' : 'default' }}">
        @endif

        <title>{{ $isLoginPage ? 'Masuk — Pijat.in' : ($isRegisterPage ? 'Daftar — Pijat.in' : config('app.name', 'Laravel')) }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="{{ $isLoginPage ? 'login-page' : ($isRegisterPage ? 'register-page' : 'font-sans text-gray-900 antialiased') }}">
        @if ($isLoginPage)
            <div class="login-shell">
                <header class="login-header">
                    <a class="login-back" href="{{ url('/') }}" aria-label="Kembali ke halaman utama">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7" /></svg>
                    </a>
                    <h1>Silahkan Log in dulu yah</h1>
                    <svg class="login-logo" viewBox="0 0 42 24" aria-label="Pijat.in" role="img">
                        <path d="M14 5.2c-2.9-2.3-7.1-.7-9.1 3.1-1.8 3.3-.8 7.4 2.1 8.6 2.7 1.1 5.5-.6 7.5-3.6l7.1-10.3c2.1-3 5-4.7 7.7-3.6 3 1.2 3.9 5.3 2.1 8.6-2 3.8-6.2 5.4-9.1 3.1" />
                        <path d="M28 5.2c2.9-2.3 7.1-.7 9.1 3.1 1.8 3.3.8 7.4-2.1 8.6-2.7 1.1-5.5-.6-7.5-3.6L20.4 5" />
                    </svg>
                </header>

                <main class="login-main">
                    {{ $slot }}
                </main>
            </div>
        @elseif ($isRegisterPage)
            <div class="register-shell">
                <header class="register-header">
                    <a class="register-back" href="{{ url('/') }}" aria-label="Kembali ke halaman utama">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7" /></svg>
                    </a>
                    <svg class="register-logo" viewBox="0 0 42 24" aria-label="Pijat.in" role="img">
                        <path d="M14 5.2c-2.9-2.3-7.1-.7-9.1 3.1-1.8 3.3-.8 7.4 2.1 8.6 2.7 1.1 5.5-.6 7.5-3.6l7.1-10.3c2.1-3 5-4.7 7.7-3.6 3 1.2 3.9 5.3 2.1 8.6-2 3.8-6.2 5.4-9.1 3.1" />
                        <path d="M28 5.2c2.9-2.3 7.1-.7 9.1 3.1 1.8 3.3.8 7.4-2.1 8.6-2.7 1.1-5.5-.6-7.5-3.6L20.4 5" />
                    </svg>
                    <h1>Selamat bergabung</h1>
                    <p>Masukan data diri anda</p>
                </header>
                <main class="register-main">
                    {{ $slot }}
                </main>
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
