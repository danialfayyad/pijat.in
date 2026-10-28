<x-pwa-layout title="Dompet Saldo - Pijat.In" headerTitle="Dompet Saldo" :backUrl="route('pelanggan.dashboard')">
    <div class="py-2 space-y-4">

        <!-- Status Alerts -->
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Main Wallet Balance Card -->
        <div class="bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-800 rounded-2xl p-5 text-white shadow-md relative overflow-hidden">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <span class="text-xs font-medium text-emerald-100 block">Saldo Dompet Anda</span>
                    <div class="text-3xl font-black tracking-tight mt-1 font-mono">
                        {{ $wallet->formatted_balance }}
                    </div>
                </div>

                <a href="{{ route('pelanggan.wallet.topup') }}" 
                    class="min-h-[44px] px-4 py-2.5 rounded-xl bg-white text-emerald-800 text-xs font-extrabold shadow-sm hover:bg-emerald-50 active:scale-95 transition flex items-center space-x-1.5 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Isi Saldo</span>
                </a>
            </div>

            <div class="mt-5 pt-3.5 border-t border-emerald-600/60 flex items-center justify-between text-xs text-emerald-100/90 relative z-10">
                <div class="flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Wilayah: <strong>{{ $user->region?->name ?? 'Belum ada alamat' }}</strong></span>
                </div>
                <span class="text-[11px] text-emerald-200 font-medium">Buku Kas Aman</span>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Riwayat Mutasi Saldo</h3>
                    <p class="text-xs text-slate-500">Catatan transaksi keluar dan masuk dompet Anda</p>
                </div>
                <span class="text-xs font-semibold text-slate-600 font-mono">
                    {{ $transactions->total() }} Transaksi
                </span>
            </div>

            @if ($transactions->isEmpty())
                <div class="text-center py-12 px-4 bg-white rounded-2xl border border-slate-200/90 shadow-xs space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-700 mx-auto flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs text-slate-900">Belum Ada Riwayat Transaksi</h4>
                        <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                            Isi saldo dompet Anda untuk kemudahan pembayaran instan tanpa uang tunai.
                        </p>
                    </div>
                    <div class="pt-1">
                        <a href="{{ route('pelanggan.wallet.topup') }}"
                            class="inline-flex items-center min-h-[44px] px-4 py-2 rounded-xl bg-emerald-700 text-white text-xs font-bold shadow-xs hover:bg-emerald-800 transition cursor-pointer">
                            <span>Mulai Isi Saldo</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs divide-y divide-slate-100 overflow-hidden">
                    @foreach ($transactions as $tx)
                        <div class="p-3.5 flex items-center justify-between hover:bg-slate-50/70 transition">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $tx->isCredit() ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    @if ($tx->isCredit())
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 pr-2">
                                    <h4 class="text-xs font-bold text-slate-900 truncate leading-snug">{{ $tx->description }}</h4>
                                    <div class="flex items-center space-x-1.5 text-xs text-slate-500 mt-0.5">
                                        <span>{{ $tx->created_at->translatedFormat('d M Y, H:i') }}</span>
                                        @if ($tx->region)
                                            <span>•</span>
                                            <span class="text-slate-600 font-medium">{{ $tx->region->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-black font-mono block {{ $tx->isCredit() ? 'text-emerald-700' : 'text-slate-900' }}">
                                    {{ $tx->formatted_amount }}
                                </span>
                                <span class="text-[11px] text-slate-500 font-mono block mt-0.5">
                                    Sisa: Rp {{ number_format((float) $tx->balance_after, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-1">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>

    </div>
</x-pwa-layout>

