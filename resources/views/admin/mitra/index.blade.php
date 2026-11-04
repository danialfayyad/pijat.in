<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800 leading-tight">
            {{ __('Verifikasi Kemitraan & Permohonan Relokasi') }}
        </h2>
    </x-slot>

    <div x-data="{
        activeTab: '{{ $tab }}',
        rejectModal: false,
        selectedRelocation: null,
        openRejectModal(relocation) {
            this.selectedRelocation = relocation;
            this.rejectModal = true;
        }
    }" class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold shadow-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Filter Multi-Wilayah Tugas & Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Segmented Control Tabs -->
            <div class="inline-flex p-1 bg-slate-100/90 rounded-2xl border border-slate-200/80">
                <a href="{{ route('admin.mitra.index', array_filter(['tab' => 'pendaftaran', 'region_id' => $selectedRegionId])) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-2 {{ $tab !== 'relokasi' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                    <span>Pendaftaran Berkas Baru</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pendingPartners->count() > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-600' }}">
                        {{ $pendingPartners->count() }}
                    </span>
                </a>

                <a href="{{ route('admin.mitra.index', array_filter(['tab' => 'relokasi', 'region_id' => $selectedRegionId])) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-2 {{ $tab === 'relokasi' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                    <span>Permohonan Relokasi Antarwilayah</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pendingRelocations->count() > 0 ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-600' }}">
                        {{ $pendingRelocations->count() }}
                    </span>
                </a>
            </div>

            <!-- Filter Multi-Wilayah Tugas Admin (ADM-15) -->
            @if ($assignedRegions->count() > 1)
                <div class="flex items-center space-x-2 shrink-0">
                    <label for="region_filter" class="text-xs font-semibold text-slate-600 shrink-0">Filter Wilayah:</label>
                    <form method="GET" action="{{ route('admin.mitra.index') }}" class="inline-block">
                        <input type="hidden" name="tab" value="{{ $tab }}">
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

        @if ($tab === 'relokasi')
            <!-- TAB 2: Permohonan Relokasi Antarwilayah (MIT-22) -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-center space-x-2.5">
                        <h3 class="font-bold text-slate-800 text-base">Permohonan Masuk ke Wilayah Penugasan Anda</h3>
                        <span class="text-xs text-slate-400 font-medium">({{ $pendingRelocations->count() }} permohonan)</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Mitra memohon perpindahan domisili dari wilayah lain ke wilayah tugas Anda. Periksa rekam jejak pesanan, rating, dan riwayat sanksi mitra sebelum memberikan persetujuan (ADM-16).
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3">Mitra Pemohon</th>
                                <th class="px-5 py-3">Asal &rarr; Tujuan</th>
                                <th class="px-5 py-3">Alamat Baru & Titik Koordinat</th>
                                <th class="px-5 py-3">Catatan / Alasan</th>
                                <th class="px-5 py-3">Waktu Pengajuan</th>
                                <th class="px-5 py-3 text-right">Aksi Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($pendingRelocations as $req)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-slate-900">{{ $req->partner->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $req->partner->phone }}</div>
                                        <div class="mt-1">
                                            <a href="{{ route('admin.users.partners.show', $req->partner) }}" target="_blank"
                                               class="inline-flex items-center text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">
                                                <span>Audit Riwayat & Sanksi (ADM-16)</span>
                                                <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center space-x-1.5 font-semibold text-slate-700">
                                            <span>{{ $req->fromRegion->name }}</span>
                                            <span class="text-slate-400">&rarr;</span>
                                            <span class="text-emerald-700 font-bold">{{ $req->toRegion->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 max-w-xs">
                                        <div class="text-slate-800 font-medium leading-relaxed">{{ $req->address_text }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                            Pin: {{ number_format($req->latitude, 5) }}, {{ number_format($req->longitude, 5) }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600 max-w-xs">
                                        <span class="text-[11px] italic">{{ $req->notes ?: 'Tidak ada catatan' }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-400 text-[11px] whitespace-nowrap">
                                        {{ $req->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-2">
                                            <!-- Tombol Setujui -->
                                            <form action="{{ route('admin.mitra.relocation.approve', $req) }}" method="POST"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menyetujui perpindahan domisili mitra {{ addslashes($req->partner->name) }} ke wilayah {{ addslashes($req->toRegion->name) }}?');">
                                                @csrf
                                                <button type="submit"
                                                        class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-2xs cursor-pointer">
                                                    Setujui
                                                </button>
                                            </form>

                                            <!-- Tombol Tolak -->
                                            <button type="button"
                                                    @click="openRejectModal({ id: {{ $req->id }}, name: '{{ addslashes($req->partner->name) }}', region: '{{ addslashes($req->toRegion->name) }}' })"
                                                    class="px-3 py-1.5 rounded-lg text-xs font-bold border border-rose-300 text-rose-700 hover:bg-rose-50 transition shadow-2xs cursor-pointer">
                                                Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-slate-400 text-xs">
                                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Tidak ada permohonan relokasi mitra yang pending ke wilayah tugas Anda saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Tolak Relokasi -->
            <div x-show="rejectModal"
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-200"
                     @click.away="rejectModal = false">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-rose-700">Tolak Permohonan Relokasi Mitra</h3>
                        <button type="button" @click="rejectModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                    </div>

                    <template x-if="selectedRelocation">
                        <form :action="'/admin/mitra/relokasi/' + selectedRelocation.id + '/reject'" method="POST" class="space-y-4 text-xs">
                            @csrf
                            <div class="p-3 bg-rose-50/60 rounded-xl space-y-1.5 border border-rose-100">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Mitra:</span>
                                    <span class="font-bold text-slate-900" x-text="selectedRelocation.name"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Tujuan Wilayah:</span>
                                    <span class="font-bold text-slate-900" x-text="selectedRelocation.region"></span>
                                </div>
                            </div>

                            <div>
                                <label for="rejection_reason" class="font-semibold text-slate-700 block mb-1">
                                    Alasan Penolakan <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                                          placeholder="Contoh: Kuota mitra untuk wilayah ini sudah penuh atau alamat berada di luar jangkauan operasional."
                                          class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition"></textarea>
                            </div>

                            <div class="pt-2 flex justify-end gap-2">
                                <button type="button" @click="rejectModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold transition shadow-xs cursor-pointer">
                                    Konfirmasi Penolakan
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>

        @else
            <!-- TAB 1: Pendaftaran Berkas Baru (Existing ADM-02) -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2.5">
                            <h3 class="font-bold text-slate-800 text-base">Menunggu Validasi Dokumen</h3>
                            <span class="text-xs text-slate-400 font-medium">({{ $pendingPartners->count() }} permohonan)</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Target SLA Peninjauan: 1 x 24 jam sejak pengajuan berkas pendaftaran.</p>
                    </div>
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
                                        {{ $mitra->name }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-mono text-slate-700 text-xs">{{ $mitra->nik ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $mitra->phone }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600 font-medium whitespace-nowrap">
                                        {{ $mitra->partnerProfile?->region?->name ?? 'Wilayah Tidak Ditemukan' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-[11px]">
                                        <span class="font-semibold text-slate-700">{{ $mitra->primaryBankAccount?->bank_name ?? '-' }}</span>
                                        <span class="text-slate-400 font-mono block">{{ $mitra->primaryBankAccount?->account_number }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-400 text-[11px] whitespace-nowrap">
                                        {{ $mitra->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.mitra.show', $mitra) }}" 
                                            class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1">
                                            <span>Periksa Berkas</span>
                                            <span>&rarr;</span>
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
        @endif

    </div>
</x-app-layout>
