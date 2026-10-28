<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard Operasional Wilayah') }}
        </h2>
    </x-slot>

    <div class="py-8 px-6 sm:px-8 lg:px-10 space-y-6">

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
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Daftar Wilayah Tugas Anda</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @if ($selectedRegion)
                            Menampilkan Wilayah: {{ $selectedRegion->name }} ({{ $selectedRegion->timezone }})
                        @else
                            Total: {{ $assignedRegions->count() }} Wilayah Tugas
                        @endif
                    </p>
                </div>

                <!-- ADM-15: Region Selector Dropdown bagi Admin dengan Multi-Wilayah Tugas -->
                <div class="flex items-center space-x-2 shrink-0">
                    <label for="region_selector" class="text-xs text-slate-500 font-semibold shrink-0">Filter Wilayah:</label>
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="inline-block">
                        <select id="region_selector" name="region_id" onchange="this.form.submit()"
                            class="rounded-xl border-slate-200 bg-white text-slate-800 font-semibold text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500 py-1.5 pl-3 pr-8 cursor-pointer">
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
                        @php
                            $displayedRegions = $selectedRegion 
                                ? $assignedRegions->where('id', $selectedRegion->id) 
                                : $assignedRegions;
                        @endphp
                        @forelse ($displayedRegions as $r)
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
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-slate-400">
                                    Tidak ada wilayah yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
