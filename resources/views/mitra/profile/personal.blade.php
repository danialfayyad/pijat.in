<x-pwa-layout title="Data Diri Mitra - Pijat.In" headerTitle="Data Diri Mitra" :backUrl="route('mitra.profile')">
    <div class="py-2 space-y-4">
        <!-- Error & Feedback -->
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Ubah Data Pribadi</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Nama lengkap dan nomor kontak yang dapat dihubungi pelanggan saat pesanan berlangsung.
                </p>
            </div>

            <form method="POST" action="{{ route('mitra.profile.personal.update') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           required
                           class="w-full text-xs rounded-xl border border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nomor WhatsApp / HP Aktif <span class="text-rose-500">*</span>
                    </label>
                    <input type="tel"
                           name="phone"
                           value="{{ old('phone', $user->phone) }}"
                           required
                           class="w-full text-xs rounded-xl border border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-2.5 font-mono">
                </div>

                <!-- Read-only Verified Identifiers -->
                <div class="pt-2 border-t border-slate-100 space-y-3">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Identitas Terkunci (Sesuai e-KTP)</span>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Nomor Induk Kependudukan (NIK)</label>
                        <input type="text"
                               value="{{ $user->nik }}"
                               disabled
                               class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-500 py-2.5 font-mono cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Alamat Email Terdaftar</label>
                        <input type="email"
                               value="{{ $user->email }}"
                               disabled
                               class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-500 py-2.5 cursor-not-allowed">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-pwa-layout>

