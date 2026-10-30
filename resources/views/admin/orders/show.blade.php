<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" 
               class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition"
               title="Kembali ke Daftar Pesanan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="text-xl font-bold text-slate-800 leading-tight">
                Pesanan #{{ $order->order_code }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="{ showCancelModal: false, cancelReason: '' }">

        <!-- Status Alerts -->
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold shadow-2xs flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold shadow-2xs">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Informasi Pembatalan (Jika Dibatalkan) -->
        @if ($order->status === \App\Enums\OrderStatus::Dibatalkan)
            <div class="bg-rose-50/80 rounded-2xl p-5 border border-rose-200 shadow-2xs flex items-start gap-3.5">
                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div class="flex-1 text-xs">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <h4 class="font-bold text-rose-900">Pesanan Dibatalkan oleh {{ $order->cancelledBy?->name ?? 'Sistem' }} ({{ ucfirst($order->cancelled_by_role ?? 'admin') }})</h4>
                        <span class="text-[11px] text-rose-600 font-medium">{{ $order->updated_at->format('d M Y, H:i') }} WIB</span>
                    </div>
                    <p class="text-rose-800 mt-1 leading-relaxed">
                        <strong class="font-semibold">Alasan Pembatalan:</strong> {{ $order->cancellation_reason ?? 'Tidak ada catatan alasan spesifik.' }}
                    </p>
                    <div class="mt-2 text-[11px] text-rose-700 font-semibold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Alokasi pengembalian dana 100% diproses kembali ke pelanggan.</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- 2 KONTAINER UTAMA: SEJAJAR DAN TINGGI SELARAS (ITEMS-STRETCH) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

            <!-- KOLOM KIRI (7 Kolom): Rincian Layanan & Lokasi/Penerima -->
            <div class="lg:col-span-7 flex flex-col gap-6">

                <!-- 1. Rincian Layanan & Waktu -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Rincian Layanan</h3>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                            <span class="w-1.5 h-1.5 rounded-full {{ $order->status->dotClass() }}"></span>
                            {{ $order->status->label() }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <div class="text-base font-bold text-slate-800">{{ $order->service_name }}</div>
                        @if ($order->servicePackage)
                            <div class="text-xs text-slate-500">{{ $order->servicePackage->name }}</div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Tipe Pemesanan</span>
                            <span class="font-semibold text-slate-700 mt-0.5 block">{{ $order->order_type->label() }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Durasi</span>
                            <span class="font-semibold text-slate-700 mt-0.5 block">{{ $order->duration_minutes }} Menit</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Jadwal Pelaksanaan</span>
                            <span class="font-semibold text-slate-700 mt-0.5 block">
                                @if ($order->scheduled_start_time)
                                    {{ $order->scheduled_start_time->translatedFormat('d F Y, H:i') }} WIB
                                @else
                                    Segera (Instan)
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2. Lokasi & Penerima Layanan (Tinggi Fleksibel Lurus dengan Rincian Pembayaran) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex-1 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Lokasi & Penerima</h3>
                            <span class="text-xs text-slate-500 font-medium">Wilayah {{ $order->region->name }}</span>
                        </div>

                        <div class="space-y-1">
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide block">Penerima Layanan</span>
                            <div class="text-sm font-bold text-slate-800">
                                {{ $order->recipient_name }}
                                @if ($order->recipient_gender)
                                    <span class="text-xs font-normal text-slate-500">({{ ucfirst($order->recipient_gender) }})</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 font-mono">
                                {{ $order->recipient_phone }}
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 space-y-1">
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide block">Alamat Tujuan</span>
                            <p class="text-xs text-slate-700 leading-relaxed">{{ $order->service_address_text }}</p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400 mt-4">
                        <span>Dibuat: {{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                        @if ($order->customer && $order->customer->name !== $order->recipient_name)
                            <span>Pemesan: <strong class="text-slate-600 font-medium">{{ $order->customer->name }}</strong></span>
                        @endif
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN (5 Kolom): Terapis Pelaksana & Struk Finansial -->
            <div class="lg:col-span-5 flex flex-col gap-6">

                <!-- 1. Terapis Penanggung Jawab -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Terapis Pelaksana</h3>
                    </div>

                    @if ($order->mitra)
                        <div class="space-y-2 text-xs">
                            <div class="font-bold text-slate-800 text-sm">{{ $order->mitra->name }}</div>
                            <div class="text-slate-500 font-mono">{{ $order->mitra->phone }}</div>
                            @if ($order->mitra->partnerProfile)
                                <div class="text-[11px] text-emerald-700 font-medium">
                                    Mitra Terverifikasi &bull; Status: {{ ucfirst($order->mitra->partnerProfile->status) }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="space-y-2 text-xs">
                            <div class="flex items-start gap-2.5 text-slate-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0 animate-ping"></span>
                                <div>
                                    <span class="font-semibold text-slate-800">Mencari Mitra Terdekat</span>
                                    <p class="text-[11px] text-slate-400 mt-0.5 leading-relaxed">
                                        Pesanan sedang disiarkan ke mitra terapis aktif di wilayah {{ $order->region->name }}.
                                    </p>
                                </div>
                            </div>

                            @if ($order->isStuck())
                                <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-900 leading-relaxed mt-2">
                                    <strong>Status Macet:</strong> Waktu penugasan telah melampaui batas toleransi SLA sistem.
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Aksi Intervensi Admin (Jika Masih Aktif / Bisa Dibatalkan) -->
                    @if ($order->canBeCancelledByAdmin())
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] text-slate-400">Tindakan Admin:</span>
                            <button type="button" 
                                    @click="showCancelModal = true"
                                    class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline cursor-pointer inline-flex items-center gap-1">
                                <span>Batalkan Pesanan</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- 2. Rincian Pembayaran (Tinggi Fleksibel Lurus dengan Lokasi & Penerima) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Rincian Pembayaran</h3>
                        </div>

                        <div class="space-y-2.5 text-xs pt-4">
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Tarif Layanan (Bruto)</span>
                                <span class="font-medium text-slate-800">{{ $order->formatted_price }}</span>
                            </div>

                            <div class="flex items-center justify-between text-slate-500">
                                <span>Komisi Platform ({{ (float) $order->commission_percent }}%)</span>
                                <span class="text-slate-600">
                                    - Rp {{ number_format($order->total_price * ($order->commission_percent / 100), 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-emerald-700 font-semibold pt-1 border-t border-slate-100">
                                <span>Hak Bersih Mitra</span>
                                <span>
                                    Rp {{ number_format($order->total_price - ($order->total_price * ($order->commission_percent / 100)), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-slate-200 flex items-baseline justify-between mt-4">
                        <span class="font-bold text-slate-800 text-xs">Total Tagihan</span>
                        <span class="text-base font-black text-slate-900">{{ $order->formatted_price }}</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- 3. LINIMASA RIWAYAT STATUS (FULL WIDTH SELEBAR CONTAINER RINCIAN PEMBAYARAN) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Linimasa Status</h3>
                <span class="text-xs text-slate-400">{{ $order->statusHistories->count() }} Tahapan</span>
            </div>

            <!-- Activity Stream Timeline -->
            <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                @forelse ($order->statusHistories as $index => $history)
                    <div class="relative flex flex-col sm:flex-row sm:items-start justify-between gap-3 text-xs">
                        <!-- Node indicator dot -->
                        <div class="absolute -left-6 top-1">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center bg-white border-2 {{ $history->to_status === \App\Enums\OrderStatus::Dibatalkan ? 'border-rose-500 text-rose-500' : ($history->to_status === \App\Enums\OrderStatus::Selesai ? 'border-emerald-500 text-emerald-500' : 'border-sky-500 text-sky-500') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $history->to_status === \App\Enums\OrderStatus::Dibatalkan ? 'bg-rose-500' : ($history->to_status === \App\Enums\OrderStatus::Selesai ? 'bg-emerald-500' : 'bg-sky-500') }}"></span>
                            </span>
                        </div>

                        <!-- Step Info -->
                        <div class="space-y-1 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800">
                                    {{ $history->to_status->label() }}
                                </span>
                                <span class="text-[11px] text-slate-400 font-normal">
                                    &bull; Tahap {{ $index + 1 }} dari {{ $order->statusHistories->count() }}
                                </span>
                            </div>

                            @if ($history->note)
                                <div class="text-xs text-slate-700 bg-slate-50/70 p-2.5 rounded-xl border border-slate-100 max-w-3xl leading-relaxed">
                                    {{ $history->note }}
                                </div>
                            @endif

                            <div class="text-[11px] text-slate-400">
                                Diperbarui oleh: <span class="text-slate-600 font-medium">{{ $history->actor?->name ?? 'Sistem' }}</span> ({{ ucfirst($history->actor_role) }})
                            </div>
                        </div>

                        <!-- Timestamp -->
                        <div class="text-left sm:text-right shrink-0 text-slate-500">
                            <span class="font-semibold text-slate-700">{{ $history->created_at->format('H:i') }} WIB</span>
                            <span class="text-[11px] text-slate-400 block">{{ $history->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-xs text-slate-400 py-4">
                        Belum ada catatan riwayat status.
                    </div>
                @endforelse
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
                    
                    <div class="mx-auto mb-4 w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shadow-2xs">
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