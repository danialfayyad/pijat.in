<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800 leading-tight">
            {{ __('Meja Kerja Live Chat Bantuan Wilayah') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold shadow-xs">
                {{ session('error') }}
            </div>
        @endif

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Tiket Aktif</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($counts['aktif']) }}</div>
                <div class="text-[11px] text-slate-400">Perlu penanganan admin</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Menunggu Respon</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-2xl font-bold text-amber-600">{{ number_format($counts['menunggu']) }}</div>
                <div class="text-[11px] text-slate-400">Belum ada admin yang membalas</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Sedang Berlangsung</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <div class="text-2xl font-bold text-blue-700">{{ number_format($counts['berlangsung']) }}</div>
                <div class="text-[11px] text-slate-400">Sedang dalam obrolan aktif</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Selesai Ditangani</span>
                    <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                </div>
                <div class="text-2xl font-bold text-slate-600">{{ number_format($counts['selesai']) }}</div>
                <div class="text-[11px] text-slate-400">Kasus bantuan ditutup</div>
            </div>
        </div>

        <!-- Filter Status Tabs (Di Luar Tabel, Sesuai Standar Sistem) -->
        <div class="flex items-center gap-2.5 overflow-x-auto pb-1 scrollbar-none">
            <!-- Tab Semua -->
            <a href="{{ route('admin.support-chats.index', array_merge(request()->except('status', 'page'), ['status' => 'semua'])) }}"
               class="px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ $currentStatus === 'semua' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/90' }}">
                Semua
            </a>

            <!-- Tab Menunggu -->
            <a href="{{ route('admin.support-chats.index', array_merge(request()->except('status', 'page'), ['status' => 'menunggu'])) }}"
               class="px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ $currentStatus === 'menunggu' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/90' }}">
                Menunggu
            </a>

            <!-- Tab Berlangsung -->
            <a href="{{ route('admin.support-chats.index', array_merge(request()->except('status', 'page'), ['status' => 'berlangsung'])) }}"
               class="px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ $currentStatus === 'berlangsung' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/90' }}">
                Berlangsung
            </a>

            <!-- Tab Selesai -->
            <a href="{{ route('admin.support-chats.index', array_merge(request()->except('status', 'page'), ['status' => 'selesai'])) }}"
               class="px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ $currentStatus === 'selesai' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/90' }}">
                Selesai
            </a>
        </div>

        <!-- Master Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Header Toolbar -->
            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Antrean Live Chat Wilayah</h3>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    <!-- Role & Region Filters -->
                    <form method="GET" action="{{ route('admin.support-chats.index') }}" class="flex items-center gap-2 flex-wrap">
                        <input type="hidden" name="status" value="{{ $currentStatus }}">

                        <!-- Search Input -->
                        <div class="relative w-48 sm:w-56">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}" 
                                   placeholder="Cari tiket, subjek, user..." 
                                   class="w-full text-xs border border-slate-200 rounded-xl pl-8 pr-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        </div>
                        
                        <!-- Segmented Role Tabs (Sesuai Desain) -->
                        <div class="bg-slate-100/90 p-1 rounded-2xl inline-flex items-center gap-1 shrink-0">
                            <a href="{{ route('admin.support-chats.index', array_merge(request()->except('role', 'page'), ['role' => 'semua'])) }}"
                               class="px-4 py-1.5 rounded-xl text-xs sm:text-sm font-semibold transition {{ $currentRole === 'semua' || !$currentRole ? 'bg-white text-slate-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-800' }}">
                                Semua
                            </a>
                            <a href="{{ route('admin.support-chats.index', array_merge(request()->except('role', 'page'), ['role' => 'pelanggan'])) }}"
                               class="px-4 py-1.5 rounded-xl text-xs sm:text-sm font-semibold transition {{ $currentRole === 'pelanggan' ? 'bg-white text-slate-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-800' }}">
                                Pelanggan
                            </a>
                            <a href="{{ route('admin.support-chats.index', array_merge(request()->except('role', 'page'), ['role' => 'mitra'])) }}"
                               class="px-4 py-1.5 rounded-xl text-xs sm:text-sm font-semibold transition {{ $currentRole === 'mitra' ? 'bg-white text-slate-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-800' }}">
                                Mitra
                            </a>
                        </div>

                        @if ($accessibleRegions->count() > 1)
                            <select name="region_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 py-1.5 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                                <option value="">Semua Wilayah</option>
                                @foreach ($accessibleRegions as $reg)
                                    <option value="{{ $reg->id }}" @selected((int) $selectedRegionId === $reg->id)>{{ $reg->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Table Antrean Chat -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5">Tiket / Subjek</th>
                            <th class="px-5 py-3.5">Pengguna</th>
                            <th class="px-5 py-3.5">Wilayah</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5">Waktu Aktivitas</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tickets as $item)
                            @php
                                $statusBadge = match($item->status) {
                                    'menunggu' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Menunggu Respon', 'dot' => 'bg-amber-500'],
                                    'berlangsung' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Sedang Chat', 'dot' => 'bg-blue-500'],
                                    'selesai' => ['bg' => 'bg-slate-100 text-slate-500 border-slate-200', 'label' => 'Selesai', 'dot' => 'bg-slate-300'],
                                    default => ['bg' => 'bg-slate-100 text-slate-600 border-slate-200', 'label' => $item->status, 'dot' => 'bg-slate-400'],
                                };

                                $userBadge = match($item->user_role) {
                                    'mitra' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Mitra'],
                                    default => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Pelanggan'],
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition {{ $item->status === 'menunggu' ? 'bg-amber-50/20' : '' }}">
                                <!-- Tiket & Subjek -->
                                <td class="px-5 py-4">
                                    <div class="font-mono text-[11px] text-slate-400 font-semibold">
                                        {{ $item->ticket_code }}
                                    </div>
                                    <div class="font-bold text-slate-800 text-xs sm:text-sm mt-0.5">
                                        {{ $item->subject }}
                                    </div>
                                </td>

                                <!-- Pengguna -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold border {{ $userBadge['bg'] }}">
                                            {{ $userBadge['label'] }}
                                        </span>
                                        <span class="font-semibold text-slate-800">
                                            {{ $item->user?->name ?? 'Pengguna' }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $item->user?->phone ?? $item->user?->email }}
                                    </div>
                                </td>

                                <!-- Wilayah -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-medium text-slate-700">
                                        {{ $item->region?->name ?? '-' }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $statusBadge['bg'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $statusBadge['dot'] }}"></span>
                                        <span>{{ $statusBadge['label'] }}</span>
                                    </span>
                                </td>

                                <!-- Waktu -->
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500">
                                    <div>
                                        {{ ($item->last_message_at ?? $item->created_at)->locale('id')->diffForHumans() }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ ($item->last_message_at ?? $item->created_at)->format('H:i') }} WIB
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('admin.support-chats.show', $item) }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-xs {{ $item->status === 'menunggu' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                                        <span>{{ $item->status === 'menunggu' ? 'Tangani Chat' : 'Buka Chat' }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400 text-xs">
                                    Tidak ada tiket bantuan chat pada antrean ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($tickets->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

