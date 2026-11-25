<x-guest-layout>
    @if (session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs sm:text-sm text-rose-800 font-medium">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" x-data="{ showPassword: false, showConfirmation: false }" class="space-y-5 sm:space-y-6">
        @csrf

        <!-- Nama Lengkap -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2" for="name">
                Nama Lengkap <span class="text-rose-500">*</span>
            </label>
            <input id="name" 
                   type="text" 
                   name="name" 
                   value="{{ old('name') }}" 
                   required 
                   autofocus 
                   autocomplete="name"
                   placeholder="Nama lengkap Anda"
                   class="w-full h-12 px-4 rounded-xl border border-slate-300 text-sm sm:text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition bg-white @error('name') border-rose-400 focus:ring-rose-500 @enderror">
            @error('name')
                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jenis Kelamin -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2">
                Jenis Kelamin <span class="text-rose-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center justify-center gap-2.5 p-3 rounded-xl border border-slate-300 cursor-pointer transition hover:bg-slate-50 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50 has-[:checked]:text-emerald-900">
                    <input type="radio" name="gender" value="laki-laki" @checked(old('gender') === 'laki-laki') required class="text-emerald-600 focus:ring-emerald-500 w-4 h-4 border-slate-300">
                    <span class="text-xs sm:text-sm font-medium">Laki-laki</span>
                </label>
                <label class="flex items-center justify-center gap-2.5 p-3 rounded-xl border border-slate-300 cursor-pointer transition hover:bg-slate-50 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50 has-[:checked]:text-emerald-900">
                    <input type="radio" name="gender" value="perempuan" @checked(old('gender') === 'perempuan') required class="text-emerald-600 focus:ring-emerald-500 w-4 h-4 border-slate-300">
                    <span class="text-xs sm:text-sm font-medium">Perempuan</span>
                </label>
            </div>
            @error('gender')
                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tanggal Lahir (Opsional) -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2" for="date_of_birth">
                Tanggal Lahir <span class="text-slate-400 font-normal">(Opsional)</span>
            </label>
            <input id="date_of_birth" 
                   type="date" 
                   name="date_of_birth" 
                   value="{{ old('date_of_birth') }}" 
                   autocomplete="bday"
                   class="w-full h-12 px-4 rounded-xl border border-slate-300 text-sm sm:text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition bg-white @error('date_of_birth') border-rose-400 focus:ring-rose-500 @enderror">
            @error('date_of_birth')
                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2" for="email">
                Email <span class="text-rose-500">*</span>
            </label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autocomplete="email"
                   placeholder="nama@email.com"
                   class="w-full h-12 px-4 rounded-xl border border-slate-300 text-sm sm:text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition bg-white @error('email') border-rose-400 focus:ring-rose-500 @enderror">
            @error('email')
                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nomor Telepon -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2" for="phone">
                Nomor Telepon / WhatsApp <span class="text-rose-500">*</span>
            </label>
            <input id="phone" 
                   type="tel" 
                   inputmode="tel" 
                   name="phone" 
                   value="{{ old('phone') }}" 
                   required 
                   autocomplete="tel"
                   placeholder="081234567890"
                   class="w-full h-12 px-4 rounded-xl border border-slate-300 text-sm sm:text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition bg-white @error('phone') border-rose-400 focus:ring-rose-500 @enderror">
            @error('phone')
                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kata Sandi -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2" for="password">
                Kata Sandi <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <input id="password" 
                       name="password" 
                       type="password" 
                       required 
                       autocomplete="new-password" 
                       placeholder="Minimal 8 karakter"
                       x-bind:type="showPassword ? 'text' : 'password'"
                       class="w-full h-12 pl-4 pr-12 rounded-xl border border-slate-300 text-sm sm:text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition bg-white @error('password') border-rose-400 focus:ring-rose-500 @enderror">
                <button type="button" 
                        @click="showPassword = !showPassword" 
                        :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition">
                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Konfirmasi Kata Sandi -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2" for="password_confirmation">
                Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <input id="password_confirmation" 
                       name="password_confirmation" 
                       type="password" 
                       required 
                       autocomplete="new-password" 
                       placeholder="Ulangi kata sandi"
                       x-bind:type="showConfirmation ? 'text' : 'password'"
                       class="w-full h-12 pl-4 pr-12 rounded-xl border border-slate-300 text-sm sm:text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition bg-white @error('password_confirmation') border-rose-400 focus:ring-rose-500 @enderror">
                <button type="button" 
                        @click="showConfirmation = !showConfirmation" 
                        :aria-label="showConfirmation ? 'Sembunyikan konfirmasi kata sandi' : 'Tampilkan konfirmasi kata sandi'"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition">
                    <svg x-show="!showConfirmation" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showConfirmation" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            @error('password_confirmation')
                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" 
                class="w-full h-12 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold text-sm sm:text-base shadow-xs transition cursor-pointer flex items-center justify-center">
            Daftar Akun
        </button>
    </form>

    <!-- Divider -->
    <div class="relative my-6 sm:my-7">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-slate-200"></div>
        </div>
        <div class="relative flex justify-center text-xs sm:text-sm">
            <span class="bg-white px-3 text-slate-400 font-medium">atau</span>
        </div>
    </div>

    <!-- Google Button -->
    <a href="{{ route('auth.google.redirect', ['role' => 'pelanggan']) }}" 
       class="w-full h-12 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-700 font-medium text-xs sm:text-sm flex items-center justify-center gap-2.5 transition shadow-xs">
        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" />
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" />
        </svg>
        <span>Daftar dengan Google</span>
    </a>

    <!-- Sign-in Link -->
    <div class="mt-8 pt-6 border-t border-slate-100 space-y-2 text-center text-xs sm:text-sm text-slate-600">
        <div>
            Sudah punya akun? 
            <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 hover:underline transition ml-1">
                Masuk di sini
            </a>
        </div>
        <div>
            Ingin mendaftar sebagai mitra terapis? 
            <a href="{{ route('mitra.register') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 hover:underline transition ml-1">
                Daftar Mitra Terapis
            </a>
        </div>
    </div>
</x-guest-layout>
