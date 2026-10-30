<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Tinjauan Banding Sanksi
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Verifikasi permohonan banding dan surat pernyataan dari mitra atau pelanggan di wilayah Anda.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold rounded-2xl flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold rounded-2xl flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Summary Metric Cards (1x4 Grid) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Banding -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">Total Banding</span>
                    <span class="p-2 rounded-xl bg-slate-100 text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-slate-800">{{ number_format($counts['semua']) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Keseluruhan berkas diajukan</div>
                </div>
            </div>

            <!-- Menunggu Peninjauan -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-amber-600">
                    <span class="text-xs font-semibold uppercase tracking-wider">Menunggu Tinjauan</span>
                    <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-amber-600">{{ number_format($counts['menunggu']) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Perlu verifikasi admin wilayah</div>
                </div>
            </div>

            <!-- Disetujui -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-emerald-600">
                    <span class="text-xs font-semibold uppercase tracking-wider">Disetujui</span>
                    <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-emerald-600">{{ number_format($counts['disetujui']) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Poin dipulihkan & sanksi dicabut</div>
                </div>
            </div>

            <!-- Ditolak -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-rose-600">
                    <span class="text-xs font-semibold uppercase tracking-wider">Ditolak</span>
                    <span class="p-2 rounded-xl bg-rose-50 text-rose-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-rose-600">{{ number_format($counts['ditolak']) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Sanksi poin tetap diberlakukan</div>
                </div>
            </div>
        </div>

        <!-- Filter Status Tabs (Di Luar Tabel, Sesuai Standar Sistem) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <!-- Tab Semua -->
            <a href="{{ route('admin.appeals.index', array_merge(request()->except('status', 'page'), ['status' => 'semua'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $currentTab === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Semua
            </a>

            <!-- Tab Menunggu -->
            <a href="{{ route('admin.appeals.index', array_merge(request()->except('status', 'page'), ['status' => 'menunggu'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $currentTab === 'menunggu' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Menunggu Tinjauan
            </a>

            <!-- Tab Disetujui -->
            <a href="{{ route('admin.appeals.index', array_merge(request()->except('status', 'page'), ['status' => 'disetujui'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $currentTab === 'disetujui' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Disetujui
            </a>

            <!-- Tab Ditolak -->
            <a href="{{ route('admin.appeals.index', array_merge(request()->except('status', 'page'), ['status' => 'ditolak'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $currentTab === 'ditolak' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Ditolak
            </a>
        </div>

        <!-- Master Table Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <!-- Header Controls: Judul di Atas, Filter di Bawah Teks (Sesuai Referensi Laporan Aduan) -->
            <div class="p-5 border-b border-slate-100 space-y-3.5">
                <h3 class="font-bold text-slate-800 text-base">
                    Daftar Berkas Banding
                </h3>

                <!-- Filters & Search Form (Di Bawah Teks Judul) -->
                <form method="GET" action="{{ route('admin.appeals.index') }}" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="status" value="{{ $currentTab }}">

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
                               placeholder="Cari kode, pemohon..." 
                               class="w-full text-xs sm:text-sm border-slate-200 rounded-xl pl-9 pr-3.5 py-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs placeholder-slate-400">
                    </div>

                    <!-- 2. Multi-Wilayah Filter (Posisi Kedua) -->
                    @if ($accessibleRegions->count() > 1)
                        <div class="flex items-center space-x-1.5 shrink-0">
                            <label for="region_filter" class="text-xs font-semibold text-slate-600 shrink-0">Wilayah:</label>
                            <select id="region_filter" name="region_id" onchange="this.form.submit()"
                                    class="text-xs sm:text-sm font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                                <option value="">Semua Wilayah ({{ $accessibleRegions->count() }})</option>
                                @foreach ($accessibleRegions as $region)
                                    <option value="{{ $region->id }}" {{ (string) request('region_id') === (string) $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- 3. Peran Filter (Posisi Ketiga) -->
                    <div class="flex items-center space-x-1.5 shrink-0">
                        <label for="role_filter" class="text-xs font-semibold text-slate-600 shrink-0">Peran:</label>
                        <select id="role_filter" name="role" onchange="this.form.submit()"
                                class="text-xs sm:text-sm font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                            <option value="">Semua Peran</option>
                            <option value="mitra" {{ request('role') === 'mitra' ? 'selected' : '' }}>Mitra</option>
                            <option value="pelanggan" {{ request('role') === 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
                        </select>
                    </div>

                    <!-- 4. Reset Button -->
                    @if (request('search') || request('role') || request('region_id'))
                        <a href="{{ route('admin.appeals.index', ['status' => $currentTab]) }}" 
                           title="Reset Filter"
                           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Appeals Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3 whitespace-nowrap">Kode Banding</th>
                            <th class="px-5 py-3 whitespace-nowrap">Pemohon</th>
                            <th class="px-5 py-3 whitespace-nowrap">Wilayah</th>
                            <th class="px-5 py-3 whitespace-nowrap">Dasar Banding</th>
                            <th class="px-5 py-3 whitespace-nowrap">Status</th>
                            <th class="px-5 py-3 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($appeals as $appeal)
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- Kode Banding -->
                                <td class="px-5 py-3.5 whitespace-nowrap font-bold text-slate-800">
                                    {{ $appeal->appeal_code }}
                                </td>

                                <!-- Pemohon -->
                                <td class="px-5 py-3.5 whitespace-nowrap font-semibold text-slate-800">
                                    {{ $appeal->user?->name ?? '-' }}
                                </td>

                                <!-- Wilayah -->
                                <td class="px-5 py-3.5 whitespace-nowrap text-slate-600">
                                    {{ $appeal->region?->name ?? '-' }}
                                </td>

                                <!-- Dasar Banding -->
                                <td class="px-5 py-3.5 whitespace-nowrap font-semibold text-slate-800">
                                    {{ $appeal->complaint ? 'Aduan ' . $appeal->complaint->complaint_code : 'Sanksi Poin Akun' }}
                                </td>

                                <!-- Status Badge -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $appeal->status->badgeClasses() }}">
                                        {{ $appeal->status->label() }}
                                    </span>
                                </td>

                                <!-- Action -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.appeals.show', $appeal) }}" 
                                       class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition inline-flex items-center gap-1.5 cursor-pointer">
                                        <span>Tinjau Berkas</span>
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">Tidak ada pengajuan banding</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Belum ada mitra atau pelanggan yang mengajukan permohonan banding pada filter ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($appeals->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $appeals->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

