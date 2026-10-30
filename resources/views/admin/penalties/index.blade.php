<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Monitoring Poin Sanksi & Akun
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Pantau akumulasi poin sanksi, penegakan pembekuan akun (suspend), dan riwayat pelanggaran.
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

        <!-- Summary Metric Cards (1x4 Grid) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Bersanksi -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">Total Bersanksi</span>
                    <span class="p-2 rounded-xl bg-slate-100 text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-slate-800">{{ number_format($counts['semua']) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Akun dengan akumulasi poin / sanksi</div>
                </div>
            </div>

            <!-- Peringatan (30-59) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-amber-600">
                    <span class="text-xs font-semibold uppercase tracking-wider">Peringatan (30-59)</span>
                    <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-amber-600">{{ number_format($counts['peringatan']) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Mendapat notifikasi waspada</div>
                </div>
            </div>

            <!-- Suspend Sementara (60-149) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-orange-600">
                    <span class="text-xs font-semibold uppercase tracking-wider">Tersuspend (60-149)</span>
                    <span class="p-2 rounded-xl bg-orange-50 text-orange-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-orange-600">{{ number_format($counts['tersuspend']) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Dibekukan 3 atau 7 hari kerja</div>
                </div>
            </div>

            <!-- Banned Permanen (150+) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-rose-600">
                    <span class="text-xs font-semibold uppercase tracking-wider">Diblokir (150+)</span>
                    <span class="p-2 rounded-xl bg-rose-50 text-rose-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-rose-600">{{ number_format($counts['banned']) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Akses layanan dicabut permanen</div>
                </div>
            </div>
        </div>

        <!-- Filter Status Tabs (Di Luar Tabel, Sesuai Standar Sistem) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <!-- Tab Semua -->
            <a href="{{ route('admin.penalties.index', array_merge(request()->except('status', 'page'), ['status' => 'semua'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $currentTab === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Semua
            </a>

            <!-- Tab Peringatan -->
            <a href="{{ route('admin.penalties.index', array_merge(request()->except('status', 'page'), ['status' => 'peringatan'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $currentTab === 'peringatan' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Peringatan
            </a>

            <!-- Tab Tersuspend -->
            <a href="{{ route('admin.penalties.index', array_merge(request()->except('status', 'page'), ['status' => 'tersuspend'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $currentTab === 'tersuspend' ? 'bg-orange-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Tersuspend
            </a>

            <!-- Tab Diblokir -->
            <a href="{{ route('admin.penalties.index', array_merge(request()->except('status', 'page'), ['status' => 'banned'])) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $currentTab === 'banned' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Diblokir
            </a>
        </div>

        <!-- Master Table Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <!-- Header Controls: Judul di Atas, Filter di Bawah Teks (Sesuai Referensi Laporan Aduan) -->
            <div class="p-5 border-b border-slate-100 space-y-3.5">
                <h3 class="font-bold text-slate-800 text-base">
                    Daftar Pengguna Bersanksi
                </h3>

                <!-- Filters & Search Form (Di Bawah Teks Judul) -->
                <form method="GET" action="{{ route('admin.penalties.index') }}" class="flex flex-wrap items-center gap-3">
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
                               placeholder="Cari nama, email, no HP..." 
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
                        <a href="{{ route('admin.penalties.index', ['status' => $currentTab]) }}" 
                           title="Reset Filter"
                           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Table of Penalized Users -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Pengguna</th>
                            <th class="px-5 py-3">Peran</th>
                            <th class="px-5 py-3">Wilayah</th>
                            <th class="px-5 py-3 text-center">Akumulasi Poin</th>
                            <th class="px-5 py-3">Status Sanksi</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($users as $penalizedUser)
                            @php
                                $regionName = $penalizedUser->isMitra() 
                                    ? ($penalizedUser->partnerProfile?->region?->name ?? 'Belum ditentukan')
                                    : ($penalizedUser->customerAddresses?->first()?->region?->name ?? 'Belum ditentukan');
                                
                                $points = $penalizedUser->penalty_points;
                                $statusColor = 'text-slate-600';
                                $dotColor = 'bg-slate-400';
                                $statusLabel = 'Normal';

                                if ($penalizedUser->status === \App\Enums\UserStatus::Diblokir || $points >= \App\Services\PenaltyService::THRESHOLD_BANNED) {
                                    $statusColor = 'text-rose-700';
                                    $dotColor = 'bg-rose-500';
                                    $statusLabel = 'Diblokir Permanen';
                                } elseif ($penalizedUser->status === \App\Enums\UserStatus::Ditangguhkan || $points >= \App\Services\PenaltyService::THRESHOLD_SUSPEND_3_DAYS) {
                                    $statusColor = 'text-orange-700';
                                    $dotColor = 'bg-orange-500';
                                    $statusLabel = 'Tersuspend';
                                } elseif ($points >= \App\Services\PenaltyService::THRESHOLD_WARNING) {
                                    $statusColor = 'text-amber-800';
                                    $dotColor = 'bg-amber-500';
                                    $statusLabel = 'Peringatan Aktif';
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- User Info -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">{{ $penalizedUser->name }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $penalizedUser->email }} &bull; {{ $penalizedUser->phone ?? '-' }}</div>
                                </td>

                                <!-- Role -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="font-medium text-slate-700 text-xs">{{ $penalizedUser->isMitra() ? 'Mitra' : 'Pelanggan' }}</span>
                                </td>

                                <!-- Region -->
                                <td class="px-5 py-3.5 whitespace-nowrap text-slate-600">
                                    {{ $regionName }}
                                </td>

                                <!-- Points Accumulation -->
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    <span class="font-bold text-sm {{ $points >= 150 ? 'text-rose-600' : ($points >= 60 ? 'text-orange-600' : ($points >= 30 ? 'text-amber-600' : 'text-slate-700')) }}">
                                        {{ $points }} Poin
                                    </span>
                                </td>

                                <!-- Sanction Status -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $statusColor }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                        <span>{{ $statusLabel }}</span>
                                    </span>
                                </td>

                                <!-- Action -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.penalties.show', $penalizedUser) }}" 
                                       class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline inline-flex items-center gap-1">
                                        <span>Detail & Audit</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">Tidak ada akun dengan sanksi</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Semua pengguna berada dalam status aman tanpa poin pelanggaran aktif.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

