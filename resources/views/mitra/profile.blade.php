<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800 leading-tight">
                Profil & Pengaturan Akun Mitra
            </h2>
            <a href="{{ route('mitra.dashboard') }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                ← Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold shadow-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-900 flex items-start space-x-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <strong>Aturan Keamanan Kemitraan (MIT-20):</strong>
                Perubahan nomor rekening bank atau titik alamat domisili akan otomatis memicu <strong>verifikasi ulang</strong> oleh Admin Wilayah dan membekukan sementara penarikan saldo demi keamanan dana Anda.
            </div>
        </div>

        <form action="{{ route('mitra.profile.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Identitas Pribadi -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2">Informasi Pribadi</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIK (Terkunci)</label>
                        <input type="text" value="{{ $user->nik }}" disabled
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 text-sm font-mono cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email (Terkunci)</label>
                        <input type="email" value="{{ $user->email }}" disabled
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 text-sm cursor-not-allowed">
                    </div>
                </div>
            </div>

            <!-- Rekening Bank -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2">Rekening Bank Pencairan Dana</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Bank</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $user->primaryBankAccount?->bank_name) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Rekening</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $user->primaryBankAccount?->account_number) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pemilik Rekening</label>
                        <input type="text" name="account_holder_name" value="{{ old('account_holder_name', $user->primaryBankAccount?->account_holder_name) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                    </div>
                </div>
            </div>

            <!-- Ganti Password -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2">Ubah Kata Sandi (Kosongkan jika tidak diubah)</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Baru</label>
                        <input type="password" name="password" autocomplete="new-password"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
