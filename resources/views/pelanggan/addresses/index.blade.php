<x-pwa-layout title="Daftar Alamat - Pijat.In" :hideHeader="true">
    <div class="-mx-4 -mt-3 -mb-8 flex flex-col min-h-screen">

        <!-- Fixed Top Bar Navigasi (Selalu Mengambang di Atas) -->
        <div class="fixed top-0 left-0 right-0 max-w-md mx-auto z-20 px-5 pt-4 pb-2 flex items-center justify-between pointer-events-none">
            <a href="{{ route('profile.edit') }}" 
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto"
               title="Kembali"
               aria-label="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <span class="text-xs font-bold text-white uppercase tracking-widest drop-shadow-xs">
                Alamat Saya
            </span>

            <a href="{{ route('pelanggan.help.index') }}" 
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto"
               title="Pusat Bantuan"
               aria-label="Pusat Bantuan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </a>
        </div>

        <!-- 1. FIXED GREEN HERO BACKGROUND (Full & Fluid Wave) -->
        <div class="fixed inset-0 max-w-md mx-auto z-0 bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] text-white overflow-hidden pointer-events-none">
            <!-- Fluid Wave Kurva Ombak Background -->
            <div class="absolute inset-0 opacity-20">
                <svg class="w-full h-full" viewBox="0 0 400 600" fill="none" preserveAspectRatio="none">
                    <path d="M-20 60 C80 120 180 0 300 80 C360 120 420 100 450 140 L450 600 L-20 600 Z" fill="white" opacity="0.3"/>
                    <path d="M-20 120 C100 200 220 80 340 180 C380 210 420 190 450 220 L450 600 L-20 600 Z" fill="white" opacity="0.4"/>
                </svg>
            </div>
        </div>

        <!-- Spacer Tinggi Hero di Atas (Compact Opsi 1: 80px) -->
        <div class="h-[80px] shrink-0 pointer-events-none"></div>

        <!-- 2. OVERLAPPING WHITE BOTTOM SHEET (Maksimal Ruang Scroll) -->
        <div class="relative z-10 bg-white rounded-t-[32px] px-5 pt-5 pb-28 shadow-2xl flex-1 min-h-[calc(100vh-80px)] space-y-4">

            <!-- Top Header & Add Button -->
            <div class="flex items-center justify-between gap-3 pb-1">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 leading-tight">Daftar Alamat Tersimpan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kelola alamat penjemputan layanan pijat Anda.</p>
                </div>
                <a href="{{ route('pelanggan.addresses.create') }}" 
                    class="min-h-[42px] px-4 py-2 rounded-full bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-700/20 active:scale-95 transition flex items-center space-x-1.5 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah</span>
                </a>
            </div>

            <!-- Status Alerts -->
            @if (session('status'))
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                    <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Notice Box regarding Primary Address (DEC-02 / CUS-23) -->
            <div class="p-3.5 bg-emerald-50/80 border border-emerald-200 rounded-2xl flex items-start space-x-3 text-xs text-emerald-900">
                <svg class="w-4 h-4 text-emerald-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="leading-relaxed text-[11px]">
                    <strong>Alamat Utama</strong> menentukan wilayah operasional akun Anda. Mengubah alamat utama akan langsung mengalihkan wilayah layanan Anda.
                </div>
            </div>

            <!-- Addresses List -->
            @if ($addresses->isEmpty())
                <div class="text-center py-10 px-4 bg-slate-50/70 rounded-3xl border border-slate-200/80 shadow-xs space-y-3 my-2">
                    <div class="w-16 h-16 rounded-full bg-emerald-100/80 text-emerald-700 mx-auto flex items-center justify-center shadow-xs">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Belum Ada Alamat Tersimpan</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto leading-relaxed">
                            Simpan alamat rumah atau tempat Anda tinggal untuk mulai memesan layanan pijat profesional.
                        </p>
                    </div>
                    <div class="pt-1">
                        <a href="{{ route('pelanggan.addresses.create') }}"
                            class="inline-flex items-center min-h-[44px] px-6 py-2.5 rounded-full bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-700/20 hover:bg-emerald-800 active:scale-95 transition space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Tambah Alamat Sekarang</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($addresses as $addr)
                        <div class="bg-white rounded-2xl p-4 border transition {{ $addr->is_primary ? 'border-emerald-600 shadow-sm ring-1 ring-emerald-600' : 'border-slate-200/90 hover:border-slate-300 shadow-xs' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-800">
                                        {{ $addr->label }}
                                    </span>
                                    @if ($addr->is_primary)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <svg class="w-3 h-3 mr-1 text-emerald-700" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Alamat Utama
                                        </span>
                                    @endif
                                </div>

                                <span class="text-xs font-semibold text-slate-500">
                                    {{ $addr->region?->name ?? 'Wilayah Aktif' }}
                                </span>
                            </div>

                            <div class="mt-2.5 text-xs text-slate-800 font-medium leading-relaxed">
                                {{ $addr->address_text }}
                            </div>

                            @if ($addr->benchmark)
                                <div class="mt-1 text-xs text-slate-500 flex items-center">
                                    <span class="font-semibold text-slate-700 mr-1">Patokan:</span>
                                    <span>{{ $addr->benchmark }}</span>
                                </div>
                            @endif

                            <!-- Card Actions -->
                            <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                <div>
                                    @if (! $addr->is_primary)
                                        <form action="{{ route('pelanggan.addresses.set-primary', $addr) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="min-h-[40px] text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline cursor-pointer">
                                                Jadikan Alamat Utama
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-500 font-medium">Alamat aktif saat ini</span>
                                    @endif
                                </div>

                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('pelanggan.addresses.edit', $addr) }}" 
                                        class="min-h-[40px] inline-flex items-center text-xs font-semibold text-slate-700 hover:text-emerald-700 transition cursor-pointer">
                                        Ubah
                                    </a>

                                    <form action="{{ route('pelanggan.addresses.destroy', $addr) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="min-h-[40px] text-xs font-semibold text-rose-600 hover:text-rose-800 transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

    </div>
</x-pwa-layout>
