<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.orders.index') }}" 
               class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-800 leading-tight">
                    Pesanan #{{ $order->order_code }}
                </h2>
                <div class="flex items-center space-x-2 text-xs text-slate-500 mt-0.5">
                    <span>Wilayah: <strong class="text-slate-700">{{ $order->region->name }}</strong></span>
                    <span>•</span>
                    <span>Dibuat: {{ $order->created_at->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="{ showCancelModal: false, cancelReason: '' }">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold shadow-xs">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Header Status & Action Bar -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Status Badge -->
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold border {{ $order->status->badgeClasses() }}">
                    {{ $order->status->label() }}
                </span>

                <!-- Type Badge -->
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $order->order_type === \App\Enums\OrderType::Instan ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                    {{ $order->order_type->label() }}
                </span>

                @if ($order->isStuck())
                    <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-600 animate-ping"></span>
                        <span>Pesanan Macet (Perlu Intervensi)</span>
                    </span>
                @endif
            </div>

            <!-- Intervensi Tindakan Admin (ADM-04, BRD 5.3) -->
            @if ($order->canBeCancelledByAdmin())
                <div>
                    <button type="button" 
                            @click="showCancelModal = true"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition duration-150 inline-flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Batalkan Pesanan (Intervensi)</span>
                    </button>
                </div>
            @endif
        </div>

        <!-- Informasi Pembatalan (Jika Dibatalkan) -->
        @if ($order->status === \App\Enums\OrderStatus::Dibatalkan)
            <div class="bg-rose-50/90 rounded-2xl p-5 border border-rose-200 shadow-xs flex items-start space-x-3.5">
                <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <h4 class="text-xs font-bold text-rose-900">Pesanan Dibatalkan oleh {{ $order->cancelledBy?->name ?? 'Sistem' }} ({{ ucfirst($order->cancelled_by_role ?? 'admin') }})</h4>
                        <span class="text-[11px] text-rose-600 font-medium font-mono">{{ $order->updated_at->format('d M Y, H:i') }} WIB</span>
                    </div>
                    <p class="text-xs text-rose-800 mt-1 leading-relaxed">
                        <strong class="font-semibold">Alasan Pembatalan:</strong> {{ $order->cancellation_reason ?? 'Tidak ada catatan alasan spesifik.' }}
                    </p>
                    <div class="mt-2 text-[11px] text-rose-700 font-semibold flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Alokasi pengembalian dana 100% diproses kembali ke pelanggan.</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- 2 KONTAINER UTAMA SEJAJAR -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">

            <!-- KONTAINER 1: Rincian Layanan & Lokasi -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
                <div>
                    <!-- Header Kartu -->
                    <div class="p-5 border-b border-slate-100 flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Layanan & Finansial</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Paket durasi, tarif transaksi, dan jadwal pelaksanaan.</p>
                        </div>
                    </div>

                    <!-- Isi Layanan & Finansial -->
                    <div class="p-5 grid grid-cols-2 gap-4 text-xs">
                        <div class="col-span-2">
                            <span class="text-slate-400 block text-[11px] font-medium">Layanan Dipilih</span>
                            <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $order->service_name }}</div>
                            @if ($order->servicePackage)
                                <span class="text-slate-500 text-[11px] block mt-0.5">{{ $order->servicePackage->name }}</span>
                            @endif
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px] font-medium">Durasi Pijat</span>
                            <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $order->duration_minutes }} Menit</div>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px] font-medium">Total Tarif Layanan</span>
                            <div class="font-bold text-emerald-700 text-base mt-0.5">{{ $order->formatted_price }}</div>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px] font-medium">Bagi Hasil / Komisi</span>
                            <div class="font-semibold text-slate-700 text-sm mt-0.5">{{ (float) $order->commission_percent }}%</div>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px] font-medium">Jadwal Mulai</span>
                            <div class="font-semibold text-slate-800 text-xs mt-0.5">
                                @if ($order->scheduled_start_time)
                                    {{ $order->scheduled_start_time->translatedFormat('d F Y, H:i') }} WIB
                                @else
                                    Segera (Instan)
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian Bawah: Alamat & Wilayah -->
                <div class="p-5 border-t border-slate-100 bg-slate-50/60">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center space-x-1.5 text-xs font-bold text-slate-800">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Alamat Layanan</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ $order->region->name }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        {{ $order->service_address_text }}
                    </p>
                </div>
            </div>

            <!-- KONTAINER 2: Data Pelanggan & Mitra Terapis -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
                <div>
                    <!-- Header Kartu -->
                    <div class="p-5 border-b border-slate-100 flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Pelanggan & Mitra</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Identitas pemesan, penerima manfaat, dan mitra terapis.</p>
                        </div>
                    </div>

                    <!-- Isi Data Pemesan & Penerima -->
                    <div class="p-5 space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-slate-400 block text-[11px] font-medium">Nama Penerima</span>
                                <div class="font-bold text-slate-800 mt-0.5">
                                    {{ $order->recipient_name }}
                                    @if ($order->recipient_gender)
                                        <span class="text-slate-500 font-normal text-[11px]">({{ ucfirst($order->recipient_gender) }})</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px] font-medium">Kontak Penerima</span>
                                <div class="font-mono text-slate-700 font-semibold mt-0.5">{{ $order->recipient_phone }}</div>
                            </div>
                        </div>

                        @if ($order->customer)
                            <div class="pt-2.5 border-t border-slate-100">
                                <span class="text-slate-400 block text-[11px] font-medium">Akun Pemesan</span>
                                <div class="font-semibold text-slate-800">{{ $order->customer->name }}</div>
                                <div class="text-slate-400 text-[11px] mt-0.5">{{ $order->customer->phone }} • {{ $order->customer->email }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Bagian Bawah: Mitra Penanggung Jawab -->
                <div class="p-5 border-t border-slate-100 bg-slate-50/60">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider mb-2">Mitra Terapis Penanggung Jawab</span>

                    @if ($order->mitra)
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm uppercase">
                                {{ substr($order->mitra->name, 0, 1) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-xs">{{ $order->mitra->name }}</h4>
                                <p class="text-xs text-slate-500 font-mono">{{ $order->mitra->phone }}</p>
                                @if ($order->mitra->partnerProfile)
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        Status: <span class="text-emerald-700 font-semibold">{{ ucfirst($order->mitra->partnerProfile->status) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-amber-50/80 border border-amber-200/80 text-amber-900 text-xs">
                            <div class="flex items-center space-x-1.5 font-bold text-amber-800">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>Belum Ada Mitra Ditugaskan</span>
                            </div>
                            <p class="mt-1 text-slate-600 text-[11px] leading-relaxed">
                                Sedang disiarkan ke mitra terdekat di wilayah <strong>{{ $order->region->name }}</strong>.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- KONTAINER 3: LINIMASA RIWAYAT STATUS (FULL WIDTH) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Linimasa Riwayat Status</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Catatan perkembangan dan tahapan pelaksanaan pesanan secara kronologis.</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 border border-slate-200">
                    {{ $order->statusHistories->count() }} Tahapan
                </span>
            </div>

            <!-- Step by Step Vertical Track -->
            <div class="mt-6">
                <div class="relative pl-8 space-y-8 before:absolute before:left-3.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200">
                    @forelse ($order->statusHistories as $index => $history)
                        <div class="relative flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <!-- Step Number Indicator -->
                            <div class="absolute -left-8 top-0 flex items-center justify-center">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs ring-4 ring-white shadow-2xs {{ $history->to_status === \App\Enums\OrderStatus::Dibatalkan ? 'bg-rose-100 text-rose-700 border border-rose-300' : ($history->to_status === \App\Enums\OrderStatus::Selesai ? 'bg-emerald-100 text-emerald-700 border border-emerald-300' : 'bg-blue-100 text-blue-700 border border-blue-300') }}">
                                    {{ $index + 1 }}
                                </span>
                            </div>

                            <!-- Step Content -->
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $history->to_status->badgeClasses() }}">
                                        {{ $history->to_status->label() }}
                                    </span>
                                    <span class="text-xs text-slate-400 font-medium">
                                        Tahap {{ $index + 1 }} dari {{ $order->statusHistories->count() }}
                                    </span>
                                </div>

                                @if ($history->note)
                                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs text-slate-700 leading-relaxed max-w-3xl shadow-2xs">
                                        {{ $history->note }}
                                    </div>
                                @endif

                                <div class="text-xs text-slate-400 flex items-center space-x-1.5 pt-0.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>Diperbarui oleh: <strong class="text-slate-700 font-semibold">{{ $history->actor?->name ?? 'Sistem' }}</strong> ({{ ucfirst($history->actor_role) }})</span>
                                </div>
                            </div>

                            <!-- Timestamp Right-Aligned -->
                            <div class="text-left sm:text-right shrink-0">
                                <div class="text-xs font-bold text-slate-800 font-mono">
                                    {{ $history->created_at->format('H:i') }} WIB
                                </div>
                                <div class="text-[11px] text-slate-400 font-medium mt-0.5">
                                    {{ $history->created_at->translatedFormat('d F Y') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-xs text-slate-400 py-6">
                            Belum ada catatan riwayat status.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Cancellation Modal for Required Reason -->
        <div x-cloak 
             x-show="showCancelModal" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;" 
             role="dialog" 
             aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showCancelModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-3xl bg-white p-6 text-center shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200"
                     @click.stop>
                    
                    <!-- Centered Circular Icon -->
                    <div class="mx-auto mb-4 w-14 h-14 rounded-full bg-rose-100 text-rose-600 ring-8 ring-rose-50 flex items-center justify-center shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <h3 class="text-base font-bold text-slate-900 leading-tight">
                        Batalkan Pesanan Ini?
                    </h3>
                    <p class="text-xs text-slate-500 mt-1.5 px-4 leading-relaxed">
                        Dana pelanggan akan dialokasikan 100% untuk pengembalian. Wajib sertakan alasan pembatalan.
                    </p>

                    <form id="cancelOrderForm" method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="mt-4 text-left">
                        @csrf
                        <label for="reason" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Alasan Pembatalan <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="reason" 
                                  name="reason" 
                                  x-model="cancelReason" 
                                  rows="3" 
                                  required 
                                  placeholder="Contoh: Tidak ada mitra yang tersedia di area dalam waktu batas SLA."
                                  class="w-full text-xs rounded-xl border-slate-300 focus:border-rose-500 focus:ring-rose-500 shadow-2xs placeholder-slate-400"></textarea>

                        <div class="grid grid-cols-2 gap-2.5 mt-5">
                            <button type="button" 
                                    @click="showCancelModal = false" 
                                    class="w-full py-2.5 px-3 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200/80 rounded-xl transition cursor-pointer">
                                Tutup
                            </button>

                            <button type="submit" 
                                    :disabled="cancelReason.trim().length < 5"
                                    class="w-full py-2.5 px-3 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl shadow-xs transition cursor-pointer">
                                Batalkan Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
