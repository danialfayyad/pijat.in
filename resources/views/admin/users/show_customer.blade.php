<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index', ['tab' => 'customers']) }}" 
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition"
               title="Kembali ke Daftar Pelanggan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight flex items-center gap-2">
                    <span>Detail Pelanggan: {{ $user->name }}</span>
                    @php
                        $st = $user->status?->value ?? (string) $user->status;
                    @endphp
                    @if ($st === 'terverifikasi')
                        <span class="text-xs px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full">
                            Aktif
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
                    ID Pelanggan: #{{ $user->id }} • Domisili Utama: {{ $primaryAddress?->region?->name ?? 'Belum terdaftar' }}
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

        <!-- Quick Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-semibold uppercase">Pesanan Selesai</div>
                    <div class="text-xl font-bold text-slate-800 mt-0.5">
                        {{ number_format($completedOrdersCount) }} <span class="text-xs text-slate-400 font-normal">pesanan</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-semibold uppercase">Pesanan Dibatalkan</div>
                    <div class="text-xl font-bold text-slate-800 mt-0.5">
                        {{ number_format($cancelledOrdersCount) }} <span class="text-xs text-slate-400 font-normal">pesanan</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-teal-50 text-teal-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-semibold uppercase">Saldo Dompet</div>
                    <div class="text-xl font-bold text-teal-600 mt-0.5">
                        Rp {{ number_format($wallet?->balance ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-sky-50 text-sky-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-semibold uppercase">Alamat Tersimpan</div>
                    <div class="text-xl font-bold text-slate-800 mt-0.5">
                        {{ $addresses->count() }} <span class="text-xs text-slate-400 font-normal">titik</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Biodata & Saved Addresses -->
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
                            <span class="text-slate-400">Nomor Telepon</span>
                            <span class="font-bold text-slate-700">{{ $user->phone ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Jenis Kelamin</span>
                            <span class="font-bold text-slate-700 uppercase">{{ $user->gender ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Tanggal Terdaftar</span>
                            <span class="font-semibold text-slate-700">{{ $user->created_at->format('d F Y') }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-400">Status Akun</span>
                            <span class="font-bold {{ $user->isBlocked() ? 'text-rose-600' : ($user->isSuspended() ? 'text-amber-600' : 'text-emerald-700') }}">
                                {{ $user->status?->label() ?? 'Aktif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Moderation Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <h4 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Tindakan Moderasi Pelanggan
                    </h4>

                    @if ($user->status_reason)
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Catatan Terakhir:</span>
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

                <!-- Addresses Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-3">
                    <h4 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Daftar Alamat Tersimpan
                    </h4>

                    <div class="space-y-2">
                        @forelse ($addresses as $addr)
                            <div class="p-3 rounded-xl border {{ $addr->is_primary ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-100 bg-slate-50' }} text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800">{{ $addr->label ?? 'Alamat' }}</span>
                                    @if ($addr->is_primary)
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold text-[10px] rounded-full">
                                            Alamat Utama
                                        </span>
                                    @endif
                                </div>
                                <p class="text-slate-600 leading-relaxed">{{ $addr->address_text }}</p>
                                <span class="text-[10px] text-slate-400 block">Wilayah: {{ $addr->region?->name ?? '-' }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">Belum ada alamat tersimpan.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column: Recent Orders -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-6 border-b border-slate-200/80 bg-white flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                Riwayat Pesanan Pelanggan
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Daftar pesanan terbaru yang dibuat oleh customer ini.
                            </p>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse ($recentOrders as $order)
                            <div class="p-4 hover:bg-slate-50/60 transition flex items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs font-bold text-emerald-700 hover:underline">
                                            #{{ $order->order_code }}
                                        </a>
                                        <span class="text-[10px] px-2 py-0.5 bg-slate-100 text-slate-600 font-semibold rounded uppercase">
                                            {{ $order->order_type }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-700">
                                        Mitra: <span class="font-semibold">{{ $order->mitra?->name ?? 'Belum ada mitra' }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $order->created_at->format('d M Y, H:i') }} • Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    @php
                                        $os = $order->status?->value ?? (string) $order->status;
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg {{ $os === 'selesai' ? 'bg-emerald-100 text-emerald-800' : ($os === 'dibatalkan' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700') }}">
                                        {{ ucfirst($os) }}
                                    </span>

                                    <a href="{{ route('admin.orders.show', $order) }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition" title="Lihat Detail Pesanan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-400">
                                <p class="text-xs">Pelanggan belum pernah melakukan pemesanan.</p>
                            </div>
                        @endforelse
                    </div>
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
                        Aktifkan Kembali Akun Pelanggan
                    </h3>
                    <button type="button" @click="activateModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="activate">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pengaktifan <span class="text-rose-500">*</span></label>
                        <textarea name="reason" 
                                  rows="3" 
                                  required 
                                  placeholder="Contoh: Masa penangguhan telah usai / banding diterima."
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
                        Tangguhkan Akun Pelanggan (Suspend)
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
                            <option value="3">3 Hari</option>
                            <option value="7">7 Hari</option>
                            <option value="14">14 Hari</option>
                            <option value="30">30 Hari</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penangguhan <span class="text-rose-500">*</span></label>
                        <textarea name="reason" 
                                  rows="3" 
                                  required 
                                  placeholder="Contoh: Pembatalan sepihak berulang / laporan ketidakpatuhan dari mitra."
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
                        Blokir Akun Pelanggan (Blacklist)
                    </h3>
                    <button type="button" @click="blockModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 leading-relaxed">
                    <span class="font-bold block mb-0.5">Peringatan:</span>
                    Akun customer ini akan diblokir permanen. Nomor HP <strong>({{ $user->phone ?? '-' }})</strong> akan dicatat ke <strong>Daftar Hitam (Blacklist)</strong> sistem sehingga tidak dapat login kembali.
                </div>

                <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="block">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pemblokiran Permanen <span class="text-rose-500">*</span></label>
                        <textarea name="reason" 
                                  rows="3" 
                                  required 
                                  placeholder="Contoh: Pelecehan seksual / penipuan pembayaran / pelanggaran pidana."
                                  class="w-full text-xs rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-rose-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="blockModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition">
                            Konfirmasi Blokir & Blacklist
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
