<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800 leading-tight">
            {{ __('Pemantauan Pesanan Wilayah') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

        <!-- Filter Status Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['tab' => 'semua', 'page' => 1])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center space-x-2 {{ $tab === 'semua' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                <span>Semua</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $tab === 'semua' ? 'bg-emerald-700/80 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $totalCount }}
                </span>
            </a>

            <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['tab' => 'aktif', 'page' => 1])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center space-x-2 {{ $tab === 'aktif' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                <span>Pesanan Aktif</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $tab === 'aktif' ? 'bg-blue-700/80 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $activeCount }}
                </span>
            </a>

            <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['tab' => 'macet', 'page' => 1])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center space-x-2 {{ $tab === 'macet' ? 'bg-amber-500 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                <span class="relative flex h-2 w-2">
                    @if ($stuckCount > 0)
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    @endif
                    <span class="relative inline-flex rounded-full h-2 w-2 {{ $tab === 'macet' ? 'bg-white' : 'bg-amber-500' }}"></span>
                </span>
                <span>Pesanan Macet</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $tab === 'macet' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 font-bold border border-amber-200' }}">
                    {{ $stuckCount }}
                </span>
            </a>

            <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['tab' => 'selesai', 'page' => 1])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center space-x-2 {{ $tab === 'selesai' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                <span>Selesai</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $tab === 'selesai' ? 'bg-emerald-700/80 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $completedCount }}
                </span>
            </a>

            <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['tab' => 'batal', 'page' => 1])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center space-x-2 {{ $tab === 'batal' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                <span>Dibatalkan</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $tab === 'batal' ? 'bg-rose-700/80 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $cancelledCount }}
                </span>
            </a>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <!-- Table Header Controls: Region Filter & Search -->
            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">
                        @if ($tab === 'macet')
                            Daftar Pesanan Membutuhkan Intervensi (Macet)
                        @elseif ($tab === 'aktif')
                            Daftar Pesanan Sedang Berjalan
                        @elseif ($tab === 'selesai')
                            Daftar Riwayat Pesanan Selesai
                        @elseif ($tab === 'batal')
                            Daftar Riwayat Pesanan Dibatalkan
                        @else
                            Seluruh Pesanan Wilayah
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Menampilkan pesanan di bawah wilayah tugas operasional Anda.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <!-- Filter Multi-Wilayah Tugas Admin -->
                    @if ($assignedRegions->count() > 1)
                        <form method="GET" action="{{ route('admin.orders.index') }}" class="inline-block">
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            @if (request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            <div class="flex items-center space-x-2">
                                <label for="region_filter" class="text-xs font-semibold text-slate-600 shrink-0">Wilayah:</label>
                                <select id="region_filter" name="region_id" onchange="this.form.submit()"
                                        class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-1.5 pl-3 pr-8 cursor-pointer">
                                    <option value="">Semua Wilayah Tugas ({{ $assignedRegions->count() }})</option>
                                    @foreach ($assignedRegions as $r)
                                        <option value="{{ $r->id }}" @selected($selectedRegionId == $r->id)>{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    @endif

                    <!-- Search Box -->
                    <form method="GET" action="{{ route('admin.orders.index') }}" class="relative">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        @if ($selectedRegionId)
                            <input type="hidden" name="region_id" value="{{ $selectedRegionId }}">
                        @endif
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari kode, pemesan, mitra..."
                               class="text-xs border-slate-200 rounded-xl pl-8 pr-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 w-full sm:w-56 shadow-xs placeholder-slate-400">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Layanan</th>
                            <th class="px-5 py-3">Pemesan</th>
                            <th class="px-5 py-3">Mitra Terapis</th>
                            <th class="px-5 py-3">Wilayah</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-slate-50/60 transition {{ $order->isStuck() ? 'bg-amber-50/30' : '' }}">
                                <!-- Kode -->
                                <td class="px-5 py-3.5">
                                    <span class="font-mono font-bold text-slate-800 text-xs">
                                        {{ $order->order_code }}
                                    </span>
                                </td>

                                <!-- Layanan -->
                                <td class="px-5 py-3.5 font-semibold text-slate-800">
                                    {{ $order->service_name }}
                                </td>

                                <!-- Pemesan -->
                                <td class="px-5 py-3.5 font-semibold text-slate-800">
                                    {{ $order->recipient_name }}
                                </td>

                                <!-- Mitra Terapis -->
                                <td class="px-5 py-3.5">
                                    @if ($order->mitra)
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-[10px] uppercase">
                                                {{ substr($order->mitra->name, 0, 1) }}
                                            </div>
                                            <span class="font-semibold text-slate-800">{{ $order->mitra->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 font-medium">-</span>
                                    @endif
                                </td>

                                <!-- Wilayah -->
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $order->region->name }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $order->status->badgeClasses() }}">
                                            {{ $order->status->label() }}
                                        </span>

                                        @if ($order->isStuck())
                                            <span class="inline-flex items-center space-x-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-ping"></span>
                                                <span>Macet</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition inline-flex items-center space-x-1">
                                        <span>Detail</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400 text-xs">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Tidak ada data pesanan yang sesuai dengan filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($orders->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>

