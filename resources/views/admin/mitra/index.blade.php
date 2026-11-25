<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-bold text-slate-800">
            {{ __('Verifikasi Kemitraan') }}
        </h2>
    </x-slot>

    <div x-data="{
        activeTab: '{{ $tab }}',
        rejectModal: false,
        approveModal: false,
        selectedRelocation: null,
        openRejectModal(relocation) {
            this.selectedRelocation = relocation;
            this.rejectModal = true;
        },
        openApproveModal(relocation) {
            this.selectedRelocation = relocation;
            this.approveModal = true;
        }
    }" class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

        @if (session('status'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Filter & Navigation Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <!-- Tabs -->
            <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/70 text-xs font-semibold">
                <a href="{{ route('admin.mitra.index', array_filter(['tab' => 'pendaftaran', 'region_id' => $selectedRegionId])) }}"
                   class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $tab !== 'relokasi' ? 'bg-white text-slate-800 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                    <span>Berkas Baru</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $pendingPartners->count() > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-200/70 text-slate-600' }}">
                        {{ $pendingPartners->count() }}
                    </span>
                </a>

                <a href="{{ route('admin.mitra.index', array_filter(['tab' => 'relokasi', 'region_id' => $selectedRegionId])) }}"
                   class="px-3.5 py-1.5 rounded-lg transition flex items-center gap-1.5 {{ $tab === 'relokasi' ? 'bg-white text-slate-800 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                    <span>Permohonan Relokasi</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $pendingRelocations->count() > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200/70 text-slate-600' }}">
                        {{ $pendingRelocations->count() }}
                    </span>
                </a>
            </div>

            <!-- Region Filter -->
            @if ($assignedRegions->count() > 1)
                <div class="flex items-center gap-2">
                    <form method="GET" action="{{ route('admin.mitra.index') }}" class="inline-block">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <select name="region_id" onchange="this.form.submit()"
                            class="text-xs font-medium border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-2xs py-1.5 pl-3 pr-8 cursor-pointer text-slate-700">
                            <option value="">Semua Wilayah Tugas ({{ $assignedRegions->count() }})</option>
                            @foreach ($assignedRegions as $r)
                                <option value="{{ $r->id }}" @selected($selectedRegionId == $r->id)>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endif
        </div>

        @if ($tab === 'relokasi')
            <!-- TAB 2: Permohonan Relokasi Antarwilayah -->
            <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-slate-800 text-sm">Permohonan Relokasi</h3>
                        <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                            {{ $pendingRelocations->count() }} permohonan
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50/70 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Mitra</th>
                                <th class="px-4 py-3 font-semibold">Perpindahan Wilayah</th>
                                <th class="px-4 py-3 font-semibold">Alamat Baru</th>
                                <th class="px-4 py-3 font-semibold">Waktu</th>
                                <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($pendingRelocations as $req)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-slate-900 text-xs">{{ $req->partner->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $req->partner->phone }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5 text-xs">
                                            <span class="font-medium text-slate-600">{{ $req->fromRegion->name }}</span>
                                            <span class="text-slate-300">&rarr;</span>
                                            <span class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60">{{ $req->toRegion->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 max-w-sm">
                                        <div class="truncate text-xs text-slate-700 font-medium" title="{{ $req->address_text }}">
                                            {{ $req->address_text }}
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5 text-[11px]">
                                            @if ($req->latitude && $req->longitude)
                                                <a href="https://www.google.com/maps?q={{ $req->latitude }},{{ $req->longitude }}" target="_blank" rel="noopener noreferrer"
                                                   class="text-emerald-700 hover:text-emerald-800 hover:underline font-medium inline-flex items-center gap-0.5">
                                                    <span>Lihat Peta</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                            @endif
                                            @if ($req->notes)
                                                <span class="text-slate-300">•</span>
                                                <span class="text-slate-500 truncate" title="{{ $req->notes }}">Catatan: {{ Str::limit($req->notes, 35) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-[11px] text-slate-500">
                                        {{ $req->created_at->locale('id')->diffForHumans() }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button"
                                                    @click="openApproveModal({
                                                        id: {{ $req->id }},
                                                        name: '{{ addslashes($req->partner->name) }}',
                                                        phone: '{{ addslashes($req->partner->phone) }}',
                                                        from: '{{ addslashes($req->fromRegion->name) }}',
                                                        to: '{{ addslashes($req->toRegion->name) }}',
                                                        address: '{{ addslashes($req->address_text) }}',
                                                        notes: '{{ addslashes($req->notes ?? '') }}',
                                                        lat: '{{ $req->latitude }}',
                                                        lng: '{{ $req->longitude }}'
                                                    })"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-2xs cursor-pointer">
                                                Setujui
                                            </button>

                                            <button type="button"
                                                    @click="openRejectModal({
                                                        id: {{ $req->id }},
                                                        name: '{{ addslashes($req->partner->name) }}',
                                                        region: '{{ addslashes($req->toRegion->name) }}'
                                                    })"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-slate-200 hover:border-rose-300 text-slate-700 hover:text-rose-700 transition shadow-2xs cursor-pointer">
                                                Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-slate-400 text-xs">
                                        Tidak ada permohonan relokasi ke wilayah tugas Anda saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Setujui Relokasi -->
            <div x-show="approveModal"
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
                 style="display: none;">
                <div class="bg-white rounded-2xl max-w-md w-full p-5 space-y-4 shadow-xl border border-slate-200"
                     @click.away="approveModal = false"
                     @keydown.escape.window="approveModal = false">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-800">Setujui Permohonan Relokasi</h3>
                        <button type="button" @click="approveModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <template x-if="selectedRelocation">
                        <form :action="'/admin/mitra/relokasi/' + selectedRelocation.id + '/approve'" method="POST" class="space-y-3.5 text-xs">
                            @csrf
                            <div class="p-3 bg-slate-50 rounded-xl space-y-2 border border-slate-200/70">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Mitra:</span>
                                    <span class="font-bold text-slate-900" x-text="selectedRelocation.name"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Rute:</span>
                                    <div class="font-semibold">
                                        <span class="text-slate-700" x-text="selectedRelocation.from"></span>
                                        <span class="text-slate-400 mx-1">&rarr;</span>
                                        <span class="text-emerald-700 font-bold" x-text="selectedRelocation.to"></span>
                                    </div>
                                </div>
                                <div class="pt-1 border-t border-slate-200/60">
                                    <span class="text-slate-500 block mb-0.5">Alamat Tujuan:</span>
                                    <p class="text-slate-800 font-medium leading-relaxed" x-text="selectedRelocation.address"></p>
                                </div>
                                <template x-if="selectedRelocation.notes">
                                    <div class="pt-1 border-t border-slate-200/60">
                                        <span class="text-slate-500 block mb-0.5">Catatan Mitra:</span>
                                        <p class="text-slate-700" x-text="selectedRelocation.notes"></p>
                                    </div>
                                </template>
                            </div>

                            <p class="text-xs text-slate-500 leading-relaxed">
                                Mitra akan langsung dipindahkan ke wilayah tugas Anda dengan seluruh riwayat pesanan tetap terjaga.
                            </p>

                            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                                <button type="button" @click="approveModal = false" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-xs cursor-pointer">
                                    Setujui Relokasi
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>

            <!-- Modal Tolak Relokasi -->
            <div x-show="rejectModal"
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
                 style="display: none;">
                <div class="bg-white rounded-2xl max-w-md w-full p-5 space-y-4 shadow-xl border border-slate-200"
                     @click.away="rejectModal = false"
                     @keydown.escape.window="rejectModal = false">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-800">Tolak Permohonan Relokasi</h3>
                        <button type="button" @click="rejectModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <template x-if="selectedRelocation">
                        <form :action="'/admin/mitra/relokasi/' + selectedRelocation.id + '/reject'" method="POST" class="space-y-3.5 text-xs">
                            @csrf
                            <div class="p-3 bg-rose-50/60 rounded-xl space-y-1.5 border border-rose-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Mitra:</span>
                                    <span class="font-bold text-slate-900" x-text="selectedRelocation.name"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Tujuan Wilayah:</span>
                                    <span class="font-bold text-slate-900" x-text="selectedRelocation.region"></span>
                                </div>
                            </div>

                            <div>
                                <label for="rejection_reason" class="font-semibold text-slate-700 block mb-1">
                                    Alasan Penolakan <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                                          placeholder="Tuliskan alasan penolakan..."
                                          class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition leading-relaxed"></textarea>
                            </div>

                            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                                <button type="button" @click="rejectModal = false" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold transition shadow-xs cursor-pointer">
                                    Konfirmasi Penolakan
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>

        @else
            <!-- TAB 1: Pendaftaran Berkas Baru -->
            <div class="bg-white rounded-2xl shadow-2xs border border-slate-200/80 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-slate-800 text-sm">Menunggu Validasi Dokumen</h3>
                        <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                            {{ $pendingPartners->count() }} permohonan
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50/70 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama Pemohon</th>
                                <th class="px-4 py-3 font-semibold">Kontak & NIK</th>
                                <th class="px-4 py-3 font-semibold">Wilayah</th>
                                <th class="px-4 py-3 font-semibold">Rekening Bank</th>
                                <th class="px-4 py-3 font-semibold">Waktu</th>
                                <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($pendingPartners as $mitra)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-5 py-3.5 font-bold text-slate-900">
                                        {{ $mitra->name }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="font-mono text-slate-700">{{ $mitra->phone }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5">NIK: {{ $mitra->nik ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-700 font-medium whitespace-nowrap">
                                        {{ $mitra->partnerProfile?->region?->name ?? 'Wilayah Tidak Ditemukan' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-xs whitespace-nowrap">
                                        <span class="font-medium text-slate-800">{{ $mitra->primaryBankAccount?->bank_name ?? '-' }}</span>
                                        <span class="text-slate-400 font-mono text-[11px] block">{{ $mitra->primaryBankAccount?->account_number }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-[11px] text-slate-500">
                                        {{ $mitra->updated_at->locale('id')->diffForHumans() }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.mitra.show', $mitra) }}" 
                                            class="px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-2xs inline-flex items-center gap-1 cursor-pointer">
                                            <span>Periksa</span>
                                            <span>&rarr;</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-slate-400 text-xs">
                                        Tidak ada antrean verifikasi berkas mitra saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
