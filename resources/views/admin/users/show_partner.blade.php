<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index', ['tab' => 'partners']) }}" 
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition"
               title="Kembali ke Manajemen Pengguna">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Profil Mitra: {{ $user->name }}
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
            <!-- Left Column: Data Diri, Rekening, & Moderasi -->
            <div class="space-y-6">
                <!-- Data Diri Mitra -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Data Diri Mitra
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
                            <span class="text-slate-400 block">Nomor WhatsApp:</span>
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
                        @if ($user->date_of_birth)
                            <div>
                                <span class="text-slate-400 block">Tanggal Lahir:</span>
                                <span class="font-semibold text-slate-700">{{ $user->date_of_birth->format('d F Y') }}</span>
                            </div>
                        @endif
                        <div>
                            <span class="text-slate-400 block">Nomor Induk Kependudukan (NIK):</span>
                            <span class="font-mono font-bold text-slate-800">
                                @if ($user->nik)
                                    •••• •••• •••• {{ substr($user->nik, -4) }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Wilayah Operasional:</span>
                            <span class="font-bold text-emerald-700">{{ $partnerProfile?->region?->name ?? 'Belum terdata' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Alamat Domisili Fisik:</span>
                            <span class="text-slate-700 leading-relaxed">{{ $partnerProfile?->address_text ?? 'Belum ada alamat' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Status Ketersediaan:</span>
                            <span class="font-semibold {{ $partnerProfile?->is_active ? 'text-emerald-700' : 'text-slate-500' }}">
                                {{ $partnerProfile?->is_active ? 'Online (Siap Menerima Pesanan)' : 'Offline' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Rekening Pencairan Bank -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                        Rekening Bank (Pencairan)
                    </h3>

                    @if ($bankAccount)
                        <div class="space-y-2 text-xs">
                            <div>
                                <span class="text-slate-400 block">Nama Bank:</span>
                                <span class="font-bold text-slate-800">{{ $bankAccount->bank_name }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Nomor Rekening:</span>
                                <span class="font-mono font-bold text-slate-800">{{ $bankAccount->account_number }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Atas Nama Pemilik:</span>
                                <span class="font-semibold text-slate-700">{{ $bankAccount->account_holder_name }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">Belum mendaftarkan rekening pencairan.</p>
                    @endif
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

            <!-- Right Column: Rating & Ulasan Terpadu & Poin Sanksi -->
            <div class="lg:col-span-2 space-y-6">
                <!-- RATING & ULASAN PELANGGAN CARD -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-slate-800 text-base">
                                Evaluasi Rating & Ulasan Pelanggan
                            </h3>
                            <div class="text-xs text-slate-400 mt-0.5">
                                {{ $completedOrdersCount }} pesanan selesai &bull; {{ $ratingsCount }} ulasan terverifikasi
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-xl">
                            {{ $ratingsCount }} Ulasan
                        </span>
                    </div>

                    @if ($ratingsCount > 0)
                        <!-- Rating Breakdown Visual (When reviews exist) -->
                        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 items-center">
                                <!-- Overall Score -->
                                <div class="text-center sm:border-r sm:border-slate-200 sm:pr-6">
                                    <div class="text-4xl font-extrabold text-slate-800 tracking-tight">
                                        {{ number_format($ratingScore, 2) }}
                                    </div>
                                    <div class="flex items-center justify-center gap-1 my-1.5 text-amber-400">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= round($ratingScore) ? 'fill-current' : 'text-slate-300' }}" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-xs text-slate-400">Skor rata-rata bintang</span>
                                </div>

                                <!-- Star Bars -->
                                <div class="sm:col-span-2 space-y-2">
                                    @foreach ([5, 4, 3, 2, 1] as $star)
                                        @php
                                            $countStar = $distribution[$star] ?? 0;
                                            $pct = round(($countStar / $ratingsCount) * 100);
                                        @endphp
                                        <div class="flex items-center gap-3 text-xs">
                                            <span class="w-16 font-medium text-slate-600 shrink-0">
                                                Bintang {{ $star }}
                                            </span>
                                            <div class="flex-1 bg-slate-200/80 rounded-full h-2 overflow-hidden">
                                                <div class="bg-amber-400 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="w-14 text-right text-slate-400 font-mono text-[11px] shrink-0">
                                                {{ $countStar }} ({{ $pct }}%)
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Customer Reviews List -->
                        <div class="p-5 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Riwayat Testimoni Pelanggan
                            </h4>

                            <div class="divide-y divide-slate-100">
                                @foreach ($reviews as $rev)
                                    <div class="py-3.5 space-y-1.5 first:pt-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-slate-800">{{ $rev->fromUser?->name ?? 'Pelanggan' }}</span>
                                                @if ($rev->order)
                                                    <span class="text-slate-300">•</span>
                                                    <a href="{{ route('admin.orders.show', $rev->order) }}" class="font-mono text-emerald-700 hover:underline">
                                                        #{{ $rev->order->order_code }}
                                                    </a>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-amber-500 font-bold">★ {{ $rev->score }}</span>
                                                <span class="text-slate-400 text-[11px]">{{ $rev->created_at->format('d/m/Y') }}</span>
                                            </div>
                                        </div>

                                        @if ($rev->review)
                                            <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                                "{{ $rev->review }}"
                                            </p>
                                        @else
                                            <p class="text-xs text-slate-400 italic">Pelanggan tidak menyertakan ulasan teks.</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if ($reviews->hasPages())
                                <div class="pt-3 border-t border-slate-100">
                                    {{ $reviews->links() }}
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Clean Empty State for New Partners without Reviews -->
                        <div class="p-8 text-center text-slate-400 text-xs space-y-1">
                            <p class="font-semibold text-slate-600">Belum ada ulasan rating untuk mitra ini.</p>
                            <p class="text-slate-400">Skor performa dan distribusi bintang akan otomatis tercatat setelah mitra menyelesaikan layanan pesanan.</p>
                        </div>
                    @endif
                </div>

                <!-- MONITORING POIN SANKSI CARD -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Monitoring Poin Sanksi & Kepatuhan
                        </h3>
                        <a href="{{ route('admin.penalties.show', $user) }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                            Audit Poin Lengkap &rarr;
                        </a>
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-semibold text-slate-700">Akumulasi Poin Saat Ini:</span>
                            <span class="font-bold text-sm {{ $user->penalty_points >= 30 ? 'text-rose-600' : 'text-slate-800' }}">
                                {{ $user->penalty_points }} Poin
                            </span>
                        </div>
                        <div class="grid grid-cols-4 gap-1 text-[10px] text-center pt-1">
                            <div class="p-1 rounded {{ $user->penalty_points >= 30 ? 'bg-amber-100 font-bold text-amber-800' : 'bg-slate-200/60 text-slate-500' }}">
                                30: Peringatan
                            </div>
                            <div class="p-1 rounded {{ $user->penalty_points >= 60 ? 'bg-orange-100 font-bold text-orange-800' : 'bg-slate-200/60 text-slate-500' }}">
                                60: Suspend 3H
                            </div>
                            <div class="p-1 rounded {{ $user->penalty_points >= 100 ? 'bg-rose-100 font-bold text-rose-800' : 'bg-slate-200/60 text-slate-500' }}">
                                100: Suspend 7H
                            </div>
                            <div class="p-1 rounded {{ $user->penalty_points >= 150 ? 'bg-purple-100 font-bold text-purple-800' : 'bg-slate-200/60 text-slate-500' }}">
                                150: Banned
                            </div>
                        </div>
                    </div>

                    @if ($user->penaltyLedgers->isNotEmpty())
                        <div class="space-y-1.5 pt-1">
                            @foreach ($user->penaltyLedgers->take(3) as $ledger)
                                <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-50 last:border-0">
                                    <div>
                                        <p class="font-medium text-slate-700">{{ $ledger->reason }}</p>
                                        <span class="text-[10px] text-slate-400">{{ $ledger->created_at->format('d/m/Y H:i') }}</span>
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

        <!-- MODALS -->

        <!-- MODAL: ACTIVATE ACCOUNT -->
        <div x-show="activateModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-base text-slate-800">
                        Aktifkan Kembali Akun Mitra
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
                                  placeholder="Contoh: Masa penangguhan telah selesai / banding disetujui / pembinaan SOP selesai."
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
                        Tangguhkan Akun Mitra (Suspend)
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
                                  placeholder="Jelaskan alasan pelanggaran SOP atau keluhan pelanggan yang mendasari sanksi suspend."
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
                        Blokir Akun Permanen (Blacklist)
                    </h3>
                    <button type="button" @click="blockModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 leading-relaxed">
                    Nomor WhatsApp <strong>({{ $user->phone ?? '-' }})</strong> dan identitas perangkat akan dimasukkan ke <strong>Daftar Hitam (Blacklist)</strong> sehingga tidak dapat login kembali.
                </div>

                <form method="POST" action="{{ route('admin.users.update-status', $user) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="block">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pemblokiran <span class="text-rose-500">*</span></label>
                        <textarea name="reason" 
                                  rows="3" 
                                  required 
                                  placeholder="Contoh: Akumulasi poin > 150 / pelanggaran pidana / penipuan transaksi."
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
