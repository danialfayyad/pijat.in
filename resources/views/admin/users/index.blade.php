<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Manajemen Pengguna Wilayah') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold rounded-2xl flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold rounded-2xl shadow-xs">
                <div class="font-bold mb-1">Terjadi kesalahan input:</div>
                <ul class="list-disc pl-5 space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Summary Metric Cards (1x4 Grid, Consistent with Penalties & Orders) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">{{ $activeTab === 'partners' ? 'Total Mitra' : 'Total Pelanggan' }}</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($stats['total']) }}</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Aktif / Terverifikasi</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($stats['verified']) }}</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Ditangguhkan</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($stats['suspended']) }}</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Diblokir (Blacklist)</span>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($stats['blocked']) }}</div>
            </div>
        </div>

        <!-- Role Toggle Pills (Single Clean Row, Non-duplicated) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-0.5 scrollbar-none">
            <a href="{{ route('admin.users.index', ['tab' => 'partners', 'region_id' => $selectedRegionId, 'status' => $statusFilter]) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'partners' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Mitra Terapis
            </a>

            <a href="{{ route('admin.users.index', ['tab' => 'customers', 'region_id' => $selectedRegionId, 'status' => $statusFilter]) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'customers' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                Pelanggan / Customer
            </a>

            <a href="{{ route('admin.users.index', ['tab' => 'deletion_requests', 'region_id' => $selectedRegionId]) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap inline-flex items-center gap-2 {{ $activeTab === 'deletion_requests' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                <span>Pengajuan Hapus Akun</span>
                @if (($pendingDeletionCount ?? 0) > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $activeTab === 'deletion_requests' ? 'bg-white text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                        {{ $pendingDeletionCount }}
                    </span>
                @endif
            </a>
        </div>

        <!-- Master Table Card (Consistent Layout) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <!-- Header Controls: Title & Integrated Dropdown Filters -->
            <div class="p-5 border-b border-slate-100 space-y-3.5">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-base">
                        {{ $activeTab === 'partners' ? 'Daftar Mitra Terapis' : ($activeTab === 'deletion_requests' ? 'Pengajuan Penghapusan Akun' : 'Daftar Pelanggan') }}
                    </h3>
                    <span class="text-xs text-slate-400 font-medium">
                        {{ number_format($users->total()) }} {{ $activeTab === 'deletion_requests' ? 'pengajuan' : 'akun terdata' }}
                    </span>
                </div>

                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">

                    <!-- Search Input -->
                    <div class="relative w-56 sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}" 
                               placeholder="Cari nama, email, no HP..." 
                               class="w-full text-xs border-slate-200 rounded-xl pl-9 pr-3.5 py-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs placeholder:text-xs placeholder-slate-400">
                    </div>

                    <!-- Multi-Wilayah Filter Dropdown -->
                    @if ($assignedRegions->count() > 1 || Auth::user()->isSuperadmin())
                        <div class="flex items-center space-x-1.5 shrink-0">
                            <label for="region_filter" class="text-xs font-semibold text-slate-600 shrink-0">Wilayah:</label>
                            <select id="region_filter" name="region_id" onchange="this.form.submit()"
                                    class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                                <option value="">Semua Wilayah Tugas ({{ $assignedRegions->count() }})</option>
                                @foreach ($assignedRegions as $region)
                                    <option value="{{ $region->id }}" @selected($selectedRegionId == $region->id)>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Status Filter Dropdown (Integrated in Table Header) -->
                    <div class="flex items-center space-x-1.5 shrink-0">
                        <label for="status_filter" class="text-xs font-semibold text-slate-600 shrink-0">Status:</label>
                        <select id="status_filter" name="status" onchange="this.form.submit()"
                                class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                            <option value="semua">Semua Status</option>
                            <option value="terverifikasi" @selected($statusFilter === 'terverifikasi')>Aktif / Terverifikasi</option>
                            <option value="ditangguhkan" @selected($statusFilter === 'ditangguhkan')>Ditangguhkan (Suspend)</option>
                            <option value="diblokir" @selected($statusFilter === 'diblokir')>Diblokir (Banned)</option>
                            @if ($activeTab === 'partners')
                                <option value="menunggu_verifikasi" @selected($statusFilter === 'menunggu_verifikasi')>Menunggu Verifikasi</option>
                            @endif
                        </select>
                    </div>

                    @if ($search !== '' || $selectedRegionId || $statusFilter !== 'semua')
                        <a href="{{ route('admin.users.index', ['tab' => $activeTab]) }}" 
                           class="text-xs font-semibold text-slate-500 hover:text-slate-700 hover:underline">
                            Reset Filter
                        </a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            @if ($activeTab === 'deletion_requests')
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3">Pengguna</th>
                                <th class="px-5 py-3">Kontak</th>
                                <th class="px-5 py-3">Alasan Pengajuan</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($deletionRequests as $req)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-3.5 font-semibold text-slate-800">
                                        <div>{{ $req->user?->name ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-400 font-normal">{{ $req->user?->role?->label() ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-mono text-slate-700 text-xs">{{ $req->user?->phone ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $req->user?->email ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 max-w-xs">
                                        <p class="text-xs text-slate-600 leading-relaxed">{{ $req->reason ?: '-' }}</p>
                                        @if ($req->rejection_reason)
                                            <p class="text-[11px] text-rose-600 mt-1">Alasan tolak: {{ $req->rejection_reason }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-slate-500">
                                        {{ $req->created_at?->translatedFormat('d M Y, H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        @if ($req->status === \App\Models\AccountDeletionRequest::STATUS_PENDING)
                                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <span>Menunggu</span>
                                            </span>
                                        @elseif ($req->status === \App\Models\AccountDeletionRequest::STATUS_APPROVED)
                                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Disetujui</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <span>Ditolak</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        @if ($req->status === \App\Models\AccountDeletionRequest::STATUS_PENDING)
                                            <div class="inline-flex items-center gap-2" x-data="{ showReject: false }">
                                                <form method="POST" action="{{ route('admin.users.deletion-requests.approve', $req) }}" onsubmit="return confirm('Setujui penghapusan akun {{ addslashes($req->user?->name ?? '') }} secara permanen?')">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition cursor-pointer">
                                                        Setujui &amp; Hapus
                                                    </button>
                                                </form>
                                                <button type="button" @click="showReject = !showReject" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer">
                                                    Tolak
                                                </button>
                                                <div x-show="showReject" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 text-left">
                                                    <form method="POST" action="{{ route('admin.users.deletion-requests.reject', $req) }}" @click.away="showReject = false" class="bg-white rounded-2xl max-w-sm w-full p-5 space-y-3 shadow-xl border border-slate-200">
                                                        @csrf
                                                        <h4 class="text-sm font-bold text-slate-900">Tolak Pengajuan Hapus Akun</h4>
                                                        <textarea name="rejection_reason" rows="3" required placeholder="Masukkan alasan penolakan..." class="w-full text-xs rounded-xl border border-slate-200 p-3"></textarea>
                                                        <div class="flex justify-end gap-2">
                                                            <button type="button" @click="showReject = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold cursor-pointer">Batal</button>
                                                            <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold cursor-pointer">Kirim Penolakan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-[11px] text-slate-400">Selesai diproses</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-slate-400 text-xs">
                                        Belum ada pengajuan penghapusan akun.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Nama Pengguna</th>
                            <th class="px-5 py-3">Kontak & Identitas</th>
                            <th class="px-5 py-3">Wilayah Domisili</th>
                            @if ($activeTab === 'partners')
                                <th class="px-5 py-3">Performa Rating</th>
                                <th class="px-5 py-3">Poin Sanksi</th>
                            @else
                                <th class="px-5 py-3">Alamat Utama</th>
                            @endif
                            <th class="px-5 py-3">Status Akun</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $userItem)
                            @php
                                $regionName = $activeTab === 'partners' 
                                    ? ($userItem->partnerProfile?->region?->name ?? $userItem->region?->name ?? 'Belum terdata')
                                    : ($userItem->primaryAddress?->region?->name ?? $userItem->region?->name ?? 'Belum ada alamat');

                                $st = $userItem->status?->value ?? (string) $userItem->status;
                                if ($st === 'terverifikasi') {
                                    $dotClass = 'bg-emerald-500';
                                    $textClass = 'text-emerald-700';
                                    $label = 'Aktif';
                                } elseif ($st === 'ditangguhkan') {
                                    $dotClass = 'bg-amber-500';
                                    $textClass = 'text-amber-700';
                                    $label = 'Ditangguhkan';
                                } elseif ($st === 'diblokir') {
                                    $dotClass = 'bg-rose-500';
                                    $textClass = 'text-rose-700';
                                    $label = 'Diblokir';
                                } elseif ($st === 'menunggu_verifikasi') {
                                    $dotClass = 'bg-sky-500';
                                    $textClass = 'text-sky-700';
                                    $label = 'Verifikasi';
                                } else {
                                    $dotClass = 'bg-slate-400';
                                    $textClass = 'text-slate-600';
                                    $label = ucfirst(str_replace('_', ' ', $st));
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- Nama Pengguna (Clean, without awkward gender pills) -->
                                <td class="px-5 py-3.5 font-semibold text-slate-800">
                                    {{ $userItem->name }}
                                </td>

                                <!-- Kontak & Identitas -->
                                <td class="px-5 py-3.5">
                                    <div class="font-mono text-slate-700 text-xs">{{ $userItem->phone ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $userItem->email }}</div>
                                </td>

                                <!-- Wilayah Domisili -->
                                <td class="px-5 py-3.5 text-slate-600 font-medium whitespace-nowrap">
                                    {{ $regionName }}
                                </td>

                                @if ($activeTab === 'partners')
                                    <!-- Performa Rating -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        @php
                                            $ratingVal = $userItem->partnerRating();
                                            $ratingTotal = $userItem->partnerRatingsCount();
                                        @endphp
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-amber-500 font-bold">★ {{ number_format($ratingVal, 1) }}</span>
                                            <span class="text-[11px] text-slate-400">({{ $ratingTotal }} ulasan)</span>
                                        </div>
                                    </td>

                                    <!-- Poin Sanksi (Clean text, without heavy pastel bubbles) -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        @php
                                            $pts = $userItem->penalty_points;
                                        @endphp
                                        <span class="font-bold text-xs {{ $pts >= 60 ? 'text-rose-600' : ($pts >= 30 ? 'text-amber-600' : 'text-slate-700') }}">
                                            {{ $pts }} Poin
                                        </span>
                                    </td>
                                @else
                                    <!-- Alamat Utama -->
                                    <td class="px-5 py-3.5">
                                        <div class="text-xs text-slate-600 max-w-xs truncate" title="{{ $userItem->primaryAddress?->address_text ?? '-' }}">
                                            {{ $userItem->primaryAddress?->address_text ?? 'Belum ada alamat tersimpan' }}
                                        </div>
                                    </td>
                                @endif

                                <!-- Status Akun (Subtle dot indicator, without chunky background) -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $textClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                        <span>{{ $label }}</span>
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    @if ($activeTab === 'partners')
                                        <a href="{{ route('admin.users.partners.show', $userItem) }}" 
                                           class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1">
                                            <span>Lihat Profil</span>
                                            <span>&rarr;</span>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.users.customers.show', $userItem) }}" 
                                           class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1">
                                            <span>Lihat Detail</span>
                                            <span>&rarr;</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $activeTab === 'partners' ? 6 : 5 }}" class="px-5 py-10 text-center text-slate-400 text-xs">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    Tidak ada data pengguna ditemukan di wilayah tugas Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Pagination -->
            @if ($users->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
