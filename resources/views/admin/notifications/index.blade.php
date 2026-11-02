<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pusat Notifikasi dan Antrean Tugas</h1>
                <p class="text-sm text-slate-500 mt-1">Pemantauan tugas masuk dan notifikasi operasional pada wilayah tugas Anda.</p>
            </div>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                        <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Tandai Semua Dibaca ({{ $unreadCount }})
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center shadow-xs">
                <svg class="w-5 h-5 mr-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Task Queue Summary Cards (ADM-14) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Verifikasi Mitra Card -->
            <a href="{{ route('admin.mitra.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-500 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-black text-slate-900 group-hover:text-emerald-700 transition">{{ $queueSummary['pending_verifications'] }}</span>
                </div>
                <div class="mt-3">
                    <h2 class="text-sm font-bold text-slate-800">Antrean Verifikasi Mitra</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Berkas pendaftaran mitra baru menunggu verifikasi.</p>
                </div>
            </a>

            <!-- Laporan Aduan Card -->
            <a href="{{ route('admin.complaints.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-500 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-black text-slate-900 group-hover:text-amber-600 transition">{{ $queueSummary['open_complaints'] }}</span>
                </div>
                <div class="mt-3">
                    <h2 class="text-sm font-bold text-slate-800">Laporan Aduan Terbuka</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Aduan pelanggan/mitra yang membutuhkan investigasi.</p>
                </div>
            </a>

            <!-- Banding Sanksi Card -->
            <a href="{{ route('admin.appeals.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-500 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-black text-slate-900 group-hover:text-rose-600 transition">{{ $queueSummary['pending_appeals'] }}</span>
                </div>
                <div class="mt-3">
                    <h2 class="text-sm font-bold text-slate-800">Pengajuan Banding Sanksi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Berkas banding sanksi poin menunggu peninjauan.</p>
                </div>
            </a>
        </div>

        <!-- Notification Feed List -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Riwayat Notifikasi Masuk</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar pemberitahuan aktivitas operasional dan peringatan sistem.</p>
                </div>
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">
                    Total {{ $notifications->total() }} Notifikasi
                </span>
            </div>

            @if ($notifications->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Belum Ada Notifikasi</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Kotak masuk Anda bersih. Notifikasi baru terkait antrean verifikasi atau peristiwa penting akan muncul di sini.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($notifications as $item)
                        <div class="p-5 flex flex-col sm:flex-row sm:items-start justify-between gap-4 transition {{ $item->read_at ? 'bg-white hover:bg-slate-50/50' : 'bg-emerald-50/30 hover:bg-emerald-50/50' }}">
                            <div class="flex items-start space-x-4">
                                <div class="mt-1 w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $item->priority === 'high' ? 'bg-rose-100 text-rose-600' : ($item->read_at ? 'bg-slate-100 text-slate-500' : 'bg-emerald-100 text-emerald-700') }}">
                                    @if ($item->priority === 'high')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-bold text-slate-900">{{ $item->title }}</h3>
                                        @if ($item->priority === 'high')
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-700">Prioritas Tinggi</span>
                                        @endif
                                        @if (! $item->read_at)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Baru
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed">{{ $item->body }}</p>
                                    <div class="text-[11px] text-slate-400 pt-1">
                                        {{ $item->created_at->diffForHumans() }} ({{ $item->created_at->format('d M Y H:i') }})
                                    </div>
                                </div>
                            </div>
                            @if (! $item->read_at)
                                <div class="shrink-0 self-end sm:self-center">
                                    <form method="POST" action="{{ route('admin.notifications.read', $item) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-white border border-emerald-300 rounded-xl hover:bg-emerald-50 transition shadow-xs">
                                            Tandai Dibaca
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($notifications->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $notifications->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
