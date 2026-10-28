<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800 leading-tight">
            {{ __('Antrean Verifikasi Berkas Kemitraan') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-2.5">
                        <h3 class="font-bold text-slate-800 text-base">Menunggu Validasi Dokumen</h3>
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                            {{ $pendingPartners->count() }} Permohonan
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Target SLA Peninjauan: 1 x 24 jam sejak pengajuan.</p>
                </div>

                <!-- Filter Multi-Wilayah Tugas Admin (ADM-15) -->
                @if ($assignedRegions->count() > 1)
                    <div class="flex items-center space-x-2 shrink-0">
                        <label for="region_filter" class="text-xs font-semibold text-slate-600 shrink-0">Filter Wilayah:</label>
                        <form method="GET" action="{{ route('admin.mitra.index') }}" class="inline-block">
                            <select id="region_filter" name="region_id" onchange="this.form.submit()"
                                class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-1.5 pl-3 pr-8 cursor-pointer">
                                <option value="">Semua Wilayah Tugas ({{ $assignedRegions->count() }})</option>
                                @foreach ($assignedRegions as $r)
                                    <option value="{{ $r->id }}" @selected($selectedRegionId == $r->id)>{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Nama Pemohon</th>
                            <th class="px-5 py-3">NIK & Kontak</th>
                            <th class="px-5 py-3">Wilayah Domisili</th>
                            <th class="px-5 py-3">Rekening Bank</th>
                            <th class="px-5 py-3">Waktu Pengajuan</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pendingPartners as $mitra)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 font-semibold text-slate-800">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs uppercase">
                                            {{ substr($mitra->name, 0, 1) }}
                                        </div>
                                        <span>{{ $mitra->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-mono text-slate-700">{{ $mitra->nik ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $mitra->phone }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ $mitra->partnerProfile?->region?->name ?? 'Wilayah Tidak Ditemukan' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-[11px]">
                                    {{ $mitra->primaryBankAccount?->bank_name ?? '-' }}
                                    <span class="text-slate-400 block">{{ $mitra->primaryBankAccount?->account_number }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-400 text-[11px]">
                                    {{ $mitra->updated_at->diffForHumans() }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.mitra.show', $mitra) }}" 
                                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow-xs transition inline-flex items-center space-x-1">
                                        <span>Periksa Berkas</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-slate-400 text-xs">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Tidak ada antrean verifikasi berkas mitra saat ini di wilayah tugas Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
