<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Manajemen Pengguna Wilayah
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Kelola status akun, profil, performa rating mitra, dan moderasi akun pelanggan di wilayah tugas.
            </p>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-xl flex items-center gap-2 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl shadow-2xs">
                <div class="font-bold mb-1">Terjadi kesalahan:</div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Stats Overview Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        {{ $activeTab === 'partners' ? 'Total Mitra' : 'Total Pelanggan' }}
                    </span>
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-slate-800">{{ number_format($stats['total']) }}</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Terdaftar di wilayah tugas</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Akun Aktif</span>
                    <span class="p-2 bg-teal-50 text-teal-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-teal-600">{{ number_format($stats['verified']) }}</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Status terverifikasi</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ditangguhkan</span>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-amber-600">{{ number_format($stats['suspended']) }}</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Sanksi suspend sementara</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Diblokir</span>
                    <span class="p-2 bg-rose-50 text-rose-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-rose-600">{{ number_format($stats['blocked']) }}</span>
                    <span class="text-xs text-slate-400 block mt-0.5">Daftar hitam (Blacklist)</span>
                </div>
            </div>
        </div>

        <!-- Role Tab Navigation & Filter Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Tabs -->
            <div class="flex border-b border-slate-200/80 px-6 pt-3 bg-slate-50/50">
                <a href="{{ route('admin.users.index', array_merge(request()->query(), ['tab' => 'partners', 'page' => 1])) }}"
                   class="pb-3 px-4 text-sm font-bold border-b-2 transition flex items-center gap-2 {{ $activeTab === 'partners' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Mitra Terapis
                    <span class="ml-1.5 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'partners' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200/70 text-slate-600' }}">
                        {{ $activeTab === 'partners' ? number_format($stats['total']) : 'Mitra' }}
                    </span>
                </a>

                <a href="{{ route('admin.users.index', array_merge(request()->query(), ['tab' => 'customers', 'page' => 1])) }}"
                   class="pb-3 px-4 text-sm font-bold border-b-2 transition flex items-center gap-2 {{ $activeTab === 'customers' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Pelanggan / Customer
                    <span class="ml-1.5 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'customers' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200/70 text-slate-600' }}">
                        {{ $activeTab === 'customers' ? number_format($stats['total']) : 'Customer' }}
                    </span>
                </a>
            </div>

            <!-- Search & Filters -->
            <form method="GET" action="{{ route('admin.users.index') }}" class="p-6 border-b border-slate-200/80 bg-white flex flex-col md:flex-row gap-4 justify-between items-stretch md:items-center">
                <input type="hidden" name="tab" value="{{ $activeTab }}">

                <!-- Search -->
                <div class="relative flex-1 max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="Cari nama, email, atau no. HP..." 
                           class="w-full pl-10 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 transition">
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Region Filter -->
                    @if ($assignedRegions->count() > 1 || Auth::user()->isSuperadmin())
                        <div>
                            <select name="region_id" onchange="this.form.submit()" class="text-xs py-2 px-3 rounded-xl border border-slate-200 bg-white font-medium text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Semua Wilayah Tugas</option>
                                @foreach ($assignedRegions as $region)
                                    <option value="{{ $region->id }}" {{ $selectedRegionId == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Status Filter -->
                    <div>
                        <select name="status" onchange="this.form.submit()" class="text-xs py-2 px-3 rounded-xl border border-slate-200 bg-white font-medium text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="semua" {{ $statusFilter === 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="terverifikasi" {{ $statusFilter === 'terverifikasi' ? 'selected' : '' }}>Aktif / Terverifikasi</option>
                            <option value="ditangguhkan" {{ $statusFilter === 'ditangguhkan' ? 'selected' : '' }}>Ditangguhkan (Suspend)</option>
                            <option value="diblokir" {{ $statusFilter === 'diblokir' ? 'selected' : '' }}>Diblokir (Banned)</option>
                            @if ($activeTab === 'partners')
                                <option value="menunggu_verifikasi" {{ $statusFilter === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            @endif
                        </select>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-xl transition">
                        Terapkan
                    </button>
                    @if ($search !== '' || $statusFilter !== 'semua' || $selectedRegionId)
                        <a href="{{ route('admin.users.index', ['tab' => $activeTab]) }}" class="px-3 py-2 text-xs font-semibold text-slate-500 hover:text-slate-700 rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200/80">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                {{ $activeTab === 'partners' ? 'Mitra Terapis' : 'Pelanggan' }}
                            </th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Wilayah</th>
                            @if ($activeTab === 'partners')
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Performa Rating</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Poin Sanksi</th>
                            @else
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Alamat Utama</th>
                            @endif
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Status Akun</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200/80">
                        @forelse ($users as $userItem)
                            @php
                                $regionName = $activeTab === 'partners' 
                                    ? ($userItem->partnerProfile?->region?->name ?? $userItem->region?->name ?? 'Belum terdata')
                                    : ($userItem->primaryAddress?->region?->name ?? $userItem->region?->name ?? 'Belum ada alamat');
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($userItem->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                                <span>{{ $userItem->name }}</span>
                                                @if ($userItem->gender)
                                                    <span class="text-[10px] px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded font-normal uppercase">
                                                        {{ $userItem->gender }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                                                <span>{{ $userItem->phone ?? '-' }}</span>
                                                <span>•</span>
                                                <span>{{ $userItem->email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg">
                                        {{ $regionName }}
                                    </span>
                                </td>

                                @if ($activeTab === 'partners')
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $ratingVal = $userItem->partnerRating();
                                            $ratingTotal = $userItem->partnerRatingsCount();
                                        @endphp
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="text-xs font-bold text-slate-800">{{ number_format($ratingVal, 1) }}</span>
                                            <span class="text-[11px] text-slate-400">({{ $ratingTotal }} ulasan)</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $pts = $userItem->penalty_points;
                                        @endphp
                                        @if ($pts > 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-lg {{ $pts >= 60 ? 'bg-rose-100 text-rose-800' : ($pts >= 30 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                                {{ $pts }} Poin
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs text-emerald-700 bg-emerald-50 rounded font-medium">
                                                0 Poin (Bersih)
                                            </span>
                                        @endif
                                    </td>
                                @else
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-slate-700 max-w-xs truncate" title="{{ $userItem->primaryAddress?->address_text ?? '-' }}">
                                            {{ $userItem->primaryAddress?->label ? '[' . $userItem->primaryAddress->label . '] ' : '' }}{{ $userItem->primaryAddress?->address_text ?? 'Belum ada alamat tersimpan' }}
                                        </div>
                                    </td>
                                @endif

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $st = $userItem->status?->value ?? (string) $userItem->status;
                                    @endphp
                                    @if ($st === 'terverifikasi')
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-emerald-800 bg-emerald-100 rounded-lg">
                                            Aktif / Terverifikasi
                                        </span>
                                    @elseif ($st === 'ditangguhkan')
                                        <div class="inline-flex flex-col">
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold text-amber-800 bg-amber-100 rounded-lg">
                                                Ditangguhkan
                                            </span>
                                            @if ($userItem->suspended_until)
                                                <span class="text-[10px] text-amber-600 mt-0.5">
                                                    s.d. {{ $userItem->suspended_until->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    @elseif ($st === 'diblokir')
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-rose-800 bg-rose-100 rounded-lg">
                                            Diblokir Permanen
                                        </span>
                                    @elseif ($st === 'menunggu_verifikasi')
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-sky-800 bg-sky-100 rounded-lg">
                                            Menunggu Verifikasi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-slate-600 bg-slate-100 rounded-lg">
                                            {{ ucfirst(str_replace('_', ' ', $st)) }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    @if ($activeTab === 'partners')
                                        <a href="{{ route('admin.users.partners.show', $userItem) }}" 
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300 text-xs font-bold rounded-xl transition shadow-2xs">
                                            <span>Detail Profil & Rating</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.users.customers.show', $userItem) }}" 
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300 text-xs font-bold rounded-xl transition shadow-2xs">
                                            <span>Detail & Moderasi</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $activeTab === 'partners' ? 6 : 5 }}" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <p class="text-xs font-medium text-slate-500">Tidak ada data pengguna ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($users->hasPages())
                <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
