<x-pwa-layout title="Tarik Saldo - Pijat.In" headerTitle="Tarik Saldo" :backUrl="route('pelanggan.wallet.index')">
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

        @if ($hasPending)
            <div class="rounded-xl bg-amber-50 border border-amber-200 p-3.5 text-xs text-amber-800 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <span class="font-bold block">Pengajuan Sedang Diproses</span>
                    <span>Anda masih memiliki permohonan penarikan yang sedang menunggu verifikasi admin wilayah.</span>
                </div>
            </div>
        @endif

        <!-- Card Saldo Tersedia -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs">
            <span class="text-xs text-slate-500 block mb-1">Saldo Dompet Saat Ini</span>
            <div class="text-2xl font-black text-slate-900 font-mono">
                {{ $wallet->formatted_balance }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Minimal: Rp {{ number_format($minAmount, 0, ',', '.') }}</span>
                <span>Maksimal: Rp {{ number_format($maxAmount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Form Penarikan -->
        <form action="{{ route('pelanggan.wallet.withdraw.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Data Rekening Bank -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-900 border-b border-slate-100 pb-2">Rekening Bank Tujuan</h3>

                <div>
                    <label for="bank_name" class="text-xs font-medium text-slate-700 block mb-1">Nama Bank / E-Wallet</label>
                    <input type="text"
                           id="bank_name"
                           name="bank_name"
                           value="{{ old('bank_name') }}"
                           placeholder="Contoh: BCA, Mandiri, BRI, BNI"
                           class="w-full p-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                           {{ $hasPending ? 'disabled' : '' }}
                           required>
                </div>

                <div>
                    <label for="account_number" class="text-xs font-medium text-slate-700 block mb-1">Nomor Rekening</label>
                    <input type="text"
                           id="account_number"
                           name="account_number"
                           value="{{ old('account_number') }}"
                           placeholder="Contoh: 1234567890"
                           class="w-full p-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                           {{ $hasPending ? 'disabled' : '' }}
                           required>
                </div>

                <div>
                    <label for="account_holder_name" class="text-xs font-medium text-slate-700 block mb-1">Nama Pemilik Rekening</label>
                    <input type="text"
                           id="account_holder_name"
                           name="account_holder_name"
                           value="{{ old('account_holder_name', $user->name) }}"
                           placeholder="Nama sesuai buku rekening"
                           class="w-full p-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                           {{ $hasPending ? 'disabled' : '' }}
                           required>
                    <span class="text-[10px] text-slate-400 mt-1 block">Nama pemilik rekening wajib sesuai dengan nama akun Anda.</span>
                </div>
            </div>

            <!-- Nominal Penarikan -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-xs space-y-3">
                <label for="amount" class="text-xs font-bold text-slate-900 block">Nominal Penarikan (Rp)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                    <input type="number"
                           id="amount"
                           name="amount"
                           x-model="amount"
                           step="1000"
                           min="{{ $minAmount }}"
                           max="{{ $maxAmount }}"
                           placeholder="Contoh: 100000"
                           class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                           {{ $hasPending || $wallet->balance < $minAmount ? 'disabled' : '' }}
                           required>
                </div>

                @if (! $hasPending && $wallet->balance >= $minAmount)
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        @foreach ([50000, 100000, 200000, 500000] as $preset)
                            @if ($preset <= $wallet->balance)
                                <button type="button"
                                        @click="setAmount({{ $preset }})"
                                        class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 transition">
                                    Rp {{ number_format($preset, 0, ',', '.') }}
                                </button>
                            @endif
                        @endforeach
                        <button type="button"
                                @click="withdrawAll()"
                                class="px-2.5 py-1 text-xs rounded-lg border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-semibold transition">
                            Tarik Semua
                        </button>
                    </div>
                @endif

                <!-- Rincian Pencairan -->
                <div class="pt-3 border-t border-slate-100 space-y-1.5 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Nominal Penarikan</span>
                        <span class="font-semibold text-slate-900" x-text="'Rp ' + (parseFloat(amount) || 0).toLocaleString('id-ID')">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Biaya Admin</span>
                        <span class="font-semibold text-slate-900">Rp {{ number_format($fee, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between pt-1 border-t border-slate-100 text-sm font-bold text-slate-900">
                        <span>Dana yang Diterima</span>
                        <span class="text-emerald-700" x-text="'Rp ' + netAmount.toLocaleString('id-ID')">Rp 0</span>
                    </div>
                </div>

                <div class="pt-2">
                    <label for="notes" class="text-[11px] font-medium text-slate-600 block mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea id="notes"
                              name="notes"
                              rows="2"
                              placeholder="Misal: Penarikan sisa saldo dompet"
                              class="w-full p-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                              {{ $hasPending ? 'disabled' : '' }}></textarea>
                </div>
            </div>

            <div class="text-[11px] text-slate-400 space-y-1 px-1">
                <p>• Permohonan penarikan ditinjau manual oleh admin wilayah untuk memastikan keamanan transaksi.</p>
                <p>• Rekening penerima wajib menggunakan nama yang sama dengan nama akun Anda.</p>
            </div>

            <button type="submit"
                    class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs rounded-xl shadow-md transition active:scale-95"
                    :disabled="!isValid || {{ $hasPending ? 'true' : 'false' }}">
                Ajukan Penarikan Dana
            </button>
        </form>
    </div>
</x-pwa-layout>

