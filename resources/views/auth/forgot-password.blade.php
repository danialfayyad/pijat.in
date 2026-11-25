<x-guest-layout>
    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs sm:text-sm text-emerald-800 font-medium leading-relaxed flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs sm:text-sm text-rose-800 font-medium leading-relaxed flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <div x-data="{
        countdown: 0,
        isSubmitting: false,
        hasSent: {{ session('status') ? 'true' : 'false' }},
        init() {
            @if (session('status'))
                localStorage.setItem('pw_reset_cooldown', Date.now() + 60000);
                this.hasSent = true;
            @endif

            const target = parseInt(localStorage.getItem('pw_reset_cooldown') || '0', 10);
            const remaining = Math.max(0, Math.ceil((target - Date.now()) / 1000));
            if (remaining > 0) {
                this.countdown = remaining;
                this.hasSent = true;
                this.runTimer();
            }
        },
        runTimer() {
            const interval = setInterval(() => {
                const target = parseInt(localStorage.getItem('pw_reset_cooldown') || '0', 10);
                const remaining = Math.max(0, Math.ceil((target - Date.now()) / 1000));
                this.countdown = remaining;
                if (remaining <= 0) {
                    clearInterval(interval);
                    localStorage.removeItem('pw_reset_cooldown');
                }
            }, 1000);
        },
        submitForm(e) {
            if (this.countdown > 0 || this.isSubmitting) {
                e.preventDefault();
                return;
            }
            this.isSubmitting = true;
        }
    }">
        <form method="POST" action="{{ route('password.email') }}" @submit="submitForm($event)" class="space-y-5 sm:space-y-6">
            @csrf

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
                       autofocus 
                       autocomplete="email"
                       placeholder="nama@email.com"
                       class="w-full h-12 px-4 rounded-xl border border-slate-300 text-sm sm:text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition bg-white @error('email') border-rose-400 focus:ring-rose-500 @enderror">
                @error('email')
                    <p class="mt-2 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button with Countdown -->
            <button type="submit" 
                    :disabled="countdown > 0 || isSubmitting"
                    :class="countdown > 0 || isSubmitting 
                        ? 'bg-slate-100 border border-slate-200 text-slate-400 cursor-not-allowed shadow-none' 
                        : 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white cursor-pointer shadow-xs'"
                    class="w-full h-12 rounded-xl font-semibold text-sm sm:text-base transition flex items-center justify-center gap-2">
                <template x-if="isSubmitting">
                    <span>Mengirim...</span>
                </template>
                <template x-if="!isSubmitting && countdown > 0">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="'Kirim ulang dalam ' + countdown + ' dtk'"></span>
                    </span>
                </template>
                <template x-if="!isSubmitting && countdown === 0">
                    <span x-text="hasSent ? 'Kirim Ulang Tautan Reset' : 'Kirim Tautan Reset'"></span>
                </template>
            </button>
        </form>
    </div>

    <!-- Back to Login Link -->
    <div class="mt-8 pt-6 border-t border-slate-100 text-center text-xs sm:text-sm text-slate-600">
        Ingat kata sandi? 
        <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 hover:underline transition ml-1">
            Masuk di sini
        </a>
    </div>
</x-guest-layout>
