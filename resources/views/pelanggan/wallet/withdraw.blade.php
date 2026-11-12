<x-pwa-layout title="Tarik Saldo - Pijat.In" :hideHeader="true">
    <div class="-mx-4 -mt-3 -mb-8 flex flex-col min-h-screen"
         x-data="{
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

        <!-- Fixed Top Bar Navigasi Mengambang -->
        <div class="fixed top-0 left-0 right-0 max-w-md mx-auto z-20 px-5 pt-4 pb-2 flex items-center justify-between pointer-events-none">
            <a href="{{ route('pelanggan.wallet.index') }}" 
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto"
               title="Kembali ke Dompet"
               aria-label="Kembali ke Dompet">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <span class="text-xs font-bold text-white uppercase tracking-widest drop-shadow-xs">
                Tarik Saldo
            </span>

            <a href="{{ route('pelanggan.help.index') }}" 
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto"
               title="Bantuan Penarikan"
               aria-label="Pusat Bantuan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </a>
        </div>

        <!-- 1. FIXED GREEN HERO CONTAINER (KONSISTEN DENGAN DOMPET LIVIN') -->
        <div class="fixed inset-0 max-w-md mx-auto z-0 bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] text-white pt-16 pb-10 px-5 overflow-hidden">
            <!-- Fluid Wave Kurva Ombak Background -->
            <div class="absolute inset-0 pointer-events-none opacity-20">
                <svg class="w-full h-full" viewBox="0 0 400 600" fill="none" preserveAspectRatio="none">
                    <path d="M-20 60 C80 120 180 0 300 80 C360 120 420 100 450 140 L450 600 L-20 600 Z" fill="white" opacity="0.3"/>
                    <path d="M-20 120 C100 200 220 80 340 180 C380 210 420 190 450 220 L450 600 L-20 600 Z" fill="white" opacity="0.4"/>
                </svg>
            </div>

            <!-- Info Saldo di Tengah (Clean & Minimalist, Tanpa Kartu Rekening) -->
            <div class="relative z-10 flex flex-col items-center text-center pt-2 sm:pt-4">

                <!-- Label Saldo Dompet Saat Ini -->
                <div>
                    <h2 class="text-xs font-black text-emerald-100 uppercase tracking-widest drop-shadow-xs">
                        Saldo Dompet Saat Ini
                    </h2>
                </div>

                <!-- Nominal Saldo Besar -->
                <div class="mt-2">
                    <div class="text-3xl sm:text-4xl font-black text-white tracking-tight font-mono drop-shadow-sm">
                        {{ $wallet->formatted_balance }}
                    </div>
                </div>

                <!-- Limit Penarikan Badge Info -->
                <div class="mt-2.5 inline-flex items-center space-x-3 px-3.5 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-[11px] text-emerald-100">
                    <span>Min: <strong class="text-white font-mono">Rp {{ number_format($minAmount, 0, ',', '.') }}</strong></span>
                    <span class="text-white/40">•</span>
                    <span>Maks: <strong class="text-white font-mono">Rp {{ number_format($maxAmount, 0, ',', '.') }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Spacer Tinggi Kartu Hijau -->
        <div class="h-[230px] shrink-0 pointer-events-none"></div>

        <!-- 2. OVERLAPPING WHITE BOTTOM SHEET (FORMULIR INPUT PENARIKAN) -->
        <div class="relative z-10 bg-white rounded-t-[32px] px-5 pt-3 pb-24 shadow-2xl flex-1 space-y-4">
            <!-- Sheet Drag Handle Bar -->
            <div class="w-10 h-1 bg-slate-300 rounded-full mx-auto"></div>

            @if ($errors->any())
                <div class="rounded-2xl bg-rose-50 border border-rose-200 p-3.5 text-xs text-rose-800 shadow-xs">
                    <div class="font-bold mb-1 flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <span>Perhatian</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($hasPending)
                <div class="rounded-2xl bg-amber-50 border border-amber-200 p-3.5 text-xs text-amber-800 flex items-start gap-2.5 shadow-xs">
                    <svg class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <span class="font-bold block">Pengajuan Sedang Diproses</span>
                        <span class="text-[11px] leading-relaxed">Anda masih memiliki permohonan penarikan yang sedang menunggu verifikasi admin wilayah.</span>
                    </div>
                </div>
            @endif

            <!-- Form Penarikan -->
            <form action="{{ route('pelanggan.wallet.withdraw.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Section 1: Data Rekening Bank Tujuan -->
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 space-y-3.5 shadow-xs">
                    <div class="flex items-center space-x-2 border-b border-slate-200/60 pb-2.5">
                        <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Rekening Bank Tujuan</h3>
                    </div>

                    <div>
                        <label for="bank_name" class="text-xs font-bold text-slate-700 block mb-1.5">Nama Bank Tujuan</label>
                        <select id="bank_name"
                                name="bank_name"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-xs cursor-pointer"
                                {{ $hasPending ? 'disabled' : '' }}
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
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-800 font-mono placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-xs"
                               {{ $hasPending ? 'disabled' : '' }}
                               required>
                    </div>

                    <div>
                        <label for="account_holder_name" class="text-xs font-bold text-slate-700 block mb-1.5">Nama Pemilik Rekening</label>
                        <input type="text"
                               id="account_holder_name"
                               name="account_holder_name"
                               value="{{ old('account_holder_name', $user->name) }}"
                               placeholder="Nama sesuai buku rekening"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-xs"
                               {{ $hasPending ? 'disabled' : '' }}
                               required>
                        <span class="text-[10px] text-slate-400 mt-1 block">Nama pemilik rekening wajib sesuai dengan nama akun Anda: <strong>{{ $user->name }}</strong>.</span>
                    </div>
                </div>

                <!-- Section 2: Nominal Penarikan -->
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 space-y-3.5 shadow-xs">
                    <div class="flex items-center space-x-2 border-b border-slate-200/60 pb-2.5">
                        <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <label for="amount" class="text-xs font-black text-slate-900 uppercase tracking-wider block">Nominal Penarikan (Rp)</label>
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
                               class="w-full pl-11 pr-3.5 py-3 rounded-xl border border-slate-200 bg-white text-base font-black text-slate-900 font-mono placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-xs"
                               {{ $hasPending || $wallet->balance < $minAmount ? 'disabled' : '' }}
                               required>
                    </div>

                    @if (! $hasPending && $wallet->balance >= $minAmount)
                        <div class="flex flex-wrap gap-1.5 pt-0.5">
                            @foreach ([50000, 100000, 200000, 500000] as $preset)
                                @if ($preset <= $wallet->balance)
                                    <button type="button"
                                            @click="setAmount({{ $preset }})"
                                            :class="amount == {{ $preset }} ? 'bg-[#064e3b] text-white border-[#064e3b] shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'"
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
                            <span>Biaya Admin</span>
                            <span class="font-bold text-slate-900 font-mono">Rp {{ number_format($fee, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-slate-200/80 text-sm font-black text-slate-900">
                            <span>Dana yang Diterima</span>
                            <span class="text-emerald-700 font-mono text-base" x-text="'Rp ' + netAmount.toLocaleString('id-ID')">Rp 0</span>
                        </div>
                    </div>

                    <div class="pt-1">
                        <label for="notes" class="text-[11px] font-bold text-slate-600 block mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea id="notes"
                                  name="notes"
                                  rows="2"
                                  placeholder="Misal: Penarikan sisa saldo dompet"
                                  class="w-full p-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-xs"
                                  {{ $hasPending ? 'disabled' : '' }}></textarea>
                    </div>
                </div>

                <div class="text-[11px] text-slate-400 space-y-1 px-1">
                    <p>• Permohonan penarikan ditinjau manual oleh admin wilayah untuk memastikan keamanan transaksi.</p>
                    <p>• Rekening penerima wajib menggunakan nama yang sama dengan nama akun Anda.</p>
                </div>

                <button type="submit"
                        class="w-full py-3.5 px-4 bg-[#064e3b] hover:bg-emerald-900 disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-xs uppercase tracking-wider rounded-2xl shadow-lg transition active:scale-95 cursor-pointer"
                        :disabled="!isValid || {{ $hasPending ? 'true' : 'false' }}">
                    Ajukan Penarikan Dana
                </button>
            </form>
        </div>
    </div>
</x-pwa-layout>
