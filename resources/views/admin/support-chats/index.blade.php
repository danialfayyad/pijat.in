<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-bold text-slate-900 leading-tight">
            {{ __('Meja Kerja Live Chat Bantuan') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @if (session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-2xs">
                <span class="text-[11px] font-medium text-slate-500 block">Tiket Aktif</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($counts['aktif']) }}</div>
            </div>

            <div class="bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-2xs">
                <span class="text-[11px] font-medium text-slate-500 block">Menunggu Respon</span>
                <div class="text-xl font-bold text-amber-600 mt-0.5">{{ number_format($counts['menunggu']) }}</div>
            </div>

            <div class="bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-2xs">
                <span class="text-[11px] font-medium text-slate-500 block">Sedang Berlangsung</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($counts['berlangsung']) }}</div>
            </div>

            <div class="bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-2xs">
                <span class="text-[11px] font-medium text-slate-500 block">Selesai Ditangani</span>
                <div class="text-xl font-bold text-slate-400 mt-0.5">{{ number_format($counts['selesai']) }}</div>
            </div>
        </div>

        <!-- Filter & Navigation Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/70 text-xs font-semibold">
                <a href="{{ route('admin.support-chats.index', array_merge(request()->except('role', 'page'), ['role' => 'semua'])) }}"
                   class="px-3 py-1 rounded-lg transition {{ $currentRole === 'semua' || !$currentRole ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                    Semua Pengguna
                </a>
                <a href="{{ route('admin.support-chats.index', array_merge(request()->except('role', 'page'), ['role' => 'pelanggan'])) }}"
                   class="px-3 py-1 rounded-lg transition {{ $currentRole === 'pelanggan' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                    Pelanggan
                </a>
                <a href="{{ route('admin.support-chats.index', array_merge(request()->except('role', 'page'), ['role' => 'mitra'])) }}"
                   class="px-3 py-1 rounded-lg transition {{ $currentRole === 'mitra' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                    Mitra
                </a>
            </div>
        </div>

        <!-- Master Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <!-- Header Controls: Search & Dropdowns -->
            <div class="p-3.5 border-b border-slate-100">
                <form method="GET" action="{{ route('admin.support-chats.index') }}" class="flex flex-wrap items-center gap-2.5">
                    @if (request('role') && request('role') !== 'semua')
                        <input type="hidden" name="role" value="{{ request('role') }}">
                    @endif

                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[200px] sm:min-w-[260px] max-w-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}" 
                               placeholder="Cari kode tiket, subjek, nama..." 
                               class="w-full text-xs border border-slate-200 rounded-xl pl-9 pr-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 bg-white placeholder:text-xs placeholder-slate-400">
                    </div>

                    <!-- Multi-Wilayah Filter Dropdown -->
                    @if ($accessibleRegions->count() > 1)
                        <div class="shrink-0">
                            <select name="region_id" onchange="this.form.submit()"
                                    class="text-xs font-medium border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white py-1.5 pl-3 pr-8 cursor-pointer text-slate-700">
                                <option value="">Semua Wilayah ({{ $accessibleRegions->count() }})</option>
                                @foreach ($accessibleRegions as $reg)
                                    <option value="{{ $reg->id }}" @selected((int) $selectedRegionId === $reg->id)>
                                        {{ $reg->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Status Chat Filter Dropdown -->
                    <div class="shrink-0">
                        <select name="status" onchange="this.form.submit()"
                                class="text-xs font-medium border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white py-1.5 pl-3 pr-8 cursor-pointer text-slate-700">
                            <option value="semua" {{ $currentStatus === 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="menunggu" {{ $currentStatus === 'menunggu' ? 'selected' : '' }}>Menunggu Respon</option>
                            <option value="berlangsung" {{ $currentStatus === 'berlangsung' ? 'selected' : '' }}>Sedang Berlangsung</option>
                            <option value="selesai" {{ $currentStatus === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>

                    <!-- Reset Button -->
                    @if ($search !== '' || (request('role') && request('role') !== 'semua') || $selectedRegionId || ($currentStatus !== 'semua' && request('status')))
                        <a href="{{ route('admin.support-chats.index', (request('role') && request('role') !== 'semua') ? ['role' => request('role')] : []) }}" 
                           title="Reset Filter"
                           class="px-2 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition shrink-0 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Reset</span>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Table Antrean Chat -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/70 text-[11px] font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Tiket & Subjek</th>
                            <th class="px-4 py-3 font-semibold">Pengirim</th>
                            <th class="px-4 py-3 font-semibold">Wilayah</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Aktivitas</th>
                            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tickets as $item)
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- Tiket & Subjek -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-semibold text-slate-800 shrink-0">
                                            {{ $item->ticket_code }}
                                        </span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="text-xs text-slate-700 truncate max-w-xs sm:max-w-md font-medium" title="{{ $item->subject }}">
                                            {{ $item->subject }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Pengirim -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-medium text-slate-900 text-xs">
                                            {{ $item->user?->name ?? 'Pengguna' }}
                                        </span>
                                        <span class="text-[11px] text-slate-400">
                                            ({{ $item->user_role === 'mitra' ? 'Mitra' : 'Pelanggan' }})
                                        </span>
                                    </div>
                                </td>

                                <!-- Wilayah -->
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-600">
                                    {{ $item->region?->name ?? '-' }}
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-xs">
                                        @if ($item->status === 'menunggu')
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span class="font-medium text-amber-700">Menunggu Respon</span>
                                        @elseif ($item->status === 'berlangsung')
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span class="font-medium text-emerald-700">Sedang Chat</span>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                            <span class="font-medium text-slate-400">Selesai</span>
                                        @endif
                                    </span>
                                </td>

                                <!-- Waktu -->
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500">
                                    {{ ($item->last_message_at ?? $item->created_at)->locale('id')->diffForHumans() }}
                                </td>

                                <!-- Aksi -->
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    @if ($item->status === 'menunggu')
                                        <a href="{{ route('admin.support-chats.show', $item) }}" 
                                           class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-2xs inline-flex items-center gap-1">
                                            <span>Tangani</span>
                                            <span>&rarr;</span>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.support-chats.show', $item) }}" 
                                           class="text-xs font-medium text-slate-600 hover:text-emerald-700 hover:underline inline-flex items-center gap-1">
                                            <span>Buka</span>
                                            <span>&rarr;</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-xs">
                                    <p class="font-medium text-slate-600">Tidak ada tiket bantuan</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada antrean chat bantuan yang sesuai dengan filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($tickets->hasPages())
                <div class="p-3.5 border-t border-slate-100">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

