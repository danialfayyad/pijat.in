<x-pwa-layout title="Keamanan Kata Sandi - Pijat.In" headerTitle="Keamanan Sandi" :backUrl="route('mitra.profile')">
    <div class="py-2 space-y-4">
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Perbarui Kata Sandi</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Gunakan kombinasi minimal 8 karakter dengan huruf dan angka untuk keamanan akun kemitraan Anda.
                </p>
            </div>

            <form method="POST" action="{{ route('mitra.profile.security.update') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                    </label>
                    <input type="password"
                           name="current_password"
                           required
                           autocomplete="current-password"
                           class="w-full text-xs rounded-xl border border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Kata Sandi Baru <span class="text-rose-500">*</span>
                    </label>
                    <input type="password"
                           name="password"
                           required
                           autocomplete="new-password"
                           class="w-full text-xs rounded-xl border border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                    </label>
                    <input type="password"
                           name="password_confirmation"
                           required
                           autocomplete="new-password"
                           class="w-full text-xs rounded-xl border border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-2.5">
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer">
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-pwa-layout>

