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
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $tab === 'semua' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Semua
            </a>

            <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['tab' => 'aktif', 'page' => 1])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $tab === 'aktif' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Aktif
            </a>

            <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['tab' => 'macet', 'page' => 1])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap inline-flex items-center gap-1.5 {{ $tab === 'macet' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                <span>Macet</span>
                @if ($stuckCount > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold leading-none {{ $tab === 'macet' ? 'bg-emerald-700/80 text-white' : 'bg-amber-100 text-amber-800' }}">
                        {{ $stuckCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['tab' => 'selesai', 'page' => 1])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $tab === 'selesai' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Selesai
            </a>

            <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['tab' => 'batal', 'page' => 1])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $tab === 'batal' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Dibatalkan
            </a>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <!-- Header Controls: Title & Integrated Filters -->
            <div class="p-5 border-b border-slate-100 space-y-3.5">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-base">
                        Daftar Pesanan Wilayah
                    </h3>
                    <span class="text-xs text-slate-400 font-medium">
                        {{ number_format($orders->total()) }} pesanan
                    </span>
                </div>

                <!-- Single Unified Filter Toolbar Form -->
                <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <!-- 1. Search Box (Posisi Pertama) -->
                    <div class="relative w-56 sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari kode, pemesan, mitra..." 
                               class="w-full text-xs border border-slate-200 rounded-xl pl-9 pr-3.5 py-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs placeholder:text-xs placeholder-slate-400 bg-white">
                    </div>

                    <!-- 2. Multi-Wilayah Filter Dropdown (Posisi Kedua) -->
                    @if ($assignedRegions->count() > 1)
                        <div class="flex items-center space-x-1.5 shrink-0">
                            <label for="region_filter" class="text-xs font-semibold text-slate-600 shrink-0">Wilayah:</label>
                            <select id="region_filter" name="region_id" onchange="this.form.submit()"
                                    class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                                <option value="">Semua Wilayah Tugas ({{ $assignedRegions->count() }})</option>
                                @foreach ($assignedRegions as $r)
                                    <option value="{{ $r->id }}" @selected($selectedRegionId == $r->id)>
                                        {{ $r->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- 3. Reset Button [✕] -->
                    @if (request('search') || $selectedRegionId)
                        <a href="{{ route('admin.orders.index', ['tab' => $tab]) }}" 
                           title="Reset Filter"
                           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>
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
                                        <span class="font-medium text-slate-800">{{ $order->mitra->name }}</span>
                                    @else
                                        <span class="text-slate-400 font-medium">-</span>
                                    @endif
                                </td>

                                <!-- Wilayah -->
                                <td class="px-5 py-3.5 text-slate-600 font-medium whitespace-nowrap">
                                    {{ $order->region->name }}
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $order->status->dotClass() }}"></span>
                                            {{ $order->status->label() }}
                                        </span>

                                        @if ($order->isStuck())
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                                <span>Macet</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="text-xs font-semibold text-slate-700 hover:text-emerald-700 hover:underline inline-flex items-center gap-1">
                                        <span>Detail</span>
                                        <span>&rarr;</span>
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

