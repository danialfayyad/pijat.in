<x-guest-layout>
    <x-auth-session-status class="login-status" :status="session('status')" />
    <form class="login-form" method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }">
        @csrf

        <div class="login-field-group">
            <label class="login-label" for="email">Email <span>*</span></label>
            <input id="email" class="login-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="login-error" />
        </div>

        <div class="login-field-group login-password-group">
            <label class="login-label" for="password">Kata Sandi <span>*</span></label>
            <div class="login-password-wrap">
                <input id="password" class="login-input" type="password" name="password" required autocomplete="current-password" x-bind:type="showPassword ? 'text' : 'password'">
                <button class="login-password-toggle" type="button" aria-label="Tampilkan kata sandi" x-on:click="showPassword = ! showPassword" x-bind:aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <svg x-show="! showPassword" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A10.8 10.8 0 0 1 12 5c5.2 0 8.7 4.2 9.5 6-.3.8-1.2 2.1-2.7 3.4M6.2 6.2C3.9 7.5 2.8 9.7 2.5 11c.8 1.8 4.3 6 9.5 6 1 0 1.9-.2 2.7-.5" /></svg>
                    <svg x-show="showPassword" x-cloak viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12S6 6 12 6s9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="login-error" />
        </div>

        <div class="login-forgot-row">
            @if (Route::has('password.request'))
                <a class="login-forgot" href="{{ route('password.request') }}">Lupa kata sandi ?</a>
            @endif
        </div>

        <button class="login-submit" type="submit">Masuk</button>
    </form>

    <div class="login-divider" role="separator"></div>

    <button class="login-google" type="button" disabled aria-label="Masuk dengan Google belum tersedia">
        <svg class="google-mark" viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#4285F4" d="M43.6 24.5c0-1.4-.1-2.7-.4-4H24v7.6h11a9.4 9.4 0 0 1-4.1 6.2v5h6.7c3.9-3.6 6-8.8 6-14.8Z" />
            <path fill="#34A853" d="M24 44c5.5 0 10.1-1.8 13.5-4.8l-6.7-5c-1.8 1.2-4 2-6.8 2-5.3 0-9.7-3.6-11.3-8.4H5.8v5.2A20 20 0 0 0 24 44Z" />
            <path fill="#FBBC05" d="M12.7 27.8a12 12 0 0 1 0-7.6V15H5.8a20 20 0 0 0 0 17.9l6.9-5.1Z" />
            <path fill="#EA4335" d="M24 11.8c3 0 5.7 1 7.8 3.1l5.9-5.9C34.1 5.8 29.5 4 24 4A20 20 0 0 0 5.8 15l6.9 5.2c1.6-4.8 6-8.4 11.3-8.4Z" />
        </svg>
        <span>Lanjutkan dengan Google</span>
    </button>

    <p class="login-register">Belum punya akun pelanggan? <a href="{{ route('register') }}">Daftar</a></p>
    <p class="login-register">Ingin bergabung sebagai mitra? <a href="{{ route('mitra.register') }}">Daftar sebagai Mitra</a></p>
</x-guest-layout>
