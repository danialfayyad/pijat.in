<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    {{ __('Dashboard Operasional Wilayah') }}
                </h2>
            </div>

            <!-- ADM-15: Region Selector Dropdown bagi Admin dengan Multi-Wilayah Tugas -->
            <div class="flex items-center space-x-2">
                <span class="text-xs text-slate-500 font-medium">Wilayah:</span>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="inline-block">
                    <select id="region_selector" name="region_id" onchange="this.form.submit()"
                        class="rounded-xl border-slate-200 bg-white text-slate-800 font-semibold text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500 py-1.5 pl-3 pr-8">
                        <option value="">Semua Wilayah Tugas ({{ $assignedRegions->count() }})</option>
                        @foreach ($assignedRegions as $r)
                            <option value="{{ $r->id }}" {{ (request('region_id') == $r->id || (isset($selectedRegion) && $selectedRegion?->id == $r->id)) ? 'selected' : '' }}>
                                {{ $r->name }} ({{ strtoupper($r->status) }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8 px-6 sm:px-8 lg:px-10 space-y-6">

        <!-- Active Region Notice (Compact) -->
        <div class="p-4 bg-emerald-950 text-white rounded-2xl shadow-xs flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-800 flex items-center justify-center text-emerald-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">
                        @if ($selectedRegion)
                            Wilayah: {{ $selectedRegion->name }} ({{ $selectedRegion->timezone }})
                        @else
                            Seluruh Wilayah Tugas: {{ $assignedRegions->pluck('name')->implode(', ') }}
                        @endif
                    </h3>
                    <p class="text-[11px] text-emerald-300/80">Cakupan Wilayah Tugas Aktif</p>
                </div>
            </div>
            <span class="text-xs bg-emerald-900/80 text-emerald-300 border border-emerald-700/60 px-2.5 py-1 rounded-lg font-medium">
                Siaga Darurat Aktif
            </span>
        </div>

        <!-- ADM-01: Kartu Metrik Operasional Wilayah -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Mitra Online -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Mitra Online</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $metrics['active_partners_count'] }}</div>
                <div class="text-[11px] text-emerald-600 font-medium">Siap Terima Order</div>
            </div>

            <!-- Pesanan Berjalan -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Pesanan Aktif</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $metrics['orders_in_progress'] }}</div>
                <div class="text-[11px] text-slate-400">Sedang berlangsung</div>
            </div>

            <!-- Pesanan Macet -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Pesanan Macet</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $metrics['stuck_orders_count'] }}</div>
                <div class="text-[11px] text-amber-600 font-medium">Perlu perhatian</div>
            </div>

            <!-- Aduan Baru -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Aduan 24 Jam</span>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $metrics['new_complaints_count'] }}</div>
                <div class="text-[11px] text-slate-400">Menunggu respon</div>
            </div>
        </div>

        <!-- Tabel Detail Wilayah Tugas Anda -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-base font-bold text-slate-800">Daftar Wilayah Tugas Anda</h3>
                <span class="text-xs text-slate-500">Total: {{ $assignedRegions->count() }} Wilayah</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-slate-700 uppercase font-semibold text-[11px] border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5">Nama Wilayah</th>
                            <th class="px-5 py-3.5">Zona Waktu</th>
                            <th class="px-5 py-3.5">Status Wilayah</th>
                            <th class="px-5 py-3.5 text-right">Tugas Darurat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($assignedRegions as $r)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 font-semibold text-slate-800">
                                    {{ $r->name }}
                                </td>
                                <td class="px-5 py-3.5 font-mono text-[11px] text-slate-500">
                                    {{ $r->timezone }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($r->status === 'aktif')
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-emerald-700 font-medium">✓ Siaga Darurat</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
