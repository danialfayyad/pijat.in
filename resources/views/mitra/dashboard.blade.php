<x-pwa-layout title="Portal Mitra - Pijat.In" headerTitle="Mitra Pijat.In">
    <div class="py-2 space-y-4">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Profile Greeting & Region Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-700 text-white font-extrabold text-base flex items-center justify-center shadow-xs">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium">Halo, Terapis</span>
                    <h2 class="text-base font-black text-slate-900 leading-tight">{{ $user->name }}</h2>
                    <span class="text-xs font-semibold text-emerald-800 block mt-0.5">
                        Wilayah: <strong>{{ $profile?->region?->name ?? 'Terdaftar' }}</strong>
                    </span>
                </div>
            </div>

            <div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Terverifikasi
                </span>
            </div>
        </div>

        <!-- Work Status Toggle Card (MIT-20) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl {{ $profile?->is_active ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center transition-all shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-slate-500 block">Status Kesiapan</span>
                        <div class="text-sm font-black text-slate-900 flex items-center space-x-1.5">
                            <span>{{ $profile?->is_active ? 'Online (Aktif)' : 'Istirahat (Nonaktif)' }}</span>
                            <span class="w-2 h-2 rounded-full {{ $profile?->is_active ? 'bg-emerald-600' : 'bg-slate-300' }}"></span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('mitra.toggle-active') }}" method="POST">
                    @csrf
                    <input type="hidden" name="is_active" value="{{ $profile?->is_active ? '0' : '1' }}">
                    <button type="submit" 
                        class="min-h-[44px] px-4 py-2 rounded-xl font-bold text-xs shadow-xs transition flex items-center space-x-1.5 cursor-pointer {{ $profile?->is_active ? 'bg-slate-100 hover:bg-slate-200 text-slate-800' : 'bg-emerald-700 hover:bg-emerald-800 text-white' }}">
                        <span>{{ $profile?->is_active ? 'Istirahat' : 'Aktifkan' }}</span>
                    </button>
                </form>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-2.5">
                {{ $profile?->is_active ? 'Radar aktif: Anda siap menerima penawaran pesanan baru dari pelanggan sesama gender.' : 'Mode istirahat: Anda tidak akan menerima tawaran pesanan baru saat ini.' }}
            </p>
        </div>

        <!-- Quick Metrics Cards -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs">
                <span class="text-xs font-medium text-slate-500 block">Saldo Dompet</span>
                <div class="text-xl font-black text-slate-900 mt-1 font-mono">Rp 0</div>
                <span class="text-xs text-emerald-800 font-semibold block mt-1">Bank: {{ $user->primaryBankAccount?->bank_name ?? '-' }}</span>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs">
                <span class="text-xs font-medium text-slate-500 block">Status Kepatuhan</span>
                <div class="text-xl font-black text-emerald-800 mt-1">Bebas SP</div>
                <span class="text-xs text-slate-500 block mt-1">Rekam jejak bersih</span>
            </div>
        </div>

        <!-- Active / Scheduled Orders Placeholder -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-800">Pesanan Hari Ini</h3>
                <span class="text-xs font-semibold text-slate-500 font-mono">0 Pesanan</span>
            </div>
            <div class="py-8 text-center text-slate-500 text-xs">
                <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Belum ada pesanan aktif. Pastikan status kerja <strong>Online</strong> untuk menerima pesanan masuk.
            </div>
        </div>

    </div>
</x-pwa-layout>
