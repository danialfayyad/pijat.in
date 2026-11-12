<x-guest-layout>
    <form method="POST" action="{{ route('mitra.register') }}" x-data="{ showPassword: false, showConfirmation: false }" class="space-y-5 sm:space-y-6">
        @csrf

        <!-- Nama Lengkap KTP -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2" for="name">
                Nama Lengkap Sesuai KTP <span class="text-rose-500">*</span>
            </label>
            <input id="name" 
                   type="text" 
                   name="name" 
                   value="{{ old('name') }}" 
                   required 
                   autofocus 
                   autocomplete="name"
                   placeholder="Contoh: Budi Santoso"
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

        <!-- Email -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2" for="email">
                Email Aktif <span class="text-rose-500">*</span>
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

        <!-- Nomor Telepon / HP -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2" for="phone">
                Nomor WhatsApp / HP <span class="text-rose-500">*</span>
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
            Daftar Sebagai Mitra
        </button>
    </form>

    <!-- Sign-in Link -->
    <div class="mt-8 pt-6 border-t border-slate-100 space-y-2 text-center text-xs sm:text-sm text-slate-600">
        <div>
            Sudah punya akun mitra? 
            <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 hover:underline transition ml-1">
                Masuk di sini
            </a>
        </div>
        <div>
            Ingin memesan layanan pijat? 
            <a href="{{ route('register') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 hover:underline transition ml-1">
                Daftar Pelanggan
            </a>
        </div>
    </div>
</x-guest-layout>
