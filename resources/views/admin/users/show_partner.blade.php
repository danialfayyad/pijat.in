<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index', ['tab' => 'partners']) }}" 
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition"
               title="Kembali ke Daftar Mitra">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight flex items-center gap-2">
                    <span>Profil & Performa Mitra: {{ $user->name }}</span>
                    @php
                        $st = $user->status?->value ?? (string) $user->status;
                    @endphp
                    @if ($st === 'terverifikasi')
                        <span class="text-xs px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full">
                            Aktif / Terverifikasi
                        </span>
                    @elseif ($st === 'ditangguhkan')
                        <span class="text-xs px-2.5 py-0.5 bg-amber-100 text-amber-800 font-bold rounded-full">
                            Ditangguhkan (Suspend)
                        </span>
                    @elseif ($st === 'diblokir')
                        <span class="text-xs px-2.5 py-0.5 bg-rose-100 text-rose-800 font-bold rounded-full">
                            Diblokir (Banned)
                        </span>
                    @else
                        <span class="text-xs px-2.5 py-0.5 bg-slate-100 text-slate-700 font-bold rounded-full">
                            {{ ucfirst(str_replace('_', ' ', $st)) }}
                        </span>
                    @endif
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    ID Akun: #{{ $user->id }} • Wilayah: {{ $partnerProfile?->region?->name ?? 'Belum ditentukan' }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" 
         x-data="{ 
            suspendModalOpen: false, 
            blockModalOpen: false, 
            activateModalOpen: false 
         }">
        
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-xl flex items-center gap-2 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl shadow-2xs">
                <div class="font-bold mb-1">Terjadi kesalahan pada moderasi:</div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Quick Summary Metrics Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-amber-50 text-amber-500 rounded-xl">
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-semibold uppercase">Rating Mitra</div>
                    <div class="text-xl font-bold text-slate-800 mt-0.5">
                        {{ number_format($ratingScore, 1) }} <span class="text-xs text-slate-400 font-normal">/ 5.0 ({{ $ratingsCount }})</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-semibold uppercase">Pesanan Selesai</div>
                    <div class="text-xl font-bold text-slate-800 mt-0.5">
                        {{ number_format($completedOrdersCount) }} <span class="text-xs text-slate-400 font-normal">layanan</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="p-3 {{ $user->penalty_points > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-600' }} rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-semibold uppercase">Poin Sanksi</div>
                    <div class="text-xl font-bold {{ $user->penalty_points >= 30 ? 'text-rose-600' : 'text-slate-800' }} mt-0.5">
                        {{ $user->penalty_points }} <span class="text-xs text-slate-400 font-normal">poin</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-sky-50 text-sky-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-semibold uppercase">Status Kemitraan</div>
                    <div class="text-sm font-bold text-slate-800 mt-1">
                        {{ $partnerProfile?->is_active ? 'Online / Siap Melayani' : 'Offline' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Biodata & Legal Documents -->
            <div class="space-y-6">
                <!-- Biodata Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-lg shadow-2xs">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-slate-800">{{ $user->name }}</h3>
                            <p class="text-xs text-slate-500">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Nomor Telepon (WhatsApp)</span>
                            <span class="font-bold text-slate-700">{{ $user->phone ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Jenis Kelamin</span>
                            <span class="font-bold text-slate-700 uppercase">{{ $user->gender ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Tanggal Lahir</span>
                            <span class="font-bold text-slate-700">{{ $user->date_of_birth ? $user->date_of_birth->format('d F Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Nomor Induk Kependudukan (NIK)</span>
                            <span class="font-mono font-bold text-slate-700">
                                @if ($user->nik)
                                    •••• •••• •••• {{ substr($user->nik, -4) }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Wilayah Operasional</span>
                            <span class="font-bold text-emerald-700">{{ $partnerProfile?->region?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-400">Tanggal Bergabung</span>
                            <span class="font-semibold text-slate-700">{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                    </div>

                    @if ($partnerProfile?->address_text)
                        <div class="pt-3 border-t border-slate-100">
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Alamat Domisili Mitra</span>
                            <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                {{ $partnerProfile->address_text }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Bank Account Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-3">
                    <h4 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Rekening Pencairan Dana
                    </h4>
                    @if ($bankAccount)
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 space-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Bank</span>
                                <span class="font-bold text-slate-800">{{ $bankAccount->bank_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Nomor Rekening</span>
                                <span class="font-mono font-bold text-slate-800">{{ $bankAccount->account_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Atas Nama</span>
                                <span class="font-semibold text-slate-700">{{ $bankAccount->account_holder_name }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">Belum mendaftarkan rekening bank.</p>
                    @endif
                </div>

                <!-- Moderation Action Panel -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <h4 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Tindakan Moderasi Akun
                    </h4>

                    @if ($user->status_reason)
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Catatan Tindakan Terakhir:</span>
                            <p class="text-slate-700 italic">"{{ $user->status_reason }}"</p>
                            @if ($user->suspended_until)
                                <span class="text-[10px] text-amber-600 block font-semibold">
                                    Ditangguhkan s.d. {{ $user->suspended_until->format('d M Y H:i') }}
                                </span>
                            @endif
                        </div>
                    @endif

                    <div class="space-y-2 pt-1">
                        @if ($user->status === \App\Enums\UserStatus::Ditangguhkan || $user->status === \App\Enums\UserStatus::Diblokir)
                            <button type="button" 
                                    @click="activateModalOpen = true"
                                    class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-2xs flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Aktifkan Kembali Akun
                            </button>
                        @endif

                        @if ($user->status !== \App\Enums\UserStatus::Ditangguhkan && $user->status !== \App\Enums\UserStatus::Diblokir)
                            <button type="button" 
                                    @click="suspendModalOpen = true"
                                    class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition shadow-2xs flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Tangguhkan Akun (Suspend)
                            </button>
                        @endif

                        @if ($user->status !== \App\Enums\UserStatus::Diblokir)
                            <button type="button" 
                                    @click="blockModalOpen = true"
                                    class="w-full py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-2xs flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                Blokir Akun Permanen (Blacklist)
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Ratings Breakdown & Reviews (Consolidated ADM-09!) & Demerits -->
            <div class="lg:col-span-2 space-y-6">
                <!-- RATING & REVIEWS CARD (CONSOLIDATED FEATURE) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-6 border-b border-slate-200/80 bg-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-500 fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    Metrik Rating & Ulasan Pelanggan
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Distribusi penilaian kepuasan pelanggan secara transparan dan riwayat testimoni pesanan.
                                </p>
                            </div>
                            <span class="px-3 py-1 bg-amber-50 text-amber-800 text-xs font-bold rounded-xl border border-amber-200">
                                {{ $ratingsCount }} Total Ulasan
                            </span>
                        </div>

                        <!-- Rating Score & Star Bar Breakdown -->
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-6 items-center bg-slate-50/70 p-5 rounded-2xl border border-slate-100">
                            <!-- Overall Score Box -->
                            <div class="text-center sm:border-r sm:border-slate-200 sm:pr-4">
                                <div class="text-4xl font-extrabold text-slate-800 tracking-tight">
                                    {{ number_format($ratingScore, 2) }}
                                </div>
                                <div class="flex items-center justify-center gap-1 my-2 text-amber-400">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= round($ratingScore) ? 'fill-current' : 'text-slate-300' }}" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-xs text-slate-500 font-medium">Berdasarkan {{ $ratingsCount }} pesanan</span>
                            </div>

                            <!-- Star Breakdown Bars -->
                            <div class="sm:col-span-2 space-y-2">
                                @foreach ([5, 4, 3, 2, 1] as $star)
                                    @php
                                        $countStar = $distribution[$star] ?? 0;
                                        $pct = $ratingsCount > 0 ? round(($countStar / $ratingsCount) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center gap-3 text-xs">
                                        <span class="w-12 font-bold text-slate-600 shrink-0 flex items-center gap-1">
                                            {{ $star }}
                                            <svg class="w-3 h-3 text-amber-400 fill-current" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </span>
                                        <div class="flex-1 bg-slate-200/70 rounded-full h-2 overflow-hidden">
                                            <div class="bg-amber-400 h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="w-14 text-right text-slate-500 font-medium shrink-0">
                                            {{ $countStar }} <span class="text-[10px] text-slate-400">({{ $pct }}%)</span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Reviews List -->
                    <div class="p-6">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-4">
                            Ulasan & Komentar Pelanggan Terbaru
                        </h4>

                        <div class="space-y-4">
                            @forelse ($reviews as $rev)
                                <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs">
                                                {{ strtoupper(substr($rev->fromUser?->name ?? 'P', 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="text-xs font-bold text-slate-800">{{ $rev->fromUser?->name ?? 'Pelanggan' }}</span>
                                                <span class="text-[10px] text-slate-400 block">{{ $rev->created_at->format('d M Y, H:i') }}</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1 text-amber-400 bg-white px-2 py-1 rounded-lg border border-slate-200 shadow-2xs">
                                            @for ($s = 1; $s <= 5; $s++)
                                                <svg class="w-3.5 h-3.5 {{ $s <= $rev->score ? 'fill-current' : 'text-slate-200' }}" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                            <span class="text-xs font-bold text-slate-700 ml-1">{{ $rev->score }}</span>
                                        </div>
                                    </div>

                                    @if ($rev->review)
                                        <p class="text-xs text-slate-700 leading-relaxed bg-white p-3 rounded-lg border border-slate-100">
                                            "{{ $rev->review }}"
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-400 italic">Pelanggan tidak meninggalkan catatan ulasan teks.</p>
                                    @endif

                                    @if ($rev->order)
                                        <div class="text-[11px] text-slate-400 flex items-center gap-1.5 pt-1">
                                            <span>Kode Pesanan:</span>
                                            <a href="{{ route('admin.orders.show', $rev->order) }}" class="font-mono text-emerald-700 font-semibold hover:underline">
                                                #{{ $rev->order->order_code }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="py-8 text-center text-slate-400">
                                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                    </svg>
                                    <p class="text-xs">Belum ada ulasan rating untuk mitra ini.</p>
                                </div>
                            @endforelse
                        </div>

                        @if ($reviews->hasPages())
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                {{ $reviews->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Demerit Points & Sanctions History Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Rekam Jejak Poin Sanksi & Kepatuhan
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Sistem akumulasi poin pelanggaran dan penebusan performa bintang 5 ([DEC-12]).
                            </p>
                        </div>
                        <a href="{{ route('admin.penalties.show', $user) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline">
                            Kelola Poin Penuh &rarr;
                        </a>
                    </div>

                    <!-- Threshold Meter -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-slate-700">Akumulasi Poin Saat Ini:</span>
                            <span class="font-bold text-base {{ $user->penalty_points >= 30 ? 'text-rose-600' : 'text-slate-800' }}">
                                {{ $user->penalty_points }} Poin
                            </span>
                        </div>
                        <div class="grid grid-cols-4 gap-1 text-[10px] text-center pt-2">
                            <div class="p-1.5 rounded {{ $user->penalty_points >= 30 ? 'bg-amber-100 font-bold text-amber-800' : 'bg-slate-200/60 text-slate-500' }}">
                                30: Peringatan
                            </div>
                            <div class="p-1.5 rounded {{ $user->penalty_points >= 60 ? 'bg-orange-100 font-bold text-orange-800' : 'bg-slate-200/60 text-slate-500' }}">
                                60: Suspend 3H
                            </div>
                            <div class="p-1.5 rounded {{ $user->penalty_points >= 100 ? 'bg-rose-100 font-bold text-rose-800' : 'bg-slate-200/60 text-slate-500' }}">
                                100: Suspend 7H
                            </div>
                            <div class="p-1.5 rounded {{ $user->penalty_points >= 150 ? 'bg-purple-100 font-bold text-purple-800' : 'bg-slate-200/60 text-slate-500' }}">
                                150: Banned
                            </div>
                        </div>
                    </div>

                    @if ($user->penaltyLedgers->isNotEmpty())
                        <div class="space-y-2 pt-2">
                            <span class="text-xs font-bold text-slate-700 block">Mutasi Poin Terbaru:</span>
                            @foreach ($user->penaltyLedgers->take(3) as $ledger)
                                <div class="flex items-center justify-between text-xs p-2.5 rounded-lg border border-slate-100 bg-white">
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $ledger->reason }}</p>
                                        <span class="text-[10px] text-slate-400">{{ $ledger->created_at->format('d M Y H:i') }}</span>
                                    </div>
                                    <span class="font-bold {{ $ledger->points > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $ledger->points > 0 ? '+' : '' }}{{ $ledger->points }} pts
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- MODAL: ACTIVATE ACCOUNT -->
        <div x-show="activateModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">
                        <span class="p-1.5 bg-emerald-100 text-emerald-800 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        Aktifkan Kembali Akun
                    </h3>
                    <button type="button" @click="activateModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="activate">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pengaktifan Akun <span class="text-rose-500">*</span></label>
                        <textarea name="reason" 
                                  rows="3" 
                                  required 
                                  placeholder="Contoh: Masa penangguhan telah selesai / banding disetujui / verifikasi ulang valid."
                                  class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="activateModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition">
                            Aktifkan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: SUSPEND ACCOUNT -->
        <div x-show="suspendModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">
                        <span class="p-1.5 bg-amber-100 text-amber-800 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </span>
                        Tangguhkan Akun (Suspend)
                    </h3>
                    <button type="button" @click="suspendModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="suspend">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Durasi Penangguhan (Hari) <span class="text-rose-500">*</span></label>
                        <select name="suspend_days" required class="w-full text-xs rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-amber-500">
                            <option value="3">3 Hari (Pelanggaran Ringan/Sedang)</option>
                            <option value="7">7 Hari (Pelanggaran Berulang)</option>
                            <option value="14">14 Hari (Investigasi Lanjutan)</option>
                            <option value="30">30 Hari (Sanksi Berat Sementara)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penangguhan <span class="text-rose-500">*</span></label>
                        <textarea name="reason" 
                                  rows="3" 
                                  required 
                                  placeholder="Jelaskan alasan pelanggaran SOP atau keluhan pelanggan yang mendasari suspend."
                                  class="w-full text-xs rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-amber-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="suspendModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition">
                            Terapkan Suspend
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: BLOCK ACCOUNT (BANNED & BLACKLIST) -->
        <div x-show="blockModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-base text-rose-800 flex items-center gap-2">
                        <span class="p-1.5 bg-rose-100 text-rose-800 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </span>
                        Blokir Akun Permanen (Blacklist)
                    </h3>
                    <button type="button" @click="blockModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 leading-relaxed">
                    <span class="font-bold block mb-0.5">Peringatan Tindakan Keras:</span>
                    Akun ini akan diblokir permanen. Nomor HP <strong>({{ $user->phone ?? '-' }})</strong> dan identitas perangkat akan dimasukkan ke <strong>Daftar Hitam (Blacklist)</strong> sistem sehingga tidak dapat login kembali.
                </div>

                <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="block">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pemblokiran Permanen <span class="text-rose-500">*</span></label>
                        <textarea name="reason" 
                                  rows="3" 
                                  required 
                                  placeholder="Contoh: Akumulasi poin sanksi > 150 / pelanggaran berat hukum / penipuan transaksi."
                                  class="w-full text-xs rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-rose-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="blockModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition">
                            Konfirmasi Blokir & Masukkan Blacklist
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

