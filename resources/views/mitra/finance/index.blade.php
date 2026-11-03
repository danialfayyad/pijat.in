<x-pwa-layout title="Saldo & Keuangan - Mitra Pijat.In" headerTitle="Saldo & Keuangan">
    <div class="space-y-4 pb-6">
        @if (session('status'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3.5 text-xs text-emerald-800 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl bg-rose-50 border border-rose-200 p-3.5 text-xs text-rose-800 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-rose-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Card Saldo Tersedia -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-4 text-white shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></div>
                    <span class="text-xs font-semibold text-slate-300">Saldo Tersedia</span>
                </div>
                <span class="text-[11px] px-2 py-0.5 rounded-md bg-white/10 text-slate-200 font-medium">Bisa Ditarik</span>
            </div>

            <div class="text-2xl font-extrabold tracking-tight mb-4 text-white">
                Rp {{ number_format($wallet->balance, 0, ',', '.') }}
            </div>

            <div class="grid grid-cols-2 gap-2 pt-3 border-t border-white/10 text-xs">
                <div>
                    <span class="text-slate-400 text-[10px] block">Pendapatan Bulan Ini</span>
                    <span class="font-bold text-emerald-400">Rp {{ number_format($currentMonthIncome, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] block">Total Dicairkan</span>
                    <span class="font-bold text-slate-200">Rp {{ number_format($totalWithdrawn, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-white/10 flex gap-2">
                <a href="{{ route('mitra.finance.withdraw') }}"
                   class="flex-1 py-2.5 px-3 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl text-center shadow-md transition active:scale-95 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Tarik Saldo
                </a>
            </div>
        </div>

        <!-- Rekening Terverifikasi -->
        <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-900">Rekening Pencairan</span>
                @if ($bankAccount)
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Terverifikasi
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        Belum Ada
                    </span>
                @endif
            </div>

            @if ($bankAccount)
                <div class="text-xs text-slate-700 space-y-0.5">
                    <p class="font-semibold text-slate-900">{{ $bankAccount->bank_name }}</p>
                    <p class="font-mono text-slate-600">{{ $bankAccount->account_number }}</p>
                    <p class="text-slate-500 text-[11px]">a.n. {{ $bankAccount->account_holder_name }}</p>
                </div>
            @else
                <p class="text-xs text-slate-500">
                    Rekening bank diperlukan untuk melakukan penarikan saldo. Silakan lengkapi pada formulir verifikasi mitra.
                </p>
            @endif
        </div>

        <!-- Riwayat Permohonan Penarikan -->
        @if ($withdrawalRequests->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs">
                <h3 class="text-xs font-bold text-slate-900 mb-2.5">Riwayat Pengajuan Penarikan</h3>
                <div class="space-y-2">
                    @foreach ($withdrawalRequests as $wd)
                        <div class="p-2.5 rounded-lg border border-slate-100 bg-slate-50/70 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-[11px] font-semibold text-slate-700">{{ $wd->withdrawal_code }}</span>
                                @if ($wd->isPending())
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 ring-2 ring-amber-100"></span>
                                        Menunggu Verifikasi
                                    </span>
                                @elseif ($wd->isApproved())
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 ring-2 ring-emerald-100"></span>
                                        Dicairkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-rose-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 ring-2 ring-rose-100"></span>
                                        Ditolak
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between mt-1 text-slate-500 text-[11px]">
                                <span>{{ $wd->created_at->format('d M Y, H:i') }}</span>
                                <span class="font-bold text-slate-900">Rp {{ number_format($wd->amount, 0, ',', '.') }}</span>
                            </div>
                            @if ($wd->isRejected() && $wd->rejection_reason)
                                <div class="mt-1.5 pt-1.5 border-t border-slate-200/60 text-[10px] text-rose-600">
                                    Alasan: {{ $wd->rejection_reason }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Riwayat Mutasi Dompet -->
        <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold text-slate-900">Mutasi Dompet</h3>
                <!-- Range Tabs -->
                <div class="flex items-center gap-1 p-0.5 bg-slate-100 rounded-lg text-[10px]">
                    <a href="{{ route('mitra.finance.index', ['range' => '7_days']) }}"
                       class="px-2 py-1 rounded-md transition {{ $range === '7_days' ? 'bg-white font-bold text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        7 Hari
                    </a>
                    <a href="{{ route('mitra.finance.index', ['range' => '30_days']) }}"
                       class="px-2 py-1 rounded-md transition {{ $range === '30_days' ? 'bg-white font-bold text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        30 Hari
                    </a>
                    <a href="{{ route('mitra.finance.index', ['range' => 'all']) }}"
                       class="px-2 py-1 rounded-md transition {{ $range === 'all' ? 'bg-white font-bold text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
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
                        <div class="py-2.5 flex items-start justify-between gap-3 text-xs">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-slate-800 truncate">{{ $tx->description }}</p>
                                <span class="text-[10px] text-slate-400 block mt-0.5">
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

                <div class="mt-3 pt-3 border-t border-slate-100">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-pwa-layout>

