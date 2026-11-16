<x-pwa-layout title="{{ $pendingWithdrawal ? 'Status Penarikan - Pijat.In' : 'Tarik Saldo - Pijat.In' }}" :hideHeader="true" :fullBleed="true">
    <div class="flex-1 flex flex-col h-full min-h-0 overflow-hidden bg-white" x-data="{
            amount: '',
            walletBalance: {{ (float) $wallet->balance }},
            minAmount: {{ (float) $minAmount }},
            maxAmount: {{ (float) $maxAmount }},
            fee: {{ (float) $fee }},
            setAmount(val) {
                this.amount = val;
            },
            withdrawAll() {
                let maxPossible = Math.min(this.walletBalance, this.maxAmount);
                this.amount = maxPossible > 0 ? maxPossible : 0;
            },
            get netAmount() {
                let num = parseFloat(this.amount) || 0;
                return Math.max(0, num - this.fee);
            },
            get isValid() {
                let num = parseFloat(this.amount) || 0;
                return num >= this.minAmount && num <= this.maxAmount && num <= this.walletBalance;
            }
         }">

        <!-- 1. GREEN HERO HEADER (TETAP DIPERTAHANKAN) -->
        <div
            class="relative bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] text-white px-5 pt-4 pb-8 overflow-hidden shrink-0">
            <!-- Fluid Wave Kurva Ombak Background -->
            <div class="absolute inset-0 pointer-events-none opacity-20">
                <svg class="w-full h-full" viewBox="0 0 400 300" fill="none" preserveAspectRatio="none">
                    <path d="M-20 60 C80 120 180 0 300 80 C360 120 420 100 450 140 L450 300 L-20 300 Z" fill="white"
                        opacity="0.3" />
                    <path d="M-20 120 C100 200 220 80 340 180 C380 210 420 190 450 220 L450 300 L-20 300 Z" fill="white"
                        opacity="0.4" />
                </svg>
            </div>

            <!-- Top Bar Navigasi -->
            <div class="relative z-10 flex items-center justify-between">
                <a href="{{ route('pelanggan.wallet.index') }}"
                    class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer"
                    title="Kembali ke Dompet" aria-label="Kembali ke Dompet">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <span class="text-xs font-bold text-white uppercase tracking-widest drop-shadow-xs">
                    {{ $pendingWithdrawal ? 'Status Penarikan' : 'Tarik Saldo' }}
                </span>

                <a href="{{ route('pelanggan.help.index') }}"
                    class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer"
                    title="Bantuan Penarikan" aria-label="Pusat Bantuan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </a>
            </div>

            <!-- Info Saldo di Tengah -->
            <div class="relative z-10 flex flex-col items-center text-center py-4">
                <h2 class="text-xs font-bold text-emerald-100 uppercase tracking-widest drop-shadow-xs">
                    Saldo Dompet Saat Ini
                </h2>

                <div class="mt-2">
                    <div class="text-3xl sm:text-4xl font-bold text-white tracking-tight font-mono drop-shadow-sm">
                        {{ $wallet->formatted_balance }}
                    </div>
                </div>

                <!-- Limit Penarikan Badge Info -->
                <div
                    class="mt-2.5 inline-flex items-center space-x-3 px-3.5 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-[11px] text-emerald-100">
                    <span>Min: <strong class="text-white font-mono">Rp
                            {{ number_format($minAmount, 0, ',', '.') }}</strong></span>
                    <span class="text-white/40">•</span>
                    <span>Maks: <strong class="text-white font-mono">Rp
                            {{ number_format($maxAmount, 0, ',', '.') }}</strong></span>
                </div>
            </div>
        </div>

        <!-- 2. WHITE CONTENT AREA (BAGIAN DALAM YANG DIPERBAIKI BERSIH & RAPI) -->
        <div class="relative z-10 -mt-4 bg-white rounded-t-3xl px-5 pt-5 pb-6 flex-1 min-h-0 overflow-y-auto space-y-4">

            @if ($errors->any())
                <div class="rounded-xl bg-rose-50 border border-rose-200 p-3.5 text-xs text-rose-800 shadow-2xs">
                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($pendingWithdrawal)
                <!-- TAMPILAN RESI PENARIKAN AKTIF (CLEAN & MINIMALIST) -->
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 space-y-4">
                        <!-- Header Status & Nominal -->
                        <div class="text-center pb-4 border-b border-slate-100">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200/70 text-xs font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                <span>Menunggu Verifikasi Admin</span>
                            </span>

                            <div class="text-3xl font-extrabold font-mono text-slate-900 tracking-tight mt-3">
                                Rp {{ number_format($pendingWithdrawal->amount, 0, ',', '.') }}
                            </div>
                            <span class="text-xs text-slate-400 mt-1 block font-medium">Pencairan Saldo Dompet</span>
                        </div>

                        <!-- Rincian Rekening & Transaksi Sejajar -->
                        <div class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Bank Tujuan</span>
                                <span class="font-bold text-slate-900">{{ $pendingWithdrawal->bank_name }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Nomor Rekening</span>
                                <span class="font-mono font-semibold text-slate-900">{{ $pendingWithdrawal->account_number }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Nama Pemilik</span>
                                <span class="font-medium text-slate-900">{{ $pendingWithdrawal->account_holder_name }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Biaya Layanan</span>
                                <span class="font-semibold text-emerald-700">Gratis (Rp 0)</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500 pt-2 border-t border-slate-100">
                                <span>Kode Pengajuan</span>
                                <span class="font-mono text-slate-600">{{ $pendingWithdrawal->withdrawal_code }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Waktu Pengajuan</span>
                                <span class="text-slate-600">{{ $pendingWithdrawal->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 text-center">
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Pengajuan Anda sedang diverifikasi admin wilayah. Saldo akan otomatis ditransfer ke rekening setelah disetujui.
                            </p>
                        </div>
                    </div>

                    <!-- Tombol Aksi Bersih -->
                    <div class="space-y-2 pt-1">
                        <form id="cancel-withdrawal-form" method="POST" action="{{ route('pelanggan.wallet.withdraw.cancel', $pendingWithdrawal) }}">
                            @csrf
                            <button type="button"
                                    onclick="confirmCancelWithdrawal('cancel-withdrawal-form')"
                                    class="w-full py-2.5 px-4 rounded-xl border border-rose-200 bg-white hover:bg-rose-50 text-rose-600 font-semibold text-xs transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Batalkan Pengajuan Penarikan</span>
                            </button>
                        </form>

                        <a href="{{ route('pelanggan.wallet.index') }}"
                           class="w-full py-2 text-center text-xs font-medium text-slate-500 hover:text-slate-700 block transition">
                            Kembali ke Dompet
                        </a>
                    </div>
                </div>
            @else
                <!-- FORM PENARIKAN NORMAL -->
                <form id="withdraw-submit-form" action="{{ route('pelanggan.wallet.withdraw.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Section 1: Data Rekening Bank Tujuan -->
                    <div class="bg-slate-50/70 rounded-2xl p-4 border border-slate-100 space-y-3.5 shadow-2xs">
                        <div class="flex items-center space-x-2 border-b border-slate-200/60 pb-2.5">
                            <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Rekening Bank Tujuan</h3>
                        </div>

                        <div>
                            <label for="bank_name" class="text-xs font-bold text-slate-700 block mb-1.5">Nama Bank Tujuan</label>
                            <select id="bank_name"
                                    name="bank_name"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-2xs cursor-pointer"
                                    required>
                                <option value="">-- Pilih Bank Tujuan Resmi --</option>
                                @foreach (\App\Models\Bank::active()->orderBy('sort_order')->orderBy('name')->get() as $bank)
                                    <option value="{{ $bank->name }}" {{ old('bank_name') === $bank->name || old('bank_name') === $bank->code ? 'selected' : '' }}>
                                        {{ $bank->name }} ({{ strtoupper($bank->code) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="account_number" class="text-xs font-bold text-slate-700 block mb-1.5">Nomor Rekening</label>
                            <input type="text"
                                   id="account_number"
                                   name="account_number"
                                   value="{{ old('account_number') }}"
                                   placeholder="Contoh: 1234567890"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-800 font-mono placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-2xs"
                                   required>
                        </div>

                        <div>
                            <label for="account_holder_name" class="text-xs font-bold text-slate-700 block mb-1.5">Nama Pemilik Rekening</label>
                            <input type="text"
                                   id="account_holder_name"
                                   name="account_holder_name"
                                   value="{{ old('account_holder_name', $user->name) }}"
                                   placeholder="Nama sesuai buku rekening"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-2xs"
                                   required>
                            <span class="text-[10px] text-slate-400 mt-1 block">Nama pemilik rekening wajib sesuai nama akun: <strong>{{ $user->name }}</strong></span>
                        </div>
                    </div>

                    <!-- Section 2: Nominal Penarikan -->
                    <div class="bg-slate-50/70 rounded-2xl p-4 border border-slate-100 space-y-3.5 shadow-2xs">
                        <div class="flex items-center space-x-2 border-b border-slate-200/60 pb-2.5">
                            <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <label for="amount" class="text-xs font-bold text-slate-900 uppercase tracking-wider block">Nominal Penarikan (Rp)</label>
                        </div>

                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm font-mono">Rp</span>
                            <input type="number"
                                   id="amount"
                                   name="amount"
                                   x-model="amount"
                                   step="1000"
                                   min="{{ $minAmount }}"
                                   max="{{ $maxAmount }}"
                                   placeholder="Contoh: 100000"
                                   class="w-full pl-11 pr-3.5 py-3 rounded-xl border border-slate-200 bg-white text-base font-black text-slate-900 font-mono placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-2xs"
                                   {{ $wallet->balance < $minAmount ? 'disabled' : '' }}
                                   required>
                        </div>

                        @if ($wallet->balance >= $minAmount)
                            <div class="flex flex-wrap gap-1.5 pt-0.5">
                                @foreach ([50000, 100000, 200000, 500000] as $preset)
                                    @if ($preset <= $wallet->balance)
                                        <button type="button"
                                                @click="setAmount({{ $preset }})"
                                                :class="amount == {{ $preset }} ? 'bg-[#064e3b] text-white border-[#064e3b] shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'"
                                                class="px-3 py-1.5 text-xs font-bold rounded-xl border transition active:scale-95 cursor-pointer">
                                            Rp {{ number_format($preset, 0, ',', '.') }}
                                        </button>
                                    @endif
                                @endforeach
                                <button type="button"
                                        @click="withdrawAll()"
                                        class="px-3 py-1.5 text-xs rounded-xl border border-emerald-400 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold transition active:scale-95 cursor-pointer">
                                    Tarik Semua
                                </button>
                            </div>
                        @endif

                        <!-- Rincian Pencairan -->
                        <div class="pt-3 border-t border-slate-200/80 space-y-2 text-xs text-slate-600">
                            <div class="flex justify-between">
                                <span>Nominal Penarikan</span>
                                <span class="font-bold text-slate-900 font-mono" x-text="'Rp ' + (parseFloat(amount) || 0).toLocaleString('id-ID')">Rp 0</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Biaya Layanan</span>
                                <span class="font-bold text-slate-900 font-mono">Rp {{ number_format($fee, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-slate-200/80 text-sm font-bold text-slate-900">
                                <span>Dana yang Diterima</span>
                                <span class="text-emerald-700 font-mono text-base font-extrabold" x-text="'Rp ' + netAmount.toLocaleString('id-ID')">Rp 0</span>
                            </div>
                        </div>

                        <div class="pt-1">
                            <label for="notes" class="text-[11px] font-bold text-slate-600 block mb-1">Catatan Tambahan (Opsional)</label>
                            <textarea id="notes"
                                      name="notes"
                                      rows="2"
                                      placeholder="Misal: Penarikan sisa saldo dompet"
                                      class="w-full p-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-2xs"></textarea>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-400 px-1">
                        Permohonan penarikan ditinjau manual oleh admin wilayah untuk memastikan keamanan transaksi.
                    </p>

                    <button type="button"
                            @click="confirmSubmitWithdrawal('withdraw-submit-form')"
                            class="w-full py-3.5 px-4 bg-[#064e3b] hover:bg-emerald-900 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm transition active:scale-95 cursor-pointer"
                            :disabled="!isValid">
                        Ajukan Penarikan Dana
                    </button>
                </form>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmSubmitWithdrawal(formId) {
                const form = typeof formId === 'string' ? document.getElementById(formId) : formId;
                if (form && !form.reportValidity()) return;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        iconHtml: '✓',
                        title: 'Ajukan Penarikan Saldo?',
                        text: 'Pastikan rekening tujuan dan nominal penarikan sudah benar.',
                        showCancelButton: true,
                        cancelButtonText: 'Cek Kembali',
                        confirmButtonText: 'Ya, Ajukan',
                        reverseButtons: true,
                        buttonsStyling: false,
                        customClass: {
                            popup: 'swal-admin-theme',
                            confirmButton: 'swal2-confirm',
                            cancelButton: 'swal2-cancel',
                        }
                    }).then((result) => {
                        if (result.isConfirmed && form) {
                            form.submit();
                        }
                    });
                } else if (confirm('Pastikan rekening tujuan dan nominal penarikan sudah benar. Lanjutkan pengajuan penarikan saldo?')) {
                    if (form) form.submit();
                }
            }

            function confirmCancelWithdrawal(target) {
                if (target && target.preventDefault) {
                    target.preventDefault();
                }
                const form = typeof target === 'string' ? document.getElementById(target) : (target?.currentTarget || target?.target || target);

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        iconHtml: '✕',
                        title: 'Batalkan Penarikan?',
                        text: 'Apakah Anda yakin ingin membatalkan penarikan dana ini?',
                        showCancelButton: true,
                        cancelButtonText: 'Kembali',
                        confirmButtonText: 'Ya, Batalkan',
                        reverseButtons: true,
                        buttonsStyling: false,
                        customClass: {
                            popup: 'swal-danger-theme',
                            confirmButton: 'swal2-confirm',
                            cancelButton: 'swal2-cancel',
                        }
                    }).then((result) => {
                        if (result.isConfirmed && form) {
                            form.submit();
                        }
                    });
                } else if (confirm('Apakah Anda yakin ingin membatalkan penarikan dana ini?')) {
                    if (form) form.submit();
                }
            }
        </script>
    @endpush
</x-pwa-layout>