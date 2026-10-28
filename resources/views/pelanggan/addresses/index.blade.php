<x-pwa-layout title="Daftar Alamat - Pijat.In" headerTitle="Alamat Saya" :backUrl="route('pelanggan.dashboard')">
    <div class="py-2 space-y-4">

        <!-- Top Header & Add Button -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 leading-tight">Daftar Alamat Tersimpan</h2>
                <p class="text-xs text-slate-500">Kelola alamat penjemputan layanan pijat Anda.</p>
            </div>
            <a href="{{ route('pelanggan.addresses.create') }}" 
                class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition flex items-center space-x-1.5 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah</span>
            </a>
        </div>

        <!-- Status Alerts -->
        @if (session('status'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold shadow-xs flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold shadow-xs flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Notice Box regarding Primary Address (DEC-02 / CUS-23) -->
        <div class="p-3 bg-emerald-50/60 border border-emerald-100 rounded-2xl flex items-start space-x-2.5 text-xs text-emerald-800">
            <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="leading-relaxed text-[11px]">
                <strong>Alamat Utama</strong> menentukan wilayah operasional akun Anda. Mengubah alamat utama akan langsung mengalihkan wilayah layanan Anda.
            </div>
        </div>

        <!-- Addresses List -->
        @if ($addresses->isEmpty())
            <div class="text-center py-12 px-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Belum Ada Alamat Tersimpan</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">
                        Simpan alamat rumah atau tempat Anda tinggal untuk mulai memesan layanan pijat profesional.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('pelanggan.addresses.create') }}"
                        class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold shadow-xs hover:bg-emerald-700 transition space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah Alamat Sekarang</span>
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($addresses as $addr)
                    <div class="bg-white rounded-2xl p-4 border transition {{ $addr->is_primary ? 'border-emerald-500 shadow-sm ring-1 ring-emerald-500/20' : 'border-slate-200/80 hover:border-slate-300 shadow-xs' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700">
                                    {{ $addr->label }}
                                </span>
                                @if ($addr->is_primary)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Alamat Utama
                                    </span>
                                @endif
                            </div>

                            <span class="text-[11px] font-semibold text-slate-400">
                                {{ $addr->region?->name ?? 'Wilayah Aktif' }}
                            </span>
                        </div>

                        <div class="mt-2.5 text-xs text-slate-800 font-medium leading-relaxed">
                            {{ $addr->address_text }}
                        </div>

                        @if ($addr->benchmark)
                            <div class="mt-1 text-[11px] text-slate-500 flex items-center">
                                <span class="font-semibold mr-1">Patokan:</span>
                                <span>{{ $addr->benchmark }}</span>
                            </div>
                        @endif

                        <!-- Card Actions -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <div>
                                @if (! $addr->is_primary)
                                    <form action="{{ route('pelanggan.addresses.set-primary', $addr) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                            Jadikan Alamat Utama
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Alamat aktif saat ini</span>
                                @endif
                            </div>

                            <div class="flex items-center space-x-3">
                                <a href="{{ route('pelanggan.addresses.edit', $addr) }}" 
                                    class="text-xs font-semibold text-slate-600 hover:text-emerald-600 transition">
                                    Ubah
                                </a>

                                <form action="{{ route('pelanggan.addresses.destroy', $addr) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-700 transition">
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
</x-pwa-layout>

