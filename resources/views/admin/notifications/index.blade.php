<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Pusat Notifikasi dan Antrean Tugas') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center shadow-2xs text-xs font-semibold">
                <svg class="w-4 h-4 mr-2 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Pintasan Antrean Tugas Operasional (Konsisten & Rapi) -->
        <div class="flex items-center gap-2.5 overflow-x-auto pb-1 scrollbar-none text-xs">
            <span class="text-xs font-semibold text-slate-500 shrink-0">Antrean Tugas:</span>

            <a href="{{ route('admin.mitra.index') }}" 
               class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200/80 shadow-2xs text-slate-700 font-medium inline-flex items-center gap-2 transition shrink-0">
                <span>Antrean Verifikasi Mitra</span>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold {{ $queueSummary['pending_verifications'] > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-slate-100 text-slate-400' }}">
                    {{ $queueSummary['pending_verifications'] }}
                </span>
            </a>

            <a href="{{ route('admin.complaints.index') }}" 
               class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200/80 shadow-2xs text-slate-700 font-medium inline-flex items-center gap-2 transition shrink-0">
                <span>Laporan Aduan Terbuka</span>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold {{ $queueSummary['open_complaints'] > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200/80' : 'bg-slate-100 text-slate-400' }}">
                    {{ $queueSummary['open_complaints'] }}
                </span>
            </a>

            <a href="{{ route('admin.appeals.index') }}" 
               class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200/80 shadow-2xs text-slate-700 font-medium inline-flex items-center gap-2 transition shrink-0">
                <span>Pengajuan Banding Sanksi</span>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold {{ $queueSummary['pending_appeals'] > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200/80' : 'bg-slate-100 text-slate-400' }}">
                    {{ $queueSummary['pending_appeals'] }}
                </span>
            </a>
        </div>

        <!-- Master Table Card (Konsisten dengan menu-menu admin lainnya) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Header Table & Toolbar -->
            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Daftar Pemberitahuan Masuk</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Pemantauan tugas operasional, verifikasi, dan peringatan darurat sistem.</p>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    <!-- Filter Tabs -->
                    <div class="flex items-center gap-1 bg-slate-100/80 p-1 rounded-xl">
                        <a href="{{ route('admin.notifications.index', ['filter' => 'all']) }}" 
                           class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ ($currentFilter ?? 'all') === 'all' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-500 hover:text-slate-800' }}">
                            Semua
                        </a>
                        <a href="{{ route('admin.notifications.index', ['filter' => 'unread']) }}" 
                           class="px-3 py-1 rounded-lg text-xs font-semibold transition inline-flex items-center gap-1.5 {{ ($currentFilter ?? '') === 'unread' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-500 hover:text-slate-800' }}">
                            <span>Belum Dibaca</span>
                            @if ($unreadCount > 0)
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-emerald-600 text-white">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </a>
                    </div>

                    @if ($unreadCount > 0)
                        <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-xl text-xs transition border border-slate-200 shadow-2xs inline-flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Tandai Semua Dibaca</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Table Notifikasi -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5">Waktu</th>
                            <th class="px-5 py-3.5">Pemberitahuan</th>
                            <th class="px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($notifications as $item)
                            @php
                                $isUnread = ! $item->read_at;

                                $categoryLabel = match(true) {
                                    str_contains(strtolower($item->title), 'verifikasi') || str_contains(strtolower($item->title), 'mitra') => 'Verifikasi Mitra',
                                    str_contains(strtolower($item->title), 'aduan') => 'Laporan Aduan',
                                    str_contains(strtolower($item->title), 'banding') => 'Banding Sanksi',
                                    str_contains(strtolower($item->title), 'legal') || str_contains(strtolower($item->title), 'dokumen') => 'Dokumen Legal',
                                    str_contains(strtolower($item->title), 'parameter') => 'Parameter Sistem',
                                    default => 'Operasional Sistem',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition {{ $isUnread ? 'bg-slate-50/30' : '' }}">
                                <!-- Waktu -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-slate-700 font-medium">
                                        {{ $item->created_at->locale('id')->diffForHumans() }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $item->created_at->format('d M Y, H:i') }} WIB
                                    </div>
                                </td>

                                <!-- Pemberitahuan -->
                                <td class="px-5 py-4">
                                    <span class="font-bold text-slate-800 text-xs sm:text-sm">
                                        {{ $item->title }}
                                    </span>
                                </td>

                                <!-- Kategori -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-medium text-slate-700">
                                        {{ $categoryLabel }}
                                    </span>
                                </td>

                                <!-- Status Dot Indikator -->
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    @if ($isUnread)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 ring-2 ring-emerald-100"></span>
                                            <span>Belum Dibaca</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                            <span>Dibaca</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-4 whitespace-nowrap text-right space-x-1.5">
                                    @if ($item->action_url)
                                        <a href="{{ $item->action_url }}" 
                                           class="px-3 py-1.5 bg-white hover:bg-slate-50 text-emerald-700 hover:text-emerald-800 font-semibold rounded-xl text-xs transition border border-slate-200 shadow-2xs inline-flex items-center gap-1 cursor-pointer">
                                            <span>{{ $item->action_label }}</span>
                                            <span>&rarr;</span>
                                        </a>
                                    @endif

                                    @if ($isUnread)
                                        <form method="POST" action="{{ route('admin.notifications.read', $item) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" 
                                                    title="Tandai telah dibaca" 
                                                    class="px-2.5 py-1.5 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-800 font-medium rounded-xl text-xs transition border border-slate-200 shadow-2xs cursor-pointer">
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-slate-400 text-xs">
                                    Belum ada notifikasi yang terekam sesuai filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($notifications->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
