<x-pwa-layout title="Profil Mitra - Pijat.In" headerTitle="Profil Mitra" :backUrl="route('mitra.dashboard')">
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

        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-950 flex items-start space-x-3">
            <svg class="w-5 h-5 text-amber-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="leading-relaxed">
                <strong>Aturan Keamanan Kemitraan (MIT-20):</strong>
                Perubahan nomor rekening bank atau titik alamat domisili akan otomatis memicu <strong>verifikasi ulang</strong> oleh Admin Wilayah dan membekukan sementara penarikan saldo demi keamanan dana Anda.
            </div>
        </div>

        <!-- Saldo & Keuangan Mitra (MIT-15) -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Saldo Dompet Kemitraan</span>
                <div class="flex items-baseline space-x-1 mt-0.5">
                    <span class="text-xl font-black font-mono text-slate-900">
                        Rp {{ number_format($user->wallet?->balance ?? 0, 0, ',', '.') }}
                    </span>
                    @if (($user->wallet?->held_balance ?? 0) > 0)
                        <span class="text-[10px] text-amber-700 font-bold ml-1.5">
                            (+Rp {{ number_format($user->wallet->held_balance, 0, ',', '.') }} tertahan)
                        </span>
                    @endif
                </div>
            </div>
            <div>
                <a href="{{ route('mitra.finance.index') }}"
                   class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Dompet & Tarik Saldo</span>
                </a>
            </div>
        </div>

        <!-- Status Poin Sanksi & Banding (MIT-18) -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Poin Sanksi Kemitraan (SP)</span>
                <div class="flex items-baseline space-x-1 mt-0.5">
                    <span class="text-xl font-black font-mono {{ $user->penalty_points > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                        {{ $user->penalty_points }}
                    </span>
                    <span class="text-[11px] text-slate-400 font-bold">/ 150 Pts</span>
                </div>
            </div>
            <div>
                <a href="{{ route('mitra.penalties.index') }}"
                   class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition cursor-pointer {{ $user->penalty_points > 0 ? 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>{{ $user->penalty_points > 0 ? 'Banding / Detail Sanksi' : 'Lihat Riwayat Sanksi' }}</span>
                </a>
            </div>
        </div>

        <!-- Wilayah Operasional & Relokasi (MIT-22) -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Wilayah Operasional Utama</span>
                <div class="flex items-center space-x-1.5 mt-0.5">
                    <span class="text-sm font-bold text-slate-900">
                        {{ $user->partnerProfile?->region?->name ?? 'Belum Ditentukan' }}
                    </span>
                    @if ($user->hasPendingRelocation())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                            Menunggu Verifikasi
                        </span>
                    @endif
                </div>
            </div>
            <div>
                <a href="{{ route('mitra.relocation.create') }}"
                   class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition cursor-pointer bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100">
                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Pindah Wilayah</span>
                </a>
            </div>
        </div>

        <form action="{{ route('mitra.profile.update') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="services_submitted" value="1">

            <!-- Identitas Pribadi -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 border-b border-slate-100 pb-2">Informasi Pribadi</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 font-mono transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIK (Terkunci)</label>
                        <input type="text" value="{{ $user->nik }}" disabled
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-xs font-mono cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email (Terkunci)</label>
                        <input type="email" value="{{ $user->email }}" disabled
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-xs cursor-not-allowed">
                    </div>
                </div>
            </div>

            <!-- Rekening Bank -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 border-b border-slate-100 pb-2">Rekening Bank Pencairan Dana</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Bank</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $user->primaryBankAccount?->bank_name) }}"
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Rekening</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $user->primaryBankAccount?->account_number) }}"
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 font-mono transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pemilik</label>
                        <input type="text" name="account_holder_name" value="{{ old('account_holder_name', $user->primaryBankAccount?->account_holder_name) }}"
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">
                    </div>
                </div>
            </div>

            <!-- Preferensi Layanan (MIT-08) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900">Preferensi Jenis Layanan</h3>
                        <p class="text-[11px] text-slate-500">Pilih jenis layanan yang bersedia Anda layani (minimal 1 jenis layanan aktif).</p>
                    </div>
                </div>

                @error('services')
                    <p class="text-xs text-rose-600 font-semibold bg-rose-50 p-2.5 rounded-xl border border-rose-200">{{ $message }}</p>
                @enderror

                <div class="space-y-3">
                    @forelse ($services as $service)
                        @php
                            $isEnabled = $user->partnerProfile ? $user->partnerProfile->isServiceEnabled($service->id) : true;
                        @endphp
                        <label class="flex items-start justify-between p-3.5 rounded-xl border border-slate-200/90 hover:border-emerald-300 bg-slate-50/50 hover:bg-emerald-50/20 transition cursor-pointer">
                            <div class="pr-3">
                                <span class="text-xs font-bold text-slate-900 block">{{ $service->name }}</span>
                                @if ($service->description)
                                    <span class="text-[11px] text-slate-500 block mt-0.5 leading-relaxed">{{ $service->description }}</span>
                                @endif
                            </div>
                            <div class="flex items-center min-h-[24px]">
                                <input type="checkbox" name="services[]" value="{{ $service->id }}" {{ $isEnabled ? 'checked' : '' }}
                                    class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer">
                            </div>
                        </label>
                    @empty
                        <p class="text-xs text-slate-500 italic">Belum ada katalog layanan aktif di sistem.</p>
                    @endforelse
                </div>
            </div>

            <!-- Ganti Password -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 border-b border-slate-100 pb-2">Ubah Kata Sandi (Kosongkan jika tidak diubah)</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Baru</label>
                        <input type="password" name="password" autocomplete="new-password"
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">
                    </div>
                </div>
            </div>

            <div class="pt-1">
                <button type="submit" class="w-full min-h-[48px] py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow-sm transition cursor-pointer">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>

        <!-- Keluar dari Akun (Logout Card) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3">
            <div>
                <h3 class="text-xs font-bold text-slate-900">Keluar dari Akun</h3>
                <p class="text-xs text-slate-500">Akhiri sesi aktif Anda pada perangkat ini.</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                    class="w-full min-h-[48px] py-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar dari Akun</span>
                </button>
            </form>
        </div>

    </div>
</x-pwa-layout>
