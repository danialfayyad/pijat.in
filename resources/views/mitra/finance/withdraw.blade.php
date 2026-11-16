<x-pwa-layout title="Tarik Saldo - Mitra Pijat.In" headerTitle="Tarik Saldo" :backUrl="route('mitra.finance.index')">
    <div class="space-y-4 pb-6" x-data="{
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
        @if ($errors->any())
            <div class="rounded-xl bg-rose-50 border border-rose-200 p-3.5 text-xs text-rose-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($pendingWithdrawal)
            <!-- TAMPILAN KHUSUS: PENGAJUAN SEDANG DIPROSES -->
            <div class="space-y-4">
                <div class="bg-white rounded-2xl p-5 border border-amber-200/90 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200/80 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>Menunggu Verifikasi Admin</span>
                        </span>
                        <span
                            class="text-[11px] font-mono text-slate-500 font-semibold">{{ $pendingWithdrawal->withdrawal_code }}</span>
                    </div>

                    <div>
                        <span class="text-xs font-semibold text-slate-500 block">Nominal Penarikan</span>
                        <div class="text-3xl font-extrabold text-slate-900 mt-1">
                            Rp {{ number_format($pendingWithdrawal->amount, 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- Detail Rekening Tujuan -->
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Rekening Tujuan</span>
                            <span class="font-bold text-slate-800">{{ $pendingWithdrawal->bank_name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Nomor Rekening</span>
                            <span class="font-mono font-bold text-slate-800">{{ $pendingWithdrawal->account_number }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Nama Pemilik</span>
                            <span class="font-bold text-slate-800">{{ $pendingWithdrawal->account_holder_name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 pt-1.5 border-t border-slate-200/70">
                            <span>Waktu Pengajuan</span>
                            <span
                                class="text-slate-700">{{ $pendingWithdrawal->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Permohonan penarikan dana Anda sedang dalam antrean verifikasi oleh admin wilayah. Anda dapat
                        membatalkan pengajuan ini sewaktu-waktu sebelum disetujui, dan saldo akan langsung dikembalikan ke
                        saldo tersedia Anda.
                    </p>
                </div>

                <!-- Tombol Aksi -->
                <div class="space-y-2.5 pt-2">
                    <form id="cancel-mitra-withdrawal-form" method="POST" action="{{ route('mitra.finance.withdraw.cancel', $pendingWithdrawal) }}">
                        @csrf
                        <button type="button"
                            onclick="confirmCancelWithdrawal('cancel-mitra-withdrawal-form')"
                            class="w-full py-3.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-[0.99] text-white font-bold text-xs transition cursor-pointer shadow-xs flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Batalkan Pengajuan Penarikan</span>
                        </button>
                    </form>

                    <a href="{{ route('mitra.finance.index') }}"
                        class="w-full py-3 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition text-center block">
                        Kembali ke Keuangan
                    </a>
                </div>
            </div>
        @else
            <!-- Card Saldo Tersedia -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Saldo Tersedia untuk Ditarik</span>
                <div class="text-2xl font-extrabold text-slate-900">
                    Rp {{ number_format($wallet->balance, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Min: Rp {{ number_format($minAmount, 0, ',', '.') }}</span>
                    <span>Maks: Rp {{ number_format($maxAmount, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Rekening Tujuan -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-900">Rekening Tujuan</span>
                    <span class="text-xs font-semibold text-emerald-700 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Terverifikasi
                    </span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl text-xs space-y-0.5 border border-slate-100">
                    <p class="font-bold text-slate-900">{{ $bankAccount->bank_name }}</p>
                    <p class="font-mono text-slate-700">{{ $bankAccount->account_number }}</p>
                    <p class="text-slate-500 text-[11px]">a.n. {{ $bankAccount->account_holder_name }}</p>
                </div>
                <p class="text-[11px] text-slate-400">
                    Dana hanya dapat ditarik ke rekening bank atas nama mitra yang telah diverifikasi oleh admin wilayah.
                </p>
            </div>

            <!-- Form Penarikan -->
            <form action="{{ route('mitra.finance.withdraw.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs space-y-3">
                    <label for="amount" class="text-xs font-bold text-slate-900 block">Nominal Penarikan (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                        <input type="number" id="amount" name="amount" x-model="amount" step="1000" min="{{ $minAmount }}"
                            max="{{ $maxAmount }}" placeholder="Contoh: 100000"
                            class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            {{ $wallet->balance < $minAmount ? 'disabled' : '' }} required>
                    </div>

                    <!-- Quick amount chips -->
                    @if ($wallet->balance >= $minAmount)
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach ([50000, 100000, 200000, 500000] as $preset)
                                @if ($preset <= $wallet->balance)
                                    <button type="button" @click="setAmount({{ $preset }})"
                                        class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 transition">
                                        Rp {{ number_format($preset, 0, ',', '.') }}
                                    </button>
                                @endif
                            @endforeach
                            <button type="button" @click="withdrawAll()"
                                class="px-2.5 py-1 text-xs rounded-lg border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-semibold transition">
                                Tarik Maksimal
                            </button>
                        </div>
                    @endif

                    <!-- Rincian Pencairan -->
                    <div class="pt-3 border-t border-slate-100 space-y-1.5 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span>Nominal Penarikan</span>
                            <span class="font-semibold text-slate-900"
                                x-text="'Rp ' + (parseFloat(amount) || 0).toLocaleString('id-ID')">Rp 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Biaya Admin</span>
                            <span class="font-semibold text-slate-900">Rp {{ number_format($fee, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-slate-100 text-sm font-bold text-slate-900">
                            <span>Dana yang Diterima</span>
                            <span class="text-emerald-600" x-text="'Rp ' + netAmount.toLocaleString('id-ID')">Rp 0</span>
                        </div>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div class="pt-2">
                        <label for="notes" class="text-[11px] font-medium text-slate-600 block mb-1">Catatan Tambahan
                            (Opsional)</label>
                        <textarea id="notes" name="notes" rows="2" placeholder="Misal: Penarikan pendapatan minggu ke-1"
                            class="w-full p-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>
                </div>

                <div class="text-[11px] text-slate-400 space-y-1 px-1">
                    <p>• Permohonan penarikan akan ditinjau dan disetujui secara manual oleh admin wilayah.</p>
                    <p>• Setelah disetujui, dana akan ditransfer langsung ke rekening bank Anda.</p>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs rounded-xl shadow-xs transition active:scale-95 cursor-pointer"
                    :disabled="!isValid">
                    Ajukan Penarikan Dana
                </button>
            </form>
        @endif
    </div>

    @push('scripts')
        <script>
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