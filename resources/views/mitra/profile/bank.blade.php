<x-pwa-layout title="Rekening Bank Mitra - Pijat.In" headerTitle="Rekening Pencairan" :backUrl="route('mitra.profile')">
    <div class="py-2 space-y-4">
        <!-- Warning Banner MIT-20 -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-950 flex items-start space-x-3 shadow-2xs">
            <svg class="w-5 h-5 text-amber-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="leading-relaxed">
                <strong>Ketentuan Keamanan Rekening (MIT-20):</strong>
                Mengubah nomor rekening bank akan membekukan sementara penarikan saldo dan membutuhkan <strong>verifikasi ulang</strong> oleh Admin Wilayah untuk memastikan keamanan dana kemitraan Anda.
            </div>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Rekening Tujuan Pencairan</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pastikan nama pemilik rekening sama dengan nama resmi pada identitas KTP Anda.
                </p>
            </div>

            <form method="POST" action="{{ route('mitra.profile.bank.update') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Bank <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           name="bank_name"
                           value="{{ old('bank_name', $user->primaryBankAccount?->bank_name) }}"
                           required
                           placeholder="Contoh: BCA, Mandiri, BRI, BNI"
                           class="w-full text-xs rounded-xl border border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nomor Rekening <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           name="account_number"
                           value="{{ old('account_number', $user->primaryBankAccount?->account_number) }}"
                           required
                           placeholder="Masukkan nomor rekening valid"
                           class="w-full text-xs rounded-xl border border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-2.5 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Pemilik Rekening <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           name="account_holder_name"
                           value="{{ old('account_holder_name', $user->primaryBankAccount?->account_holder_name) }}"
                           required
                           placeholder="Nama sesuai buku tabungan"
                           class="w-full text-xs rounded-xl border border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-2.5">
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer">
                        Simpan Rekening Bank
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-pwa-layout>

