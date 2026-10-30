<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800 leading-tight">
            {{ __('Pusat Laporan Aduan Wilayah') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold shadow-xs">
                {{ session('error') }}
            </div>
        @endif

        <!-- Kartu Ringkasan Metrik (Grid 1 x 5 Sebaris Penuh) -->
        <div class="grid grid-cols-5 gap-4 w-full" style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 1rem;">
            <!-- Total Laporan -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Total Laporan</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($totalCount) }}</div>
                <div class="text-[11px] text-slate-400">Semua riwayat aduan</div>
            </div>

            <!-- Aduan Baru -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Aduan Baru</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($pendingCount) }}</div>
                <div class="text-[11px] text-amber-600 font-medium">Menunggu respon</div>
            </div>

            <!-- Sedang Diproses -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Sedang Diproses</span>
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($inProcessCount) }}</div>
                <div class="text-[11px] text-sky-600 font-medium">Tahap investigasi</div>
            </div>

            <!-- Selesai Ditangani -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Selesai Ditangani</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($resolvedCount) }}</div>
                <div class="text-[11px] text-emerald-600 font-medium">Putusan selesai</div>
            </div>

            <!-- Ditolak / Diabaikan -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Ditolak / Diabaikan</span>
                    <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($rejectedCount) }}</div>
                <div class="text-[11px] text-slate-400">Tidak berdasar</div>
            </div>
        </div>

        <!-- Master Table Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <!-- Header Controls: Judul "Daftar Aduan" di Atas, Filter di Bawah Teks -->
            <div class="p-5 border-b border-slate-100 space-y-3.5">
                <h3 class="font-bold text-slate-800 text-base">
                    Daftar Aduan
                </h3>

                <!-- Filters & Search Form (Di Bawah Teks: Cari -> Wilayah -> Status) -->
                <form method="GET" action="{{ route('admin.complaints.index') }}" class="flex items-center gap-3">
                    <!-- 1. Search Input (Posisi Pertama) -->
                    <div class="relative w-56 sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari kode, pesanan, pelapor..." 
                               class="w-full text-xs sm:text-sm border-slate-200 rounded-xl pl-9 pr-3.5 py-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs placeholder-slate-400">
                    </div>

                    <!-- 2. Multi-Wilayah Filter (Posisi Kedua) -->
                    @if ($accessibleRegions->count() > 1)
                        <div class="flex items-center space-x-1.5 shrink-0">
                            <label for="region_filter" class="text-xs font-semibold text-slate-600 shrink-0">Wilayah:</label>
                            <select id="region_filter" name="region_id" onchange="this.form.submit()"
                                    class="text-xs sm:text-sm font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                                <option value="">Semua Wilayah ({{ $accessibleRegions->count() }})</option>
                                @foreach ($accessibleRegions as $reg)
                                    <option value="{{ $reg->id }}" {{ request('region_id') == $reg->id ? 'selected' : '' }}>
                                        {{ $reg->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- 3. Status Filter (Posisi Ketiga) -->
                    <div class="flex items-center space-x-1.5 shrink-0">
                        <label for="status_filter" class="text-xs font-semibold text-slate-600 shrink-0">Status:</label>
                        <select id="status_filter" name="status" onchange="this.form.submit()"
                                class="text-xs sm:text-sm font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                            <option value="semua" {{ $currentTab === 'semua' ? 'selected' : '' }}>Semua Status ({{ $totalCount }})</option>
                            <option value="menunggu" {{ $currentTab === 'menunggu' ? 'selected' : '' }}>Aduan Baru ({{ $pendingCount }})</option>
                            <option value="diproses" {{ $currentTab === 'diproses' ? 'selected' : '' }}>Sedang Diproses ({{ $inProcessCount }})</option>
                            <option value="selesai" {{ $currentTab === 'selesai' ? 'selected' : '' }}>Selesai ({{ $resolvedCount }})</option>
                            <option value="ditolak" {{ $currentTab === 'ditolak' ? 'selected' : '' }}>Ditolak ({{ $rejectedCount }})</option>
                        </select>
                    </div>

                    <!-- 4. Reset Link (Jika Filter / Search Aktif) -->
                    @if (request('search') || request('region_id') || (request('status') && request('status') !== 'semua'))
                        <a href="{{ route('admin.complaints.index') }}" 
                           title="Reset Filter"
                           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Complaints Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Kode Aduan</th>
                            <th class="px-5 py-3">Pesanan</th>
                            <th class="px-5 py-3">Pelapor</th>
                            <th class="px-5 py-3">Wilayah</th>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($complaints as $complaint)
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- Kode Aduan -->
                                <td class="px-5 py-3 font-bold text-slate-800 tracking-tight whitespace-nowrap">
                                    {{ $complaint->complaint_code }}
                                </td>

                                <!-- Pesanan -->
                                <td class="px-5 py-3 whitespace-nowrap">
                                    @if ($complaint->order)
                                        <a href="{{ route('admin.orders.show', $complaint->order) }}" class="font-bold text-emerald-700 hover:underline">
                                            {{ $complaint->order->order_code }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>

                                <!-- Pelapor -->
                                <td class="px-5 py-3 font-semibold text-slate-800">
                                    {{ $complaint->complainant?->name ?? 'Pelapor' }}
                                </td>

                                <!-- Wilayah -->
                                <td class="px-5 py-3 text-slate-600 font-medium whitespace-nowrap">
                                    {{ $complaint->region?->name ?? '-' }}
                                </td>

                                <!-- Kategori -->
                                <td class="px-5 py-3 text-slate-700">
                                    {{ $complaint->category_label }}
                                </td>

                                <!-- Status Badge -->
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $complaint->status->dotClass() }}"></span>
                                        {{ $complaint->status->label() }}
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.complaints.show', $complaint) }}" 
                                       class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1">
                                        <span>Periksa</span>
                                        <span>&rarr;</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">Tidak ada aduan ditemukan</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada laporan keluhan dengan kriteria filter yang Anda pilih.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            @if ($complaints->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $complaints->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
