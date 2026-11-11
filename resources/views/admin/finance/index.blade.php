<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800 leading-tight">
            {{ __('Keuangan Wilayah & Penarikan Dana') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="{
        detailModal: false,
        approveModal: false,
        rejectModal: false,
        currentWd: null,
        openDetail(wd) {
            this.currentWd = wd;
            this.approveModal = false;
            this.rejectModal = false;
            this.detailModal = true;
        },
        closeDetail() {
            this.detailModal = false;
            this.currentWd = null;
        },
        openApprove() {
            this.detailModal = false;
            this.approveModal = true;
        },
        closeApprove() {
            this.approveModal = false;
            this.detailModal = true;
        },
        openReject() {
            this.detailModal = false;
            this.rejectModal = true;
        },
        closeReject() {
            this.rejectModal = false;
            this.detailModal = true;
        }
    }">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold shadow-xs">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold shadow-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Kartu Ringkasan Metrik (Konsisten dengan Appeals & Complaints) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
            <!-- Total Kas Masuk -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Total Arus Masuk</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">Rp {{ number_format($totalInflow, 0, ',', '.') }}</div>
            </div>

            <!-- Total Kas Keluar -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Total Arus Keluar</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</div>
            </div>

            <!-- Pencairan Disetujui -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Pencairan Disetujui</span>
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">Rp {{ number_format($totalApprovedDisbursement, 0, ',', '.') }}</div>
            </div>

            <!-- Antrean Penarikan -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Antrean Penarikan</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">
                    {{ number_format($pendingWithdrawalCount) }} <span class="text-sm font-semibold text-slate-500">Permohonan</span>
                </div>
            </div>
        </div>

        <!-- Master Table Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <!-- Header Controls: Tabs di Kiri, Tombol Ekspor di Kanan -->
            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Tab Switching: Antrean Penarikan vs Buku Kas -->
                <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-xl">
                    <a href="{{ route('admin.finance.index', array_merge(request()->query(), ['tab' => 'withdrawals'])) }}"
                       class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 {{ $tab === 'withdrawals' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-500 hover:text-slate-800' }}">
                        <span>Antrean Penarikan Dana</span>
                        @if ($pendingWithdrawalCount > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white">
                                {{ $pendingWithdrawalCount }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('admin.finance.index', array_merge(request()->query(), ['tab' => 'ledger'])) }}"
                       class="px-4 py-2 rounded-lg text-xs font-bold transition {{ $tab === 'ledger' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-500 hover:text-slate-800' }}">
                        Buku Kas Wilayah
                    </a>
                </div>

                <!-- Export Action Buttons -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.finance.export.csv', request()->query()) }}"
                       class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Ekspor CSV
                    </a>
                    <a href="{{ route('admin.finance.export.print', request()->query()) }}"
                       target="_blank"
                       class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak / PDF
                    </a>
                </div>
            </div>

            @if ($tab === 'withdrawals')
                <!-- Secondary Toolbar: Filters (Cari -> Wilayah -> Status) Sesuai Standar Appeals & Complaints -->
                <div class="px-5 py-3.5 bg-slate-50/50 border-b border-slate-100 flex flex-wrap items-center gap-3">
                    <form method="GET" action="{{ route('admin.finance.index') }}" class="flex flex-wrap items-center gap-3 w-full">
                        <input type="hidden" name="tab" value="withdrawals">

                        <!-- 1. Search Input -->
                        <div class="relative w-56 sm:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Cari kode, nama..."
                                   class="w-full text-xs border-slate-200 rounded-xl pl-9 pr-3.5 py-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs bg-white placeholder:text-xs placeholder-slate-400">
                        </div>

                        <!-- 2. Multi-Wilayah Filter (Jika > 1 Wilayah) -->
                        @if ($assignedRegions->count() > 1)
                            <div class="flex items-center space-x-1.5 shrink-0">
                                <label for="region_select" class="text-xs font-semibold text-slate-600 shrink-0">Wilayah:</label>
                                <select id="region_select"
                                        name="region_id"
                                        onchange="this.form.submit()"
                                        class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                                    <option value="">Semua Wilayah ({{ $assignedRegions->count() }})</option>
                                    @foreach ($assignedRegions as $reg)
                                        <option value="{{ $reg->id }}" {{ $selectedRegionId === $reg->id ? 'selected' : '' }}>
                                            {{ $reg->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- 3. Status Filter Dropdown -->
                        <div class="flex items-center space-x-1.5 shrink-0">
                            <label for="status_select" class="text-xs font-semibold text-slate-600 shrink-0">Status:</label>
                            <select id="status_select"
                                    name="status"
                                    onchange="this.form.submit()"
                                    class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                                <option value="all" {{ $withdrawalStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
                                <option value="pending" {{ $withdrawalStatus === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi ({{ $pendingWithdrawalCount }})</option>
                                <option value="approved" {{ $withdrawalStatus === 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="rejected" {{ $withdrawalStatus === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>

                        <!-- 4. Reset Link -->
                        @if (request('search') || request('region_id') || (request('status') && request('status') !== 'all'))
                            <a href="{{ route('admin.finance.index', ['tab' => 'withdrawals']) }}"
                               title="Reset Filter"
                               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Table Penarikan: Format Persis Seperti admin/appeals (Tanpa Waktu, Tanpa Pill Tebal, Link Tinjau) -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3 whitespace-nowrap">Kode Penarikan</th>
                                <th class="px-5 py-3 whitespace-nowrap">Pemohon</th>
                                <th class="px-5 py-3 whitespace-nowrap">Wilayah</th>
                                <th class="px-5 py-3 whitespace-nowrap">Nominal Bersih</th>
                                <th class="px-5 py-3 whitespace-nowrap">Status</th>
                                <th class="px-5 py-3 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse ($withdrawals as $wd)
                                @php
                                    $payload = [
                                        'id' => $wd->id,
                                        'code' => $wd->withdrawal_code,
                                        'user_name' => $wd->user?->name ?? '-',
                                        'user_phone' => $wd->user?->phone ?? '-',
                                        'user_role' => $wd->user?->isMitra() ? 'Mitra Terapis' : 'Pelanggan',
                                        'is_mitra' => (bool) $wd->user?->isMitra(),
                                        'region_name' => $wd->region?->name ?? '-',
                                        'bank_name' => $wd->bank_name,
                                        'account_number' => $wd->account_number,
                                        'account_holder_name' => $wd->account_holder_name,
                                        'amount' => (float) $wd->amount,
                                        'fee' => (float) $wd->fee,
                                        'net_amount' => (float) $wd->net_amount,
                                        'notes' => $wd->notes,
                                        'status' => strtoupper($wd->status),
                                        'is_pending' => $wd->isPending(),
                                        'is_approved' => $wd->isApproved(),
                                        'is_rejected' => $wd->isRejected(),
                                        'created_at' => $wd->created_at->format('d M Y, H:i'),
                                        'processed_by' => $wd->processedBy?->name,
                                        'processed_at' => $wd->processed_at?->format('d M Y, H:i'),
                                        'transfer_reference' => $wd->transfer_reference,
                                        'proof_url' => $wd->proof_path ? asset('storage/' . $wd->proof_path) : null,
                                        'rejection_reason' => $wd->rejection_reason,
                                    ];
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition cursor-pointer"
                                    @click="openDetail({{ json_encode($payload) }})">
                                    <!-- 1. Kode Penarikan (Teks Bersih Tanpa Waktu) -->
                                    <td class="px-5 py-3.5 whitespace-nowrap font-bold text-slate-800">
                                        {{ $wd->withdrawal_code }}
                                    </td>

                                    <!-- 2. Pemohon (Nama Bersih Tanpa Pill Badge) -->
                                    <td class="px-5 py-3.5 whitespace-nowrap font-semibold text-slate-800">
                                        {{ $wd->user?->name ?? '-' }}
                                    </td>

                                    <!-- 3. Wilayah -->
                                    <td class="px-5 py-3.5 whitespace-nowrap text-slate-600">
                                        {{ $wd->region?->name ?? '-' }}
                                    </td>

                                    <!-- 4. Nominal Bersih -->
                                    <td class="px-5 py-3.5 whitespace-nowrap font-semibold text-emerald-700">
                                        Rp {{ number_format($wd->net_amount, 0, ',', '.') }}
                                    </td>

                                    <!-- 5. Status (Calm Dot Indicator Tanpa Pill Tebal) -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full {{ match($wd->status) {
                                                'pending' => 'bg-amber-500',
                                                'approved' => 'bg-emerald-500',
                                                default => 'bg-rose-500',
                                            } }}"></span>
                                            {{ $wd->isPending() ? 'Menunggu Verifikasi' : ($wd->isApproved() ? 'Disetujui' : 'Ditolak') }}
                                        </span>
                                    </td>

                                    <!-- 6. Aksi (Link Tinjau →) -->
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap" @click.stop>
                                        <button type="button"
                                                @click="openDetail({{ json_encode($payload) }})"
                                                class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1">
                                            <span>Tinjau</span>
                                            <span>&rarr;</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                        Tidak ada permohonan penarikan dana pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $withdrawals->links() }}
                </div>
            @else
                <!-- Secondary Toolbar for Ledger: Terpadu 1 Baris Sejajar (Wilayah & Jenis Auto-Submit, Date Range Kompak, Tombol Hijau) -->
                <div class="px-5 py-3.5 bg-slate-50/50 border-b border-slate-100">
                    <form method="GET" action="{{ route('admin.finance.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                        <input type="hidden" name="tab" value="ledger">

                        <!-- Sisi Kiri: Filter Wilayah & Jenis (Auto-submit on change) -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <!-- 1. Multi-Wilayah Filter -->
                            @if ($assignedRegions->count() > 1)
                                <select id="ledger_region_select"
                                        name="region_id"
                                        onchange="this.form.submit()"
                                        class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-2xs py-2 pl-3.5 pr-8 cursor-pointer text-slate-700">
                                    <option value="">Semua Wilayah ({{ $assignedRegions->count() }})</option>
                                    @foreach ($assignedRegions as $reg)
                                        <option value="{{ $reg->id }}" {{ $selectedRegionId === $reg->id ? 'selected' : '' }}>
                                            {{ $reg->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            <!-- 2. Jenis Transaksi Filter -->
                            <select id="ref_type_select"
                                    name="ref_type"
                                    onchange="this.form.submit()"
                                    class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-2xs py-2 pl-3.5 pr-8 cursor-pointer text-slate-700">
                                <option value="">Semua Jenis Transaksi</option>
                                <option value="topup" {{ request('ref_type') === 'topup' ? 'selected' : '' }}>Top-up Saldo</option>
                                <option value="order_payment" {{ request('ref_type') === 'order_payment' ? 'selected' : '' }}>Pembayaran Pesanan</option>
                                <option value="order_earning" {{ request('ref_type') === 'order_earning' ? 'selected' : '' }}>Pendapatan Layanan</option>
                                <option value="order_refund" {{ request('ref_type') === 'order_refund' ? 'selected' : '' }}>Refund Pesanan</option>
                                <option value="withdrawal_request" {{ request('ref_type') === 'withdrawal_request' ? 'selected' : '' }}>Pencairan Mitra</option>
                                <option value="customer_withdrawal" {{ request('ref_type') === 'customer_withdrawal' ? 'selected' : '' }}>Pencairan Pelanggan</option>
                                <option value="withdrawal_refund" {{ request('ref_type') === 'withdrawal_refund' ? 'selected' : '' }}>Refund Pencairan</option>
                            </select>
                        </div>

                        <!-- Sisi Kanan: Rentang Tanggal Terpadu + Tombol Hijau + Reset -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Grup Input Tanggal Terpadu -->
                            <div class="inline-flex items-center bg-white border border-slate-200 rounded-xl shadow-2xs px-2 py-1">
                                <input type="date"
                                       id="start_date"
                                       name="start_date"
                                       value="{{ request('start_date') }}"
                                       class="text-xs border-0 p-1 text-slate-700 focus:ring-0 cursor-pointer bg-transparent"
                                       placeholder="Dari">
                                <span class="text-slate-300 font-bold px-1 select-none">&rarr;</span>
                                <input type="date"
                                       id="end_date"
                                       name="end_date"
                                       value="{{ request('end_date') }}"
                                       class="text-xs border-0 p-1 text-slate-700 focus:ring-0 cursor-pointer bg-transparent"
                                       placeholder="Sampai">
                            </div>

                            <!-- Tombol Filter Hijau Emerald -->
                            <button type="submit"
                                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold transition shadow-xs text-xs flex items-center gap-1.5 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                <span>Filter</span>
                            </button>

                            <!-- Tombol Reset Filter -->
                            @if (request('ref_type') || request('start_date') || request('end_date') || request('region_id'))
                                <a href="{{ route('admin.finance.index', ['tab' => 'ledger']) }}"
                                   title="Reset Filter"
                                   class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Ledger Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 border-collapse">
                        <thead class="bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3 whitespace-nowrap">Waktu</th>
                                <th class="px-5 py-3 whitespace-nowrap">Wilayah</th>
                                <th class="px-5 py-3 whitespace-nowrap">Pengguna</th>
                                <th class="px-5 py-3 whitespace-nowrap">Jenis</th>
                                <th class="px-5 py-3 text-right whitespace-nowrap">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($ledgerTransactions as $tx)
                                @php
                                    $jenisLabel = match ($tx->reference_type) {
                                        'topup' => 'Top-up Saldo',
                                        'order_payment' => 'Pembayaran Pesanan',
                                        'order_earning' => 'Pendapatan Layanan',
                                        'order_refund' => 'Refund Pesanan',
                                        'withdrawal_request' => 'Pencairan Mitra',
                                        'customer_withdrawal' => 'Pencairan Pelanggan',
                                        'withdrawal_refund' => 'Refund Pencairan',
                                        default => ucwords(str_replace('_', ' ', $tx->reference_type)),
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-3.5 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                        {{ $tx->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-slate-600">
                                        {{ $tx->region?->name ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap font-semibold text-slate-800">
                                        {{ $tx->user?->name ?? 'Sistem' }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap" title="{{ $tx->description }}">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $tx->isCredit() ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            {{ $jenisLabel }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap font-semibold {{ $tx->isCredit() ? 'text-emerald-700' : 'text-slate-800' }}">
                                        {{ $tx->isCredit() ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
                                        Belum ada mutasi transaksi pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $ledgerTransactions->links() }}
                </div>
            @endif
        </div>

        <!-- ============================================================= -->
        <!-- MODAL 1: DETAIL PERMOHONAN PENARIKAN DANA                      -->
        <!-- ============================================================= -->
        <div x-show="detailModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-2xl w-full p-6 space-y-5 shadow-2xl border border-slate-200"
                 @click.away="closeDetail()">

                <template x-if="currentWd">
                    <div class="space-y-5">
                        <!-- Modal Header: Judul, Kode & Status -->
                        <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-slate-900">Detail Permohonan Penarikan</h3>
                                    <span class="font-mono text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md"
                                          x-text="currentWd.code"></span>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">
                                    Diajukan pada <span x-text="currentWd.created_at"></span> • Wilayah <span class="font-semibold text-slate-600" x-text="currentWd.region_name"></span>
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <!-- Status Badge di Modal -->
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                          :class="{
                                              'bg-amber-500 animate-pulse': currentWd.is_pending,
                                              'bg-emerald-500': currentWd.is_approved,
                                              'bg-rose-500': currentWd.is_rejected
                                          }"></span>
                                    <span x-text="currentWd.is_pending ? 'Menunggu Verifikasi' : (currentWd.is_approved ? 'Disetujui' : 'Ditolak')"></span>
                                </span>

                                <button type="button"
                                        @click="closeDetail()"
                                        class="text-slate-400 hover:text-slate-600 text-xl leading-none font-bold">
                                    &times;
                                </button>
                            </div>
                        </div>

                        <!-- 2-Kolom Informasi Inti: Pemohon & Rekening Tujuan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <!-- Box Pemohon -->
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Informasi Pemohon</span>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Nama Lengkap:</span>
                                    <span class="font-bold text-slate-900" x-text="currentWd.user_name"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Peran Akun:</span>
                                    <span class="font-semibold text-slate-700" x-text="currentWd.user_role"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Wilayah Tugas:</span>
                                    <span class="text-slate-700 font-medium" x-text="currentWd.region_name"></span>
                                </div>
                            </div>

                            <!-- Box Rekening Tujuan -->
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Rekening Tujuan Transfer</span>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Bank Tujuan:</span>
                                    <span class="font-bold text-slate-900" x-text="currentWd.bank_name"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Nomor Rekening:</span>
                                    <span class="font-mono font-bold text-slate-900 text-sm" x-text="currentWd.account_number"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Nama Pemilik:</span>
                                    <span class="font-semibold text-slate-800" x-text="currentWd.account_holder_name"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Rincian Finansial Bersih -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">Rincian Finansial Pencairan</span>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Nominal Pengajuan (Kotor):</span>
                                <span class="font-semibold text-slate-800" x-text="'Rp ' + Number(currentWd.amount).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Potongan / Biaya Layanan:</span>
                                <span class="font-semibold text-slate-600" x-text="'Rp ' + Number(currentWd.fee).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-sm">
                                <span class="font-bold text-slate-800">Dana Bersih yang Ditransfer:</span>
                                <span class="font-bold text-emerald-700 text-base" x-text="'Rp ' + Number(currentWd.net_amount).toLocaleString('id-ID')"></span>
                            </div>
                            <template x-if="currentWd.notes">
                                <div class="pt-2 border-t border-slate-200 text-[11px] text-slate-500">
                                    <span class="font-semibold">Catatan Pemohon:</span> <span x-text="currentWd.notes"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Bagian Status Riwayat (Jika Sudah Selesai/Ditolak) -->
                        <template x-if="currentWd.is_approved">
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">Riwayat Persetujuan Admin</span>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Diverifikasi Oleh:</span>
                                    <span class="font-semibold text-slate-800" x-text="currentWd.processed_by || 'Admin'"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Waktu Verifikasi:</span>
                                    <span class="text-slate-700" x-text="currentWd.processed_at || '-'"></span>
                                </div>
                                <template x-if="currentWd.transfer_reference">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">Referensi Transfer:</span>
                                        <span class="font-mono font-bold text-slate-800" x-text="currentWd.transfer_reference"></span>
                                    </div>
                                </template>
                                <template x-if="currentWd.proof_url">
                                    <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                                        <span class="text-slate-500">Bukti Transfer:</span>
                                        <a :href="currentWd.proof_url" target="_blank" class="font-semibold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1">
                                            <span>Lihat Bukti Transfer</span>
                                            <span>&rarr;</span>
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="currentWd.is_rejected">
                            <div class="p-4 rounded-xl bg-rose-50/60 border border-rose-100 space-y-2 text-xs">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700 block">Riwayat Penolakan</span>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Ditolak Oleh:</span>
                                    <span class="font-semibold text-slate-800" x-text="currentWd.processed_by || 'Admin'"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Waktu Penolakan:</span>
                                    <span class="text-slate-700" x-text="currentWd.processed_at || '-'"></span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block mb-1">Alasan Penolakan:</span>
                                    <p class="font-medium text-rose-700 bg-white p-2.5 rounded-lg border border-rose-200" x-text="currentWd.rejection_reason || '-'"></p>
                                </div>
                                <p class="text-[10px] text-slate-500">
                                    * Saldo sebesar nominal pengajuan telah dikembalikan ke dompet pemohon.
                                </p>
                            </div>
                        </template>

                        <!-- Modal Detail Footer -->
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <button type="button"
                                    @click="closeDetail()"
                                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                Tutup
                            </button>

                            <template x-if="currentWd.is_pending">
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="openReject()"
                                            class="px-4 py-2 rounded-xl bg-white border border-rose-200 text-rose-700 hover:bg-rose-50 font-bold text-xs transition shadow-2xs">
                                        Tolak Permohonan
                                    </button>
                                    <button type="button"
                                            @click="openApprove()"
                                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-xs">
                                        Setujui Penarikan
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- MODAL 2A: KHUSUS SETUJUI PENARIKAN (Rekening Fix, Bukti Wajib) -->
        <!-- ============================================================= -->
        <div x-show="approveModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-200"
                 @click.away="closeApprove()">

                <template x-if="currentWd">
                    <div>
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Konfirmasi Persetujuan Penarikan</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Pastikan transfer telah berhasil dilakukan ke rekening pemohon.</p>
                            </div>
                            <button type="button" @click="closeApprove()" class="text-slate-400 hover:text-slate-600 text-xl leading-none font-bold">&times;</button>
                        </div>

                        <!-- Form Persetujuan -->
                        <form :action="'/admin/finance/withdrawals/' + currentWd.id + '/approve'"
                              method="POST"
                              enctype="multipart/form-data"
                              class="space-y-4 text-xs mt-4">
                            @csrf

                            <!-- Ringkasan Rekening Otomatis Terisi & Terkunci (Read-only) -->
                            <div class="p-4 bg-slate-50 rounded-xl space-y-2 border border-slate-200/80">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">
                                    Rekening Tujuan Transfer (Terkunci)
                                </span>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Kode & Pemohon:</span>
                                    <span class="font-semibold text-slate-800" x-text="currentWd.code + ' (' + currentWd.user_name + ')'"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Bank Tujuan:</span>
                                    <span class="font-bold text-slate-900" x-text="currentWd.bank_name"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Nomor Rekening:</span>
                                    <span class="font-mono font-bold text-slate-900 text-sm" x-text="currentWd.account_number"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Nama Pemilik Rekening:</span>
                                    <span class="font-semibold text-slate-800" x-text="currentWd.account_holder_name"></span>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-sm">
                                    <span class="font-bold text-slate-800">Total Nominal Ditransfer:</span>
                                    <span class="font-bold text-emerald-700 text-base" x-text="'Rp ' + Number(currentWd.net_amount).toLocaleString('id-ID')"></span>
                                </div>
                            </div>

                            <!-- Input Referensi Transfer -->
                            <div>
                                <label for="modal_transfer_reference" class="font-semibold text-slate-700 block mb-1">
                                    Nomor Referensi Transfer / Payout (Opsional)
                                </label>
                                <input type="text"
                                       id="modal_transfer_reference"
                                       name="transfer_reference"
                                       placeholder="Contoh: REF-BCA-88992211"
                                       class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <span class="text-[10px] text-slate-400 mt-0.5 block">Nomor bukti mutasi / ref dari aplikasi bank atau payment gateway.</span>
                            </div>

                            <!-- Input Unggah Bukti Transfer (WAJIB) -->
                            <div>
                                <label for="modal_proof_file" class="font-semibold text-slate-700 block mb-1">
                                    Unggah Bukti Transfer <span class="text-rose-600 font-bold">* (Wajib)</span>
                                </label>
                                <input type="file"
                                       id="modal_proof_file"
                                       name="proof_file"
                                       accept="image/jpeg,image/png,image/jpg,application/pdf"
                                       class="w-full p-2 rounded-xl border border-slate-200 text-xs text-slate-600 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
                                       required>
                                <span class="text-[10px] text-slate-500 mt-1 block">
                                    Format: JPG, PNG, atau PDF (maks. 4MB). Berkas bukti transfer wajib diunggah untuk kelengkapan audit kas wilayah.
                                </span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                                <button type="button"
                                        @click="closeApprove()"
                                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-xs flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Konfirmasi & Setujui Selesai
                                </button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- MODAL 2B: KHUSUS TOLAK PENARIKAN (Alasan Wajib, Refund Saldo)  -->
        <!-- ============================================================= -->
        <div x-show="rejectModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-200"
                 @click.away="closeReject()">

                <template x-if="currentWd">
                    <div>
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div>
                                <h3 class="text-base font-bold text-rose-700">Tolak Permohonan Penarikan</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Saldo pengajuan akan otomatis dikembalikan ke dompet pemohon.</p>
                            </div>
                            <button type="button" @click="closeReject()" class="text-slate-400 hover:text-slate-600 text-xl leading-none font-bold">&times;</button>
                        </div>

                        <!-- Form Penolakan -->
                        <form :action="'/admin/finance/withdrawals/' + currentWd.id + '/reject'"
                              method="POST"
                              class="space-y-4 text-xs mt-4">
                            @csrf

                            <!-- Ringkasan Terkunci -->
                            <div class="p-3.5 bg-rose-50/70 rounded-xl space-y-1.5 border border-rose-100">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600">Kode Penarikan:</span>
                                    <span class="font-mono font-bold text-slate-900" x-text="currentWd.code"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600">Pemohon:</span>
                                    <span class="font-semibold text-slate-900" x-text="currentWd.user_name"></span>
                                </div>
                                <div class="flex items-center justify-between pt-1 border-t border-rose-200/60">
                                    <span class="text-slate-700 font-medium">Saldo yang Dikembalikan:</span>
                                    <span class="font-bold text-rose-700 text-sm" x-text="'Rp ' + Number(currentWd.amount).toLocaleString('id-ID')"></span>
                                </div>
                            </div>

                            <!-- Textarea Alasan Penolakan -->
                            <div>
                                <label for="modal_rejection_reason" class="font-semibold text-slate-700 block mb-1">
                                    Alasan Penolakan <span class="text-rose-600 font-bold">* (Wajib)</span>
                                </label>
                                <textarea id="modal_rejection_reason"
                                          name="rejection_reason"
                                          rows="3"
                                          placeholder="Jelaskan alasan penolakan secara jelas (misal: Rekening tujuan tidak aktif, nama penerima berbeda dengan identitas akun)..."
                                          class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                                          required></textarea>
                                <span class="text-[10px] text-slate-500 mt-1 block">
                                    Alasan ini akan dikirimkan kepada pemohon melalui notifikasi sistem aplikasi.
                                </span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                                <button type="button"
                                        @click="closeReject()"
                                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition shadow-xs flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Tolak & Kembalikan Saldo
                                </button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>
