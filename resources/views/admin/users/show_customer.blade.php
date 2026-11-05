<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index', ['tab' => 'customers']) }}" 
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition"
               title="Kembali ke Manajemen Pengguna">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Profil Pelanggan: {{ $user->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{ 
            suspendModalOpen: false, 
            blockModalOpen: false, 
            activateModalOpen: false 
         }">

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
                <div class="font-bold mb-1">Terjadi kesalahan pada moderasi:</div>
                <ul class="list-disc pl-5 space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Data Diri, Alamat Tersimpan & Moderasi -->
            <div class="space-y-6">
                <!-- Data Diri Pelanggan -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Data Diri Pelanggan
                        </h3>
                        @php
                            $st = $user->status?->value ?? (string) $user->status;
                        @endphp
                        @if ($st === 'terverifikasi')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                        @elseif ($st === 'ditangguhkan')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Ditangguhkan
                            </span>
                        @elseif ($st === 'diblokir')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Diblokir
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ ucfirst(str_replace('_', ' ', $st)) }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="space-y-2.5 text-xs">
                        <div>
                            <span class="text-slate-400 block">Nama Lengkap:</span>
                            <span class="font-bold text-slate-800 text-sm">{{ $user->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Nomor Telepon:</span>
                            <span class="font-mono font-medium text-slate-700">{{ $user->phone ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Alamat Email:</span>
                            <span class="text-slate-700">{{ $user->email }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Jenis Kelamin:</span>
                            <span class="capitalize font-semibold text-slate-700">{{ $user->gender ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Tanggal Registrasi:</span>
                            <span class="font-semibold text-slate-700">{{ $user->created_at->format('d F Y') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Wilayah Domisili Utama:</span>
                            <span class="font-bold text-emerald-700">{{ $primaryAddress?->region?->name ?? 'Belum ada alamat' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Daftar Alamat Tersimpan -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                        Alamat Tersimpan ({{ $addresses->count() }})
                    </h3>

                    <div class="space-y-2">
                        @forelse ($addresses as $addr)
                            <div class="p-3 rounded-xl border {{ $addr->is_primary ? 'border-emerald-200 bg-emerald-50/30' : 'border-slate-100 bg-slate-50' }} text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800">{{ $addr->label ?? 'Alamat' }}</span>
                                    @if ($addr->is_primary)
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-semibold text-[10px] rounded-full">
                                            Utama
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

                <!-- Tindakan Moderasi Akun -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                        Tindakan Moderasi Akun
                    </h3>

                    @if ($user->status_reason)
                        <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl text-xs space-y-1">
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
                                    class="w-full py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition shadow-xs cursor-pointer">
                                Aktifkan Kembali Akun
                            </button>
                        @endif

                        @if ($user->status !== \App\Enums\UserStatus::Ditangguhkan && $user->status !== \App\Enums\UserStatus::Diblokir)
                            <button type="button" 
                                    @click="suspendModalOpen = true"
                                    class="w-full py-2.5 px-3 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-xl transition shadow-xs cursor-pointer">
                                Tangguhkan Akun (Suspend)
                            </button>
                        @endif

                        @if ($user->status !== \App\Enums\UserStatus::Diblokir)
                            <button type="button" 
                                    @click="blockModalOpen = true"
                                    class="w-full py-2.5 px-3 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl transition shadow-xs cursor-pointer">
                                Blokir Akun Permanen (Blacklist)
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Ringkasan Aktivitas & Riwayat Pesanan -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Ringkasan Aktivitas Pelanggan (Clean 1x3 Grid) -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                        Ringkasan Aktivitas & Dompet
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-1">
                            <span class="text-xs font-medium text-slate-500 block">Pesanan Selesai</span>
                            <div class="text-2xl font-bold text-slate-800">{{ number_format($completedOrdersCount) }}</div>
                            <span class="text-[11px] text-emerald-600 font-medium">Layanan berhasil</span>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-1">
                            <span class="text-xs font-medium text-slate-500 block">Pesanan Dibatalkan</span>
                            <div class="text-2xl font-bold text-slate-800">{{ number_format($cancelledOrdersCount) }}</div>
                            <span class="text-[11px] text-rose-600 font-medium">Batal sistem/pelanggan</span>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-1">
                            <span class="text-xs font-medium text-slate-500 block">Saldo Dompet</span>
                            <div class="text-2xl font-bold text-teal-600">
                                Rp {{ number_format($wallet?->balance ?? 0, 0, ',', '.') }}
                            </div>
                            <span class="text-[11px] text-slate-400">Saldo aktif akun</span>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Pesanan Pelanggan -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-slate-800 text-base">
                                Riwayat Pesanan Pelanggan
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Riwayat transaksi dan layanan pijat yang dipesan customer ini.
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
                                        Terapis: <span class="font-semibold">{{ $order->mitra?->name ?? 'Belum ada mitra' }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $order->created_at->format('d/m/Y H:i') }} • Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    @php
                                        $os = $order->status?->value ?? (string) $order->status;
                                    @endphp
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $os === 'selesai' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($os === 'dibatalkan' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                                        {{ ucfirst($os) }}
                                    </span>

                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                                        Detail &rarr;
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-slate-400 text-xs">
                                Pelanggan belum pernah melakukan pemesanan.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- MODALS -->

        <!-- MODAL: ACTIVATE ACCOUNT -->
        <div x-show="activateModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-base text-slate-800">
                        Aktifkan Kembali Akun Pelanggan
                    </h3>
                    <button type="button" @click="activateModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="activate">

                    @if ($user->penalty_points >= 30)
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700">Penyesuaian Poin Sanksi</label>
                                <span class="text-[11px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100">
                                    {{ $user->penalty_points }} Poin
                                </span>
                            </div>
                            <select name="penalty_policy" class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-white py-2">
                                <option value="probation" selected>Masa Percobaan (Setel ke 29 Poin)</option>
                                <option value="reset">Pemutihan Penuh (Reset ke 0 Poin)</option>
                                @if ($user->penalty_points < 60)
                                    <option value="keep">Pertahankan Poin ({{ $user->penalty_points }} Poin)</option>
                                @endif
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pengaktifan <span class="text-rose-500">*</span></label>
                        <textarea name="reason" 
                                  rows="3" 
                                  required 
                                  placeholder="Contoh: Masa penangguhan telah usai / banding disetujui."
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
                    <h3 class="font-bold text-base text-slate-800">
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
                                  placeholder="Contoh: Pembatalan berulang / keluhan tidak patuh dari mitra."
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
                    <h3 class="font-bold text-base text-rose-800">
                        Blokir Akun Pelanggan (Blacklist)
                    </h3>
                    <button type="button" @click="blockModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 leading-relaxed">
                    Nomor telepon <strong>({{ $user->phone ?? '-' }})</strong> akan dicatat ke <strong>Daftar Hitam (Blacklist)</strong> sistem sehingga tidak dapat login kembali.
                </div>

                <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="block">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pemblokiran <span class="text-rose-500">*</span></label>
                        <textarea name="reason" 
                                  rows="3" 
                                  required 
                                  placeholder="Contoh: Pelecehan verbal/fisik / penipuan transaksi."
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
