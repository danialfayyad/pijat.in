<x-pwa-layout title="{{ $pendingWithdrawal ? 'Status Penarikan - Mitra Pijat.In' : 'Tarik Saldo - Mitra Pijat.In' }}" :hideHeader="true" :fullBleed="true">
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

        <!-- 1. GREEN HERO HEADER (SESUAI DESAIN CUSTOMER) -->
        <div class="relative bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] text-white px-5 sm:px-6 pt-5 pb-10 sm:pb-12 overflow-hidden shrink-0">
            <!-- Fluid Wave Kurva Ombak Background -->
            <div class="absolute inset-0 pointer-events-none opacity-20">
                <svg class="w-full h-full" viewBox="0 0 400 300" fill="none" preserveAspectRatio="none">
                    <path d="M-20 60 C80 120 180 0 300 80 C360 120 420 100 450 140 L450 300 L-20 300 Z" fill="white" opacity="0.3" />
                    <path d="M-20 120 C100 200 220 80 340 180 C380 210 420 190 450 220 L450 300 L-20 300 Z" fill="white" opacity="0.4" />
                </svg>
            </div>

            <!-- Top Bar Navigasi -->
            <div class="relative z-10 flex items-center justify-between mb-1">
                <a href="{{ route('mitra.finance.index') }}"
                    class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer"
                    title="Kembali ke Saldo" aria-label="Kembali ke Saldo">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <span class="text-xs font-bold text-white uppercase tracking-widest drop-shadow-xs">
                    {{ $pendingWithdrawal ? 'Status Penarikan' : 'Tarik Saldo' }}
                </span>

                <a href="{{ route('mitra.profile.bank') }}"
                    class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer"
                    title="Rekening Bank" aria-label="Rekening Bank">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </a>
            </div>

            <!-- Info Saldo di Tengah -->
            <div class="relative z-10 flex flex-col items-center text-center py-4 sm:py-5">
                <h2 class="text-xs font-bold text-emerald-100 uppercase tracking-widest drop-shadow-xs">
                    Saldo Tersedia
                </h2>

                <div class="mt-2">
                    <div class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight font-mono drop-shadow-sm">
                        Rp {{ number_format($wallet->balance, 0, ',', '.') }}
                    </div>
                </div>

                <!-- Limit Penarikan Badge Info -->
                <div class="mt-3.5 inline-flex items-center space-x-3 px-4 py-1.5 rounded-full bg-white/12 backdrop-blur-md border border-white/20 text-xs text-emerald-100 shadow-2xs">
                    <span>Min: <strong class="text-white font-mono">Rp {{ number_format($minAmount, 0, ',', '.') }}</strong></span>
                    <span class="text-white/40">•</span>
                    <span>Maks: <strong class="text-white font-mono">Rp {{ number_format($maxAmount, 0, ',', '.') }}</strong></span>
                </div>
            </div>
        </div>

        <!-- 2. WHITE CONTENT AREA (BAGIAN DALAM BERSIH & RAPI SESUAI CUSTOMER) -->
        <div class="relative z-10 -mt-2.5 bg-white rounded-t-3xl px-5 pt-6 pb-8 flex-1 min-h-0 overflow-y-auto space-y-4">

            @if ($errors->any())
                <div class="rounded-2xl bg-rose-50 border border-rose-200 p-3.5 text-xs text-rose-800 shadow-2xs">
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
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
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
                                <span>Nominal Penarikan</span>
                                <span class="font-mono font-semibold text-slate-900">Rp {{ number_format($pendingWithdrawal->amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Biaya Layanan</span>
                                @if (($pendingWithdrawal->fee ?? 0) > 0)
                                    <span class="font-mono font-semibold text-slate-900">Rp {{ number_format($pendingWithdrawal->fee, 0, ',', '.') }}</span>
                                @else
                                    <span class="font-semibold text-emerald-700">Gratis (Rp 0)</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between text-slate-900 font-bold pt-1.5 border-t border-slate-100">
                                <span>Total Ditransfer ke Rekening</span>
                                <span class="font-mono text-emerald-700 font-bold">Rp {{ number_format($pendingWithdrawal->net_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500 pt-2 border-t border-slate-100">
                                <span>Kode Pengajuan</span>
                                <span class="font-mono text-slate-600 font-semibold">{{ $pendingWithdrawal->withdrawal_code }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Waktu Pengajuan</span>
                                <span class="text-slate-600">{{ $pendingWithdrawal->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 text-center">
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Pengajuan Anda sedang diverifikasi admin wilayah. Saldo akan otomatis ditransfer ke rekening bank Anda setelah disetujui.
                            </p>
                        </div>
                    </div>

                    <!-- Tombol Aksi Bersih -->
                    <div class="space-y-2 pt-1">
                        <form id="cancel-withdrawal-form" method="POST" action="{{ route('mitra.finance.withdraw.cancel', $pendingWithdrawal) }}">
                            @csrf
                            <button type="button"
                                    onclick="confirmCancelWithdrawal('cancel-withdrawal-form')"
                                    class="w-full py-2.5 px-4 rounded-xl border border-rose-200 bg-white hover:bg-rose-50 text-rose-600 font-semibold text-xs transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Batalkan Pengajuan Penarikan</span>
                            </button>
                        </form>

                        <a href="{{ route('mitra.finance.index') }}"
                           class="w-full py-2 text-center text-xs font-medium text-slate-500 hover:text-slate-700 block transition">
                            Kembali ke Saldo & Keuangan
                        </a>
                    </div>
                </div>
            @else
                <!-- FORM PENARIKAN NORMAL -->
                <form id="withdraw-submit-form" action="{{ route('mitra.finance.withdraw.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Section: Data Rekening Bank Tujuan Terverifikasi -->
                    <div class="bg-slate-50/70 rounded-2xl p-4 border border-slate-100 space-y-2.5 shadow-2xs">
                        <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Rekening Tujuan</h3>
                            </div>
                            <span class="text-xs font-semibold text-emerald-700 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Terverifikasi
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <div>
                                <p class="font-bold text-slate-900 text-sm">{{ $bankAccount->bank_name }}</p>
                                <p class="font-mono text-slate-700 font-semibold">{{ $bankAccount->account_number }}</p>
                                <p class="text-slate-500 text-[11px]">a.n. {{ $bankAccount->account_holder_name }}</p>
                            </div>
                            <a href="{{ route('mitra.profile.bank') }}" class="text-xs font-bold text-emerald-700 hover:underline">
                                Ubah
                            </a>
                        </div>
                    </div>

                    <!-- Section: Input Nominal Penarikan -->
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs space-y-3">
                        <label for="amount" class="text-xs font-bold text-slate-900 block">Nominal Penarikan (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                            <input type="number" id="amount" name="amount" x-model="amount" step="1000" min="{{ $minAmount }}"
                                max="{{ $maxAmount }}" placeholder="Contoh: 100000"
                                class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold font-mono text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                {{ $wallet->balance < $minAmount ? 'disabled' : '' }} required>
                        </div>

                        <!-- Quick amount chips -->
                        @if ($wallet->balance >= $minAmount)
                            <div class="flex flex-wrap gap-1.5 pt-0.5">
                                @foreach ([50000, 100000, 200000, 500000] as $preset)
                                    @if ($preset <= $wallet->balance)
                                        <button type="button" @click="setAmount({{ $preset }})"
                                            class="px-2.5 py-1 text-xs rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 transition cursor-pointer font-medium">
                                            Rp {{ number_format($preset, 0, ',', '.') }}
                                        </button>
                                    @endif
                                @endforeach
                                <button type="button" @click="withdrawAll()"
                                    class="px-2.5 py-1 text-xs rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-semibold transition cursor-pointer">
                                    Tarik Maksimal
                                </button>
                            </div>
                        @endif

                        <!-- Rincian Pencairan -->
                        <div class="pt-3 border-t border-slate-100 space-y-1.5 text-xs text-slate-600">
                            <div class="flex justify-between">
                                <span>Nominal Penarikan</span>
                                <span class="font-semibold text-slate-900 font-mono"
                                    x-text="'Rp ' + (parseFloat(amount) || 0).toLocaleString('id-ID')">Rp 0</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Biaya Admin</span>
                                @if ($fee > 0)
                                    <span class="font-semibold text-slate-900 font-mono">Rp {{ number_format($fee, 0, ',', '.') }}</span>
                                @else
                                    <span class="font-semibold text-emerald-700">Gratis (Rp 0)</span>
                                @endif
                            </div>
                            <div class="flex justify-between pt-1.5 border-t border-slate-100 text-slate-900 font-bold">
                                <span>Total Diterima</span>
                                <span class="text-emerald-700 font-mono"
                                    x-text="'Rp ' + netAmount.toLocaleString('id-ID')">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Warning jika saldo kurang dari minimal -->
                    @if ($wallet->balance < $minAmount)
                        <div class="p-3 bg-amber-50 rounded-2xl border border-amber-200/80 text-center">
                            <p class="text-xs text-amber-800">
                                Saldo Anda belum mencukupi batas minimal penarikan (Min. Rp {{ number_format($minAmount, 0, ',', '.') }}).
                            </p>
                        </div>
                    @endif

                    <!-- Submit Button -->
                    <button type="submit" :disabled="!isValid"
                        :class="isValid ? 'bg-emerald-700 hover:bg-emerald-800 text-white shadow-xs' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                        class="w-full py-3 rounded-xl font-bold text-xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Konfirmasi Tarik Saldo</span>
                    </button>
                </form>
            @endif

        </div>

    </div>

    @push('scripts')
        <script>
            function confirmCancelWithdrawal(formId) {
                const form = document.getElementById(formId);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batalkan Penarikan?',
                        text: 'Apakah Anda yakin ingin membatalkan pengajuan penarikan dana ini?',
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
                } else if (confirm('Apakah Anda yakin ingin membatalkan pengajuan penarikan dana ini?')) {
                    if (form) form.submit();
                }
            }
        </script>
    @endpush
</x-pwa-layout>
