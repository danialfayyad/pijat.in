<x-guest-layout>
    <div class="mb-4 text-center">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
            Pendaftaran Mitra Terapis
        </span>
        <h2 class="text-xl font-bold text-slate-900 mt-2">Bergabung Bersama Pijat.In</h2>
        <p class="text-xs text-slate-500 mt-0.5">Daftarkan data dasar Anda untuk memulai proses kemitraan.</p>
    </div>

    <form class="register-form" method="POST" action="{{ route('mitra.register') }}" x-data="{ showPassword: false, showConfirmation: false }">
        @csrf

        <div class="register-field-group register-name-group">
            <label class="register-label" for="name">Nama Lengkap Sesuai KTP<span>*</span></label>
            <input class="register-input" id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Contoh: Budi Santoso">
            <x-input-error :messages="$errors->get('name')" class="register-error" />
        </div>

        <fieldset class="register-gender-group">
            <legend class="register-label">Jenis Kelamin<span>*</span></legend>
            <div class="register-gender-options">
                <label><input name="gender" type="radio" value="laki-laki" @checked(old('gender') === 'laki-laki') required><span>Laki-laki</span></label>
                <label><input name="gender" type="radio" value="perempuan" @checked(old('gender') === 'perempuan') required><span>Perempuan</span></label>
            </div>
            <x-input-error :messages="$errors->get('gender')" class="register-error" />
        </fieldset>

        <div class="register-field-group">
            <label class="register-label" for="email">Email Aktif<span>*</span></label>
            <input class="register-input" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@email.com">
            <x-input-error :messages="$errors->get('email')" class="register-error" />
        </div>

        <div class="register-field-group">
            <label class="register-label" for="phone">Nomor WhatsApp / HP<span>*</span></label>
            <input class="register-input" id="phone" name="phone" type="tel" inputmode="tel" value="{{ old('phone') }}" required autocomplete="tel" placeholder="08xxxxxxxxxx">
            <x-input-error :messages="$errors->get('phone')" class="register-error" />
        </div>

        <div class="register-field-group">
            <label class="register-label" for="password">Kata Sandi<span>*</span></label>
            <div class="register-password-wrap">
                <input class="register-input" id="password" name="password" type="password" required autocomplete="new-password" x-bind:type="showPassword ? 'text' : 'password'">
                <button class="register-password-toggle" type="button" x-on:click="showPassword = ! showPassword" x-bind:aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <svg x-show="! showPassword" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A10.8 10.8 0 0 1 12 5c5.2 0 8.7 4.2 9.5 6-.3.8-1.2 2.1-2.7 3.4M6.2 6.2C3.9 7.5 2.8 9.7 2.5 11c.8 1.8 4.3 6 9.5 6 1 0 1.9-.2 2.7-.5" /></svg>
                    <svg x-show="showPassword" x-cloak viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12S6 6 12 6s9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="register-error" />
        </div>

        <div class="register-field-group register-confirm-group">
            <label class="register-label" for="password_confirmation">Konfirmasi Kata Sandi<span>*</span></label>
            <div class="register-password-wrap">
                <input class="register-input" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" x-bind:type="showConfirmation ? 'text' : 'password'">
                <button class="register-password-toggle" type="button" x-on:click="showConfirmation = ! showConfirmation" x-bind:aria-label="showConfirmation ? 'Sembunyikan konfirmasi kata sandi' : 'Tampilkan konfirmasi kata sandi'">
                    <svg x-show="! showConfirmation" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A10.8 10.8 0 0 1 12 5c5.2 0 8.7 4.2 9.5 6-.3.8-1.2 2.1-2.7 3.4M6.2 6.2C3.9 7.5 2.8 9.7 2.5 11c.8 1.8 4.3 6 9.5 6 1 0 1.9-.2 2.7-.5" /></svg>
                    <svg x-show="showConfirmation" x-cloak viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12S6 6 12 6s9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="register-error" />
        </div>

        <div class="register-submit-wrap mt-6">
            <button class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center space-x-2" type="submit">
                <span>Daftar Sebagai Mitra</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </div>

        <div class="text-center mt-4 text-xs text-slate-500">
            Sudah punya akun mitra? 
            <a href="{{ route('login') }}" class="font-bold text-emerald-600 hover:underline">Masuk di sini</a>
        </div>
    </form>
</x-guest-layout>
