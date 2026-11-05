<x-pwa-layout title="Saldo & Keuangan - Mitra Pijat.In" headerTitle="Saldo & Keuangan">
    <div class="space-y-4 pb-6">
        @if (session('status'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200/80 p-3.5 text-xs text-emerald-900 font-semibold shadow-2xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl bg-rose-50 border border-rose-200/80 p-3.5 text-xs text-rose-900 font-semibold shadow-2xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Card Saldo Tersedia -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs space-y-4">
            <div>
                <span class="text-xs font-semibold text-slate-500 block">Saldo Tersedia</span>
                <div class="text-3xl font-extrabold tracking-tight text-slate-900 mt-1">
                    Rp {{ number_format($wallet->balance, 0, ',', '.') }}
                </div>
            </div>

            <!-- Performa Keuangan -->
            <div class="grid grid-cols-2 divide-x divide-slate-100 pt-3 border-t border-slate-100 text-center">
                <div>
                    <div class="text-base font-bold text-slate-900">
                        Rp {{ number_format($currentMonthIncome, 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5 font-medium">Pendapatan Bulan Ini</div>
                </div>
                <div>
                    <div class="text-base font-bold text-slate-900">
                        Rp {{ number_format($totalWithdrawn, 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5 font-medium">Total Dicairkan</div>
                </div>
            </div>

            <!-- Tombol Tarik Saldo -->
            <div class="pt-1">
                <a href="{{ route('mitra.finance.withdraw') }}"
                   class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl text-center shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Tarik Saldo</span>
                </a>
            </div>
        </div>

        @if (($wallet->held_balance ?? 0) > 0)
            <!-- Card Saldo Tertahan (24 Jam Escrow) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">Saldo Tertahan (24 Jam)</span>
                    <span class="text-xs font-semibold text-amber-600">Masa Tunggu</span>
                </div>

                <div class="text-2xl font-extrabold text-slate-900">
                    Rp {{ number_format($wallet->held_balance, 0, ',', '.') }}
                </div>

                <p class="text-xs text-slate-500 leading-relaxed">
                    Pendapatan pesanan selesai ditahan selama 24 jam sebagai masa jaminan komplain pelanggan. Saldo akan otomatis dipindahkan ke saldo tersedia setelah 24 jam jika tidak ada aduan aktif.
                </p>

                @if ($heldTransactions->isNotEmpty())
                    <div class="pt-3 border-t border-slate-100 divide-y divide-slate-100">
                        @foreach ($heldTransactions as $ht)
                            <div class="py-2.5 flex items-center justify-between text-xs">
                                <div class="min-w-0 flex-1 mr-2">
                                    <span class="font-semibold text-slate-800 block truncate">{{ $ht->description }}</span>
                                    <span class="text-[11px] text-slate-400">
                                        Selesai: {{ $ht->created_at->format('d M, H:i') }}
                                    </span>
                                </div>
                                <span class="font-bold text-amber-600 shrink-0">
                                    +Rp {{ number_format($ht->amount, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- Rekening Pencairan -->
        <div class="space-y-1.5">
            <h3 class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-1">Rekening Pencairan</h3>
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    @if ($bankAccount)
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-slate-900">{{ $bankAccount->bank_name }}</span>
                            <span class="font-mono text-xs text-slate-500">{{ $bankAccount->account_number }}</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">a.n. {{ $bankAccount->account_holder_name }}</p>
                    @else
                        <p class="text-xs text-slate-500">Belum ada rekening pencairan terdaftar.</p>
                    @endif
                </div>
                <div class="shrink-0 pl-3">
                    @if ($bankAccount)
                        <span class="text-xs font-semibold text-emerald-700 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Terverifikasi
                        </span>
                    @else
                        <a href="{{ route('mitra.profile.bank') }}" class="text-xs font-bold text-emerald-700 hover:underline">
                            Tambah Rekening
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Riwayat Pengajuan Penarikan -->
        @if ($withdrawalRequests->isNotEmpty())
            <div class="space-y-1.5">
                <h3 class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-1">Riwayat Pengajuan Penarikan</h3>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                    @foreach ($withdrawalRequests as $wd)
                        <div class="p-4 flex items-center justify-between">
                            <div class="min-w-0 flex-1 mr-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-semibold text-slate-700">{{ $wd->withdrawal_code }}</span>
                                    @if ($wd->isPending())
                                        <span class="text-[11px] font-semibold text-amber-600">Menunggu Verifikasi</span>
                                    @elseif ($wd->isApproved())
                                        <span class="text-[11px] font-semibold text-emerald-700">Dicairkan</span>
                                    @else
                                        <span class="text-[11px] font-semibold text-rose-600">Ditolak</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $wd->created_at->format('d M Y, H:i') }}</p>
                                @if ($wd->isRejected() && $wd->rejection_reason)
                                    <p class="text-[11px] text-rose-600 mt-1">Alasan: {{ $wd->rejection_reason }}</p>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-sm font-bold text-slate-900">Rp {{ number_format($wd->amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Riwayat Mutasi Dompet -->
        <div class="space-y-1.5">
            <h3 class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-1">Mutasi Dompet</h3>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <!-- Filter Bar -->
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-900">Riwayat Transaksi</span>
                    <div class="flex items-center gap-1 p-0.5 bg-slate-100 rounded-lg text-[10px]">
                        <a href="{{ route('mitra.finance.index', ['range' => '7_days']) }}"
                           class="px-2 py-1 rounded-md transition {{ $range === '7_days' ? 'bg-white font-bold text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                            7 Hari
                        </a>
                        <a href="{{ route('mitra.finance.index', ['range' => '30_days']) }}"
                           class="px-2 py-1 rounded-md transition {{ $range === '30_days' ? 'bg-white font-bold text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                            30 Hari
                        </a>
                        <a href="{{ route('mitra.finance.index', ['range' => 'all']) }}"
                           class="px-2 py-1 rounded-md transition {{ $range === 'all' ? 'bg-white font-bold text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                            Semua
                        </a>
                    </div>
                </div>

                @if ($transactions->isEmpty())
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Belum ada riwayat transaksi pada periode ini.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($transactions as $tx)
                            <div class="p-4 flex items-start justify-between gap-3 text-xs">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-800 truncate">{{ $tx->description }}</p>
                                    <span class="text-[11px] text-slate-400 block mt-0.5">
                                        {{ $tx->created_at->format('d M Y, H:i') }}
                                    </span>
                                </div>
                                <div class="text-right shrink-0">
                                    @if ($tx->type === 'credit')
                                        <span class="font-bold text-emerald-600 text-xs block">
                                            +Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="font-bold text-slate-900 text-xs block">
                                            -Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                        </span>
                                    @endif
                                    <span class="text-[10px] text-slate-400">
                                        Saldo: Rp {{ number_format($tx->balance_after, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($transactions->hasPages())
                        <div class="p-3 border-t border-slate-100">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-pwa-layout>
