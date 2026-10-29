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

        <!-- 2 KONTAINER UTAMA: OPERASIONAL LAPANGAN & STRUK FINANSIAL -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">

            <!-- KONTAINER 1: Pelaksanaan & Lokasi Layanan (Fulfillment) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
                <div>
                    <!-- Header Kartu -->
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pelaksanaan & Lokasi</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ $order->region->name }}
                        </span>
                    </div>

                    <!-- Penerima & Alamat Layanan -->
                    <div class="p-6 space-y-4">
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Penerima Layanan</div>
                            <div class="text-base font-bold text-slate-900 mt-0.5">
                                {{ $order->recipient_name }}
                                @if ($order->recipient_gender)
                                    <span class="text-xs font-normal text-slate-500">({{ ucfirst($order->recipient_gender) }})</span>
                                @endif
                            </div>
                            <div class="text-xs font-mono font-medium text-slate-600 mt-0.5">
                                {{ $order->recipient_phone }}
                            </div>
                        </div>

                        <!-- Alamat Tujuan -->
                        <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-200/70 text-xs leading-relaxed">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Alamat Lengkap</span>
                            <p class="text-slate-700">{{ $order->service_address_text }}</p>
                        </div>

                        @if ($order->customer && $order->customer->name !== $order->recipient_name)
                            <div class="text-xs text-slate-400 pt-1">
                                Dipesan oleh akun: <strong class="text-slate-600 font-semibold">{{ $order->customer->name }}</strong> ({{ $order->customer->phone }})
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Bagian Bawah: Terapis Pelaksana -->
                <div class="p-6 border-t border-slate-100 bg-slate-50/40">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">Terapis Penanggung Jawab</div>

                    @if ($order->mitra)
                        <div class="flex items-center space-x-3.5">
                            <div class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm uppercase shrink-0">
                                {{ substr($order->mitra->name, 0, 1) }}
                            </div>
                            <div class="text-xs">
                                <div class="font-bold text-slate-900 text-sm">{{ $order->mitra->name }}</div>
                                <div class="font-mono text-slate-500 mt-0.5">{{ $order->mitra->phone }}</div>
                                @if ($order->mitra->partnerProfile)
                                    <div class="text-[11px] text-emerald-700 font-semibold mt-0.5">
                                        Mitra Terverifikasi • {{ ucfirst($order->mitra->partnerProfile->status) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="flex items-start space-x-3 p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/80 text-xs">
                            <span class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 shrink-0 animate-pulse"></span>
                            <div>
                                <div class="font-bold text-amber-900">Menunggu Konfirmasi Mitra</div>
                                <p class="text-slate-600 mt-0.5 leading-relaxed text-[11px]">
                                    Sistem sedang menyiarkan pesanan secara otomatis ke mitra terdekat di wilayah {{ $order->region->name }}.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- KONTAINER 2: Rincian Layanan & Struk Finansial (Invoice Breakdown) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
                <div>
                    <!-- Header Kartu -->
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Layanan & Transaksi</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $order->order_type->label() }}
                        </span>
                    </div>

                    <!-- Ringkasan Paket & Waktu -->
                    <div class="p-6 border-b border-slate-100 space-y-3">
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Layanan Dipilih</div>
                            <div class="text-base font-bold text-slate-900 mt-0.5">{{ $order->service_name }}</div>
                            @if ($order->servicePackage)
                                <div class="text-xs text-slate-500 mt-0.5">{{ $order->servicePackage->name }}</div>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-xs pt-2">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Durasi Layanan</span>
                                <span class="font-semibold text-slate-800">{{ $order->duration_minutes }} Menit</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Jadwal Pelaksanaan</span>
                                <span class="font-semibold text-slate-800">
                                    @if ($order->scheduled_start_time)
                                        {{ $order->scheduled_start_time->translatedFormat('d F Y, H:i') }} WIB
                                    @else
                                        Segera (Instan)
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian Bawah: Struk Rincian Finansial -->
                <div class="p-6 bg-slate-50/40 text-xs space-y-2.5">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Rincian Finansial</div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Tarif Layanan (Bruto)</span>
                        <span class="font-mono font-semibold text-slate-800">{{ $order->formatted_price }}</span>
                    </div>

                    <div class="flex items-center justify-between text-slate-500">
                        <span>Komisi Platform ({{ (float) $order->commission_percent }}%)</span>
                        <span class="font-mono text-slate-600">
                            - Rp {{ number_format($order->total_price * ($order->commission_percent / 100), 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-emerald-700 font-semibold pt-1 border-t border-slate-200/80">
                        <span>Hak Bersih Mitra</span>
                        <span class="font-mono font-bold">
                            Rp {{ number_format($order->total_price - ($order->total_price * ($order->commission_percent / 100)), 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="mt-3 pt-3 border-t-2 border-slate-200 flex items-baseline justify-between">
                        <span class="text-xs font-bold text-slate-800">Total Tarif Pesanan</span>
                        <span class="text-base font-extrabold text-slate-900 font-mono">{{ $order->formatted_price }}</span>
                    </div>
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
