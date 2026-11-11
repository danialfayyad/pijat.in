<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Master Voucher & Kupon Diskon') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{
             openCreateModal: false,
             openEditModal: false,
             editVoucher: {
                 id: null,
                 code: '',
                 name: '',
                 description: '',
                 type: 'percentage',
                 amount: '',
                 max_discount_amount: '',
                 min_order_amount: '',
                 quota: '',
                 per_user_limit: 1,
                 starts_at: '',
                 expires_at: '',
                 is_active: true
             },
             editUrl: '',
             openEdit(v) {
                 this.editVoucher = {
                     id: v.id,
                     code: v.code,
                     name: v.name,
                     description: v.description || '',
                     type: v.type,
                     amount: parseFloat(v.amount),
                     max_discount_amount: v.max_discount_amount ? parseFloat(v.max_discount_amount) : '',
                     min_order_amount: parseFloat(v.min_order_amount || 0),
                     quota: v.quota || '',
                     per_user_limit: v.per_user_limit || 1,
                     starts_at: v.starts_at ? v.starts_at.substring(0, 16) : '',
                     expires_at: v.expires_at ? v.expires_at.substring(0, 16) : '',
                     is_active: Boolean(v.is_active)
                 };
                 this.editUrl = '{{ url('/superadmin/vouchers') }}/' + v.id;
                 this.openEditModal = true;
             }
         }">

        @if (session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-2xl flex items-center gap-2.5 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded-2xl shadow-2xs space-y-1">
                @foreach ($errors->all() as $error)
                    <p>&bull; {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- STAT METRICS (1x4 Grid) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Total Voucher</span>
                    <span class="text-xl font-bold text-slate-900">{{ number_format($totalCount) }}</span>
                </div>
                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Voucher Aktif</span>
                    <span class="text-xl font-bold text-emerald-700">{{ number_format($activeCount) }}</span>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Total Pemakaian</span>
                    <span class="text-xl font-bold text-blue-700">{{ number_format($totalUsages) }}</span>
                </div>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Diskon Tersalurkan</span>
                    <span class="text-xl font-bold text-slate-900">Rp {{ number_format($totalDiscountGiven, 0, ',', '.') }}</span>
                </div>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- TIER 1: Status Filter Tabs (Di Luar Card) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-0.5 scrollbar-none">
            <a href="{{ route('superadmin.vouchers.index', array_merge(request()->except('status', 'page'), ['status' => 'semua'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'semua' || !$status ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Semua
            </a>
            <a href="{{ route('superadmin.vouchers.index', array_merge(request()->except('status', 'page'), ['status' => 'aktif'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'aktif' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Aktif
            </a>
            <a href="{{ route('superadmin.vouchers.index', array_merge(request()->except('status', 'page'), ['status' => 'kedaluwarsa'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'kedaluwarsa' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Kedaluwarsa
            </a>
            <a href="{{ route('superadmin.vouchers.index', array_merge(request()->except('status', 'page'), ['status' => 'nonaktif'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'nonaktif' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Nonaktif
            </a>
        </div>

        <!-- TIER 2: Master Card Tabel -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <!-- Header Controls: Title & Integrated Filters Toolbar -->
            <div class="p-5 border-b border-slate-100 space-y-3.5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <h3 class="font-bold text-slate-800 text-base">
                            Daftar Voucher Diskon
                        </h3>
                        <span class="text-xs text-slate-400 font-medium">
                            {{ number_format($vouchers->total()) }} voucher
                        </span>
                    </div>

                    <button type="button" 
                            @click="openCreateModal = true"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Buat Voucher Baru</span>
                    </button>
                </div>

                <!-- Single Unified Filter Toolbar Form -->
                <form method="GET" action="{{ route('superadmin.vouchers.index') }}" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="status" value="{{ $status }}">

                    <!-- 1. Search Box -->
                    <div class="relative w-56 sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari kode atau nama voucher..." 
                               class="w-full text-xs border border-slate-200 rounded-xl pl-9 pr-3.5 py-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs placeholder:text-xs placeholder-slate-400 bg-white">
                    </div>

                    <!-- 2. Filter Tipe Diskon Dropdown -->
                    <div class="shrink-0">
                        <select id="type_filter" name="type" onchange="this.form.submit()"
                                class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                            <option value="">Semua Tipe Diskon</option>
                            <option value="percentage" {{ request('type') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                            <option value="fixed" {{ request('type') === 'fixed' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                        </select>
                    </div>

                    <!-- 3. Reset Button [✕] -->
                    @if (request('search') || request('type') || ($status !== 'semua' && request('status')))
                        <a href="{{ route('superadmin.vouchers.index') }}" 
                           title="Reset Filter"
                           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Table Data -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5">Kode & Nama</th>
                            <th class="px-5 py-3.5">Potongan</th>
                            <th class="px-5 py-3.5">Min. Belanja</th>
                            <th class="px-5 py-3.5">Kuota</th>
                            <th class="px-5 py-3.5">Masa Berlaku</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($vouchers as $voucher)
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- Kode & Nama -->
                                <td class="px-5 py-3.5">
                                    <div class="font-mono font-bold text-slate-900 text-xs tracking-wider">
                                        {{ $voucher->code }}
                                    </div>
                                    <div class="text-slate-500 text-xs mt-0.5" title="{{ $voucher->description }}">
                                        {{ $voucher->name }}
                                    </div>
                                </td>

                                <!-- Nilai Potongan -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-800 text-xs">
                                        @if ($voucher->type === 'percentage')
                                            {{ rtrim(rtrim(number_format((float) $voucher->amount, 2, ',', '.'), '0'), ',') }}%
                                        @else
                                            Rp {{ number_format((float) $voucher->amount, 0, ',', '.') }}
                                        @endif
                                    </div>
                                    @if ($voucher->type === 'percentage' && $voucher->max_discount_amount && (float) $voucher->max_discount_amount > 0)
                                        <div class="text-[11px] text-slate-400 font-normal mt-0.5">Maks. Rp {{ number_format((float) $voucher->max_discount_amount, 0, ',', '.') }}</div>
                                    @endif
                                </td>

                                <!-- Min Order -->
                                <td class="px-5 py-3.5 text-slate-700 whitespace-nowrap">
                                    @if ((float) $voucher->min_order_amount > 0)
                                        <span class="text-xs text-slate-800 font-medium">Rp {{ number_format((float) $voucher->min_order_amount, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>

                                <!-- Kuota & Penggunaan -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-medium text-slate-800 text-xs">
                                        {{ number_format($voucher->used_count) }} <span class="text-slate-400 font-normal">/</span> {{ $voucher->quota ? number_format($voucher->quota) : 'Tak terbatas' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $voucher->per_user_limit }}x per user</div>
                                </td>

                                <!-- Masa Berlaku (Kronologis Dimulai - Berakhir) -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($voucher->starts_at && $voucher->expires_at)
                                        <div class="text-xs text-slate-700 font-medium">
                                            {{ $voucher->starts_at->translatedFormat('d M Y') }} &ndash; {{ $voucher->expires_at->translatedFormat('d M Y') }}
                                        </div>
                                    @elseif ($voucher->expires_at)
                                        <div class="text-xs text-slate-700 font-medium">
                                            s/d {{ $voucher->expires_at->translatedFormat('d M Y') }}
                                        </div>
                                    @elseif ($voucher->starts_at)
                                        <div class="text-xs text-slate-700 font-medium">
                                            Mulai {{ $voucher->starts_at->translatedFormat('d M Y') }}
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">Selamanya</span>
                                    @endif
                                </td>

                                <!-- Status (Clean Dot & Label dengan Kemudahan Toggle) -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($voucher->isExpired())
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>Kedaluwarsa</span>
                                        </span>
                                    @elseif ($voucher->is_active)
                                        <form method="POST" action="{{ route('superadmin.vouchers.toggle', $voucher) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    title="Klik untuk menonaktifkan voucher"
                                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 hover:text-emerald-800 transition cursor-pointer">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Aktif</span>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('superadmin.vouchers.toggle', $voucher) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    title="Klik untuk mengaktifkan voucher"
                                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition cursor-pointer">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                                <span>Nonaktif</span>
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center space-x-2">
                                        <button type="button" 
                                                @click="openEdit({{ json_encode($voucher) }})"
                                                class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition cursor-pointer">
                                            Edit
                                        </button>
                                        <span class="text-slate-200">&bull;</span>
                                        <form id="delete-voucher-{{ $voucher->id }}" method="POST" action="{{ route('superadmin.vouchers.destroy', $voucher) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="window.confirmAction({
                                                        title: 'Hapus Voucher Diskon',
                                                        targetName: '{{ $voucher->code }}',
                                                        message: '{{ $voucher->used_count > 0 ? 'Voucher ini memiliki riwayat ' . $voucher->used_count . ' transaksi. Sistem akan menonaktifkannya demi menjaga integritas data riwayat keuangan.' : 'Apakah Anda yakin ingin menghapus voucher ini secara permanen?' }}',
                                                        type: 'danger',
                                                        confirmText: 'Ya, Lanjutkan',
                                                        cancelText: 'Batal',
                                                        form: document.getElementById('delete-voucher-{{ $voucher->id }}')
                                                    })"
                                                    class="text-xs font-semibold text-rose-500 hover:text-rose-700 transition cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-slate-400 text-xs">
                                    Tidak ada voucher diskon yang sesuai dengan filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($vouchers->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $vouchers->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL 1: BUAT VOUCHER BARU -->
        <div x-show="openCreateModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto" 
             role="dialog" 
             aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="openCreateModal = false"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-3xl bg-white p-6 text-left shadow-2xl transition-all sm:w-full sm:max-w-lg border border-slate-200/90"
                     @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <h3 class="text-base font-bold text-slate-900">Buat Voucher Diskon Baru</h3>
                        <button type="button" @click="openCreateModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('superadmin.vouchers.store') }}" class="space-y-4" x-data="{ createType: 'percentage' }">
                        @csrf
                        
                        <!-- Kode & Nama -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Voucher <span class="text-rose-500">*</span></label>
                                <input type="text" name="code" required placeholder="MISAL: HEMAT20"
                                       class="w-full text-xs font-mono uppercase font-bold border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-[10px] text-slate-400 mt-1">Huruf & angka tanpa spasi.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Voucher <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" required placeholder="Diskon Akhir Pekan"
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <!-- Tipe & Nilai -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Potongan <span class="text-rose-500">*</span></label>
                                <select name="type" x-model="createType" required
                                        class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                    <option value="percentage">Persentase (%)</option>
                                    <option value="fixed">Nominal Tetap (Rp)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    <span x-text="createType === 'percentage' ? 'Persentase Potongan (%)' : 'Besaran Diskon (Rp)'"></span>
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="any" min="0.01" name="amount" required placeholder="Contoh: 20 atau 25000"
                                       class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <!-- Plafon Maks Diskon (khusus persentase) & Min Order -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div x-show="createType === 'percentage'">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Maks. Potongan (Rp)</label>
                                <input type="number" step="any" min="0" name="max_discount_amount" placeholder="0 = tanpa batas"
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-[10px] text-slate-400 mt-0.5">Plafon batas atas diskon.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Min. Belanja (Rp)</label>
                                <input type="number" step="any" min="0" name="min_order_amount" value="0"
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-[10px] text-slate-400 mt-0.5">0 jika tanpa syarat belanja.</p>
                            </div>
                        </div>

                        <!-- Kuota Total & Batas Per User -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Total Kuota Penggunaan</label>
                                <input type="number" min="1" name="quota" placeholder="Kosongkan jika tanpa batas"
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Per Akun <span class="text-rose-500">*</span></label>
                                <input type="number" min="1" name="per_user_limit" value="1" required
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <!-- Masa Berlaku -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Mulai Berlaku</label>
                                <input type="datetime-local" name="starts_at" min="{{ now()->format('Y-m-d\TH:i') }}"
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Kedaluwarsa Pada</label>
                                <input type="datetime-local" name="expires_at" min="{{ now()->format('Y-m-d\TH:i') }}"
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi & Ketentuan</label>
                            <textarea name="description" rows="2" placeholder="Catatan syarat & ketentuan voucher untuk pelanggan..."
                                      class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" id="create_is_active" name="is_active" value="1" checked
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <label for="create_is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">
                                Aktifkan voucher langsung setelah disimpan
                            </label>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-2.5 pt-3 border-t border-slate-100">
                            <button type="button" @click="openCreateModal = false"
                                    class="w-full py-2.5 px-3 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    class="w-full py-2.5 px-3 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition cursor-pointer">
                                Simpan Voucher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL 2: EDIT VOUCHER -->
        <div x-show="openEditModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto" 
             role="dialog" 
             aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="openEditModal = false"></div>
            
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-3xl bg-white p-6 text-left shadow-2xl transition-all sm:w-full sm:max-w-lg border border-slate-200/90"
                     @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <h3 class="text-base font-bold text-slate-900">Edit Voucher Diskon</h3>
                        <button type="button" @click="openEditModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" :action="editUrl" class="space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <!-- Kode & Nama -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Voucher <span class="text-rose-500">*</span></label>
                                <input type="text" name="code" x-model="editVoucher.code" required
                                       class="w-full text-xs font-mono uppercase font-bold border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Voucher <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" x-model="editVoucher.name" required
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <!-- Tipe & Nilai -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Potongan <span class="text-rose-500">*</span></label>
                                <select name="type" x-model="editVoucher.type" required
                                        class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                    <option value="percentage">Persentase (%)</option>
                                    <option value="fixed">Nominal Tetap (Rp)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">
                                    <span x-text="editVoucher.type === 'percentage' ? 'Persentase Potongan (%)' : 'Besaran Diskon (Rp)'"></span>
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="any" min="0.01" name="amount" x-model="editVoucher.amount" required
                                       class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <!-- Plafon Maks Diskon & Min Order -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div x-show="editVoucher.type === 'percentage'">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Maks. Potongan (Rp)</label>
                                <input type="number" step="any" min="0" name="max_discount_amount" x-model="editVoucher.max_discount_amount"
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Min. Belanja (Rp)</label>
                                <input type="number" step="any" min="0" name="min_order_amount" x-model="editVoucher.min_order_amount"
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <!-- Kuota Total & Batas Per User -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Total Kuota Penggunaan</label>
                                <input type="number" min="1" name="quota" x-model="editVoucher.quota"
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Per Akun <span class="text-rose-500">*</span></label>
                                <input type="number" min="1" name="per_user_limit" x-model="editVoucher.per_user_limit" required
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <!-- Masa Berlaku -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Mulai Berlaku</label>
                                <input type="datetime-local" name="starts_at" x-model="editVoucher.starts_at"
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Kedaluwarsa Pada</label>
                                <input type="datetime-local" name="expires_at" x-model="editVoucher.expires_at" min="{{ now()->format('Y-m-d\TH:i') }}"
                                       class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi & Ketentuan</label>
                            <textarea name="description" rows="2" x-model="editVoucher.description"
                                      class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" id="edit_is_active" name="is_active" value="1" x-model="editVoucher.is_active"
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <label for="edit_is_active" class="text-xs font-semibold text-slate-700 cursor-pointer">
                                Status Voucher Aktif
                            </label>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-2.5 pt-3 border-t border-slate-100">
                            <button type="button" @click="openEditModal = false"
                                    class="w-full py-2.5 px-3 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    class="w-full py-2.5 px-3 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition cursor-pointer">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

