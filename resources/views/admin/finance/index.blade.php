<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-800 leading-tight">
                {{ __('Keuangan Wilayah & Penarikan Dana') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.finance.export.csv', request()->query()) }}"
                   class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Ekspor CSV
                </a>
                <a href="{{ route('admin.finance.export.print', request()->query()) }}"
                   target="_blank"
                   class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak / PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="{
        approveModal: false,
        rejectModal: false,
        currentWithdrawal: null,
        openApprove(wd) {
            this.currentWithdrawal = wd;
            this.approveModal = true;
        },
        openReject(wd) {
            this.currentWithdrawal = wd;
            this.rejectModal = true;
        }
    }">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold shadow-xs">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Financial Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Total Arus Masuk (Top-up & Komisi)</span>
                <span class="text-xl font-bold text-emerald-700">Rp {{ number_format($totalInflow, 0, ',', '.') }}</span>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Total Arus Keluar (Beban & Pengeluaran)</span>
                <span class="text-xl font-bold text-slate-800">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</span>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Total Pencairan Disetujui</span>
                <span class="text-xl font-bold text-slate-800">Rp {{ number_format($totalApprovedDisbursement, 0, ',', '.') }}</span>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Antrean Penarikan Tertunda</span>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-bold {{ $pendingWithdrawalCount > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                        {{ $pendingWithdrawalCount }} Permohonan
                    </span>
                    @if ($pendingWithdrawalCount > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Filter Region Bar -->
        @if ($assignedRegions->count() > 1)
            <div class="flex items-center gap-2 bg-white p-3 rounded-2xl border border-slate-200/80 shadow-2xs">
                <span class="text-xs font-semibold text-slate-600">Filter Wilayah:</span>
                <div class="flex flex-wrap gap-1.5">
                    <a href="{{ route('admin.finance.index', array_merge(request()->query(), ['region_id' => null])) }}"
                       class="px-3 py-1 text-xs rounded-lg font-medium transition {{ is_null($selectedRegionId) ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        Semua Wilayah Tugas
                    </a>
                    @foreach ($assignedRegions as $reg)
                        <a href="{{ route('admin.finance.index', array_merge(request()->query(), ['region_id' => $reg->id])) }}"
                           class="px-3 py-1 text-xs rounded-lg font-medium transition {{ $selectedRegionId === $reg->id ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                            {{ $reg->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Tab Controls: Withdrawals vs Ledger -->
        <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
            <a href="{{ route('admin.finance.index', array_merge(request()->query(), ['tab' => 'withdrawals'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $tab === 'withdrawals' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                <span>Antrean Penarikan Dana</span>
                @if ($pendingWithdrawalCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'withdrawals' ? 'bg-white text-emerald-800' : 'bg-amber-500 text-white' }}">
                        {{ $pendingWithdrawalCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.finance.index', array_merge(request()->query(), ['tab' => 'ledger'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'ledger' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Buku Kas Wilayah (Ledger Mutasi)
            </a>
        </div>

        @if ($tab === 'withdrawals')
            <!-- Tab 1: Withdrawal Requests Queue -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
                <!-- Toolbar -->
                <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <!-- Status Filter Tabs -->
                    <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto">
                        @foreach ([
                            'all' => 'Semua Status',
                            'pending' => 'Menunggu Verifikasi',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak'
                        ] as $sKey => $sLabel)
                            <a href="{{ route('admin.finance.index', array_merge(request()->query(), ['status' => $sKey, 'wd_page' => 1])) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition whitespace-nowrap {{ $withdrawalStatus === $sKey ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                {{ $sLabel }}
                            </a>
                        @endforeach
                    </div>

                    <!-- Search Input -->
                    <form method="GET" action="{{ route('admin.finance.index') }}" class="w-full sm:w-72">
                        <input type="hidden" name="tab" value="withdrawals">
                        @if ($selectedRegionId)
                            <input type="hidden" name="region_id" value="{{ $selectedRegionId }}">
                        @endif
                        <input type="hidden" name="status" value="{{ $withdrawalStatus }}">
                        <div class="relative">
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Cari kode, nama, no rekening..."
                                   class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200/80 bg-slate-50/75 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-4">Kode & Waktu</th>
                                <th class="py-3 px-4">Pemohon</th>
                                <th class="py-3 px-4">Wilayah</th>
                                <th class="py-3 px-4">Rekening Tujuan</th>
                                <th class="py-3 px-4 text-right">Nominal Diajukan</th>
                                <th class="py-3 px-4 text-right">Dana Bersih</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($withdrawals as $wd)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-4">
                                        <span class="font-mono font-bold text-slate-900 block">{{ $wd->withdrawal_code }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $wd->created_at->format('d M Y, H:i') }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-slate-900 block">{{ $wd->user?->name ?? '-' }}</span>
                                        <span class="text-[10px] text-slate-500">{{ $wd->user?->role?->label() ?? '-' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700">
                                        {{ $wd->region?->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-800 block">{{ $wd->bank_name }}</span>
                                        <span class="font-mono text-slate-600 block">{{ $wd->account_number }}</span>
                                        <span class="text-[10px] text-slate-500">a.n. {{ $wd->account_holder_name }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-semibold text-slate-900">
                                        Rp {{ number_format($wd->amount, 0, ',', '.') }}
                                        @if ($wd->fee > 0)
                                            <span class="text-[10px] text-slate-400 block">Biaya: Rp {{ number_format($wd->fee, 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-emerald-700">
                                        Rp {{ number_format($wd->net_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($wd->isPending())
                                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 ring-2 ring-amber-100"></span>
                                                Menunggu Verifikasi
                                            </span>
                                        @elseif ($wd->isApproved())
                                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-emerald-100"></span>
                                                Disetujui
                                            </span>
                                            @if ($wd->transfer_reference)
                                                <span class="text-[10px] text-slate-400 block font-mono">Ref: {{ $wd->transfer_reference }}</span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-rose-700">
                                                <span class="w-2 h-2 rounded-full bg-rose-500 ring-2 ring-rose-100"></span>
                                                Ditolak
                                            </span>
                                            @if ($wd->rejection_reason)
                                                <span class="text-[10px] text-rose-500 block truncate max-w-xs" title="{{ $wd->rejection_reason }}">
                                                    {{ $wd->rejection_reason }}
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if ($wd->isPending())
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button"
                                                        @click="openApprove({{ json_encode($wd) }})"
                                                        class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] rounded-lg transition shadow-2xs">
                                                    Setujui
                                                </button>
                                                <button type="button"
                                                        @click="openReject({{ json_encode($wd) }})"
                                                        class="px-2.5 py-1 bg-white border border-rose-300 hover:bg-rose-50 text-rose-700 font-bold text-[11px] rounded-lg transition shadow-2xs">
                                                    Tolak
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-[11px] text-slate-400">
                                                Oleh: {{ $wd->processedBy?->name ?? 'Admin' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                        Tidak ada permohonan penarikan dana pada status ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $withdrawals->links() }}
                </div>
            </div>
        @else
            <!-- Tab 2: Regional Ledger Transactions -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
                <!-- Ledger Filters -->
                <form method="GET" action="{{ route('admin.finance.index') }}" class="p-4 border-b border-slate-100 flex flex-wrap items-center gap-3 text-xs">
                    <input type="hidden" name="tab" value="ledger">
                    @if ($selectedRegionId)
                        <input type="hidden" name="region_id" value="{{ $selectedRegionId }}">
                    @endif

                    <div>
                        <label class="font-medium text-slate-600 block mb-1">Jenis Transaksi</label>
                        <select name="ref_type" class="p-1.5 rounded-lg border border-slate-200 text-xs text-slate-800">
                            <option value="">Semua Referensi</option>
                            <option value="topup" {{ request('ref_type') === 'topup' ? 'selected' : '' }}>Top-up Dompet</option>
                            <option value="order_payment" {{ request('ref_type') === 'order_payment' ? 'selected' : '' }}>Pembayaran Pesanan</option>
                            <option value="order_earning" {{ request('ref_type') === 'order_earning' ? 'selected' : '' }}>Pendapatan Mitra</option>
                            <option value="order_refund" {{ request('ref_type') === 'order_refund' ? 'selected' : '' }}>Pengembalian Refund</option>
                            <option value="withdrawal_request" {{ request('ref_type') === 'withdrawal_request' ? 'selected' : '' }}>Penarikan Saldo Mitra</option>
                            <option value="customer_withdrawal" {{ request('ref_type') === 'customer_withdrawal' ? 'selected' : '' }}>Penarikan Saldo Pelanggan</option>
                            <option value="withdrawal_refund" {{ request('ref_type') === 'withdrawal_refund' ? 'selected' : '' }}>Refund Penarikan Ditolak</option>
                        </select>
                    </div>

                    <div>
                        <label class="font-medium text-slate-600 block mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="p-1.5 rounded-lg border border-slate-200 text-xs text-slate-800">
                    </div>

                    <div>
                        <label class="font-medium text-slate-600 block mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="p-1.5 rounded-lg border border-slate-200 text-xs text-slate-800">
                    </div>

                    <div class="self-end pt-1">
                        <button type="submit" class="px-3.5 py-1.5 bg-slate-900 text-white rounded-lg font-semibold hover:bg-slate-800 transition">
                            Terapkan Filter
                        </button>
                    </div>
                </form>

                <!-- Ledger Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200/80 bg-slate-50/75 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Wilayah</th>
                                <th class="py-3 px-4">Pengguna</th>
                                <th class="py-3 px-4">Referensi</th>
                                <th class="py-3 px-4 text-right">Nominal</th>
                                <th class="py-3 px-4 text-right">Saldo Sesudah</th>
                                <th class="py-3 px-4">Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($ledgerTransactions as $tx)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                        {{ $tx->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-700">
                                        {{ $tx->region?->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-slate-900 block">{{ $tx->user?->name ?? 'Sistem' }}</span>
                                        <span class="text-[10px] text-slate-500">{{ $tx->user?->role?->label() ?? '-' }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold font-mono bg-slate-100 text-slate-700">
                                            {{ $tx->reference_type }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap font-bold {{ $tx->isCredit() ? 'text-emerald-700' : 'text-slate-800' }}">
                                        {{ $tx->isCredit() ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-600 whitespace-nowrap">
                                        Rp {{ number_format($tx->balance_after, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 max-w-xs truncate" title="{{ $tx->description }}">
                                        {{ $tx->description }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                        Belum ada mutasi transaksi pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $ledgerTransactions->links() }}
                </div>
            </div>
        @endif

        <!-- Modal Setujui Penarikan -->
        <div x-show="approveModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-200"
                 @click.away="approveModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Konfirmasi Persetujuan Penarikan</h3>
                    <button type="button" @click="approveModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <template x-if="currentWithdrawal">
                    <form :action="'/admin/finance/withdrawals/' + currentWithdrawal.id + '/approve'" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                        @csrf
                        <div class="p-3 bg-slate-50 rounded-xl space-y-1.5 border border-slate-100">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Kode Penarikan:</span>
                                <span class="font-mono font-bold text-slate-900" x-text="currentWithdrawal.withdrawal_code"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Pemohon:</span>
                                <span class="font-semibold text-slate-900" x-text="currentWithdrawal.user ? currentWithdrawal.user.name : '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Rekening Tujuan:</span>
                                <span class="font-bold text-slate-900" x-text="currentWithdrawal.bank_name + ' • ' + currentWithdrawal.account_number"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Atas Nama:</span>
                                <span class="text-slate-900" x-text="currentWithdrawal.account_holder_name"></span>
                            </div>
                            <div class="flex justify-between pt-1 border-t border-slate-200 font-bold text-sm">
                                <span>Total Ditransfer:</span>
                                <span class="text-emerald-700" x-text="'Rp ' + Number(currentWithdrawal.net_amount).toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <div>
                            <label for="transfer_reference" class="font-medium text-slate-700 block mb-1">Nomor Referensi Transfer / Payout (Opsional)</label>
                            <input type="text"
                                   id="transfer_reference"
                                   name="transfer_reference"
                                   placeholder="Contoh: REF-BCA-12345678"
                                   class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div>
                            <label for="proof_file" class="font-medium text-slate-700 block mb-1">Unggah Bukti Transfer (Opsional)</label>
                            <input type="file"
                                   id="proof_file"
                                   name="proof_file"
                                   accept="image/*,application/pdf"
                                   class="w-full p-2 rounded-xl border border-slate-200 text-xs text-slate-600">
                        </div>

                        <div class="pt-2 flex justify-end gap-2">
                            <button type="button" @click="approveModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-xs">
                                Setujui & Konfirmasi Selesai
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <!-- Modal Tolak Penarikan -->
        <div x-show="rejectModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-200"
                 @click.away="rejectModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-rose-700">Tolak Permohonan Penarikan</h3>
                    <button type="button" @click="rejectModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <template x-if="currentWithdrawal">
                    <form :action="'/admin/finance/withdrawals/' + currentWithdrawal.id + '/reject'" method="POST" class="space-y-4 text-xs">
                        @csrf
                        <div class="p-3 bg-rose-50/60 rounded-xl space-y-1.5 border border-rose-100">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Kode Penarikan:</span>
                                <span class="font-mono font-bold text-slate-900" x-text="currentWithdrawal.withdrawal_code"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Nominal Dikembalikan:</span>
                                <span class="font-bold text-slate-900" x-text="'Rp ' + Number(currentWithdrawal.amount).toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <div>
                            <label for="rejection_reason" class="font-medium text-slate-700 block mb-1">
                                Alasan Penolakan <span class="text-rose-600">*</span>
                            </label>
                            <textarea id="rejection_reason"
                                      name="rejection_reason"
                                      rows="3"
                                      placeholder="Jelaskan alasan penolakan secara jelas (misal: Nomor rekening tidak valid, nama pemilik tidak sesuai)"
                                      class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                                      required></textarea>
                            <span class="text-[10px] text-slate-500 mt-1 block">
                                Saldo penarikan sebesar nominal pengajuan akan otomatis dikembalikan ke dompet pengguna.
                            </span>
                        </div>

                        <div class="pt-2 flex justify-end gap-2">
                            <button type="button" @click="rejectModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold transition shadow-xs">
                                Tolak & Kembalikan Saldo
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>

