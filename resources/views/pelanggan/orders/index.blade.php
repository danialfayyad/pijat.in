<x-pwa-layout title="Riwayat Pesanan - Pijat.In" :hideHeader="true">
    <div class="-mx-4 -mt-3 -mb-8 flex flex-col min-h-screen" 
         x-data="{ 
            tab: 'active',
            showCancelModal: false,
            cancelOrderId: null,
            cancelOrderCode: '',
            cancelHasRefund: false,
            openCancelModal(id, code, refund) {
                this.cancelOrderId = id;
                this.cancelOrderCode = code;
                this.cancelHasRefund = refund;
                this.showCancelModal = true;
            }
         }">

        <!-- Fixed Top Bar Navigasi (Selalu Mengambang di Atas) -->
        <div class="fixed top-0 left-0 right-0 max-w-md mx-auto z-20 px-5 pt-4 pb-2 flex items-center justify-between pointer-events-none">
            <a href="{{ route('pelanggan.dashboard') }}" 
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto"
               title="Kembali ke Beranda"
               aria-label="Kembali ke Beranda">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <span class="text-xs font-bold text-white uppercase tracking-widest drop-shadow-xs">
                Riwayat Pesanan
            </span>

            <a href="{{ route('pelanggan.help.index') }}" 
               class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md text-white flex items-center justify-center hover:bg-white/25 active:scale-95 transition cursor-pointer pointer-events-auto"
               title="Pusat Bantuan"
               aria-label="Pusat Bantuan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </a>
        </div>

        <!-- 1. FIXED GREEN HERO BACKGROUND (Full & Fluid Wave) -->
        <div class="fixed inset-0 max-w-md mx-auto z-0 bg-gradient-to-b from-[#059669] via-[#047857] to-[#064e3b] text-white overflow-hidden pointer-events-none">
            <!-- Fluid Wave Kurva Ombak Background -->
            <div class="absolute inset-0 opacity-20">
                <svg class="w-full h-full" viewBox="0 0 400 600" fill="none" preserveAspectRatio="none">
                    <path d="M-20 60 C80 120 180 0 300 80 C360 120 420 100 450 140 L450 600 L-20 600 Z" fill="white" opacity="0.3"/>
                    <path d="M-20 120 C100 200 220 80 340 180 C380 210 420 190 450 220 L450 600 L-20 600 Z" fill="white" opacity="0.4"/>
                </svg>
            </div>
        </div>

        <!-- Spacer Tinggi Hero di Atas (Compact Opsi 3: 80px) -->
        <div class="h-[80px] shrink-0 pointer-events-none"></div>

        <!-- 2. OVERLAPPING WHITE BOTTOM SHEET (Maksimal Ruang Scroll) -->
        <div class="relative z-10 bg-white rounded-t-[32px] px-5 pt-5 pb-28 shadow-2xl flex-1 min-h-[calc(100vh-80px)] space-y-4">

            @if (session('success'))
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-950 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="flex-1 leading-snug">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-950 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                    <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <span class="flex-1 leading-snug">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Segmented Tab Switcher (Modern iOS/PWA style) -->
            <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 rounded-2xl border border-slate-200/60">
                <button type="button" @click="tab = 'active'"
                        class="min-h-[42px] py-2 px-3 rounded-xl text-xs font-bold transition cursor-pointer flex items-center justify-center space-x-2"
                        :class="tab === 'active' ? 'bg-white text-emerald-800 shadow-xs border border-slate-200/50' : 'text-slate-500 hover:text-slate-800'">
                    <span>Pesanan Berjalan</span>
                    @if ($activeOrders->isNotEmpty())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                            {{ $activeOrders->count() }}
                        </span>
                    @endif
                </button>
                <button type="button" @click="tab = 'history'"
                        class="min-h-[42px] py-2 px-3 rounded-xl text-xs font-bold transition cursor-pointer flex items-center justify-center space-x-2"
                        :class="tab === 'history' ? 'bg-white text-emerald-800 shadow-xs border border-slate-200/50' : 'text-slate-500 hover:text-slate-800'">
                    <span>Selesai & Batal</span>
                    @if ($pastOrders->isNotEmpty())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                            {{ $pastOrders->count() }}
                        </span>
                    @endif
                </button>
            </div>

            <!-- Tab 1: Pesanan Berjalan -->
            <div x-show="tab === 'active'" class="space-y-3">
                @forelse ($activeOrders as $order)
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-slate-300 transition space-y-3">
                        
                        <!-- Header Bar: Order Code, Type & Status Badge -->
                        <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
                            <div class="flex items-center space-x-2 text-xs">
                                <span class="font-mono text-slate-400 font-bold text-[11px]">{{ $order->order_code }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="font-medium text-slate-600">
                                    @if ($order->order_type->value === 'instan')
                                        <span class="text-emerald-700 font-semibold">Pesanan Instan</span>
                                    @else
                                        {{ $order->local_scheduled_start_time?->translatedFormat('d M Y, H:i') }} {{ $order->timezone_abbr }}
                                    @endif
                                </span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold shrink-0 {{ $order->status->badgeClasses() }}">
                                {{ $order->status->label() }}
                            </span>
                        </div>

                        <!-- Main Info: Service Title & Inline Meta -->
                        <div class="space-y-1.5">
                            <h4 class="text-sm font-extrabold text-slate-900 leading-snug">
                                {{ $order->service_name }}
                            </h4>

                            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs text-slate-500">
                                <span class="inline-flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $order->duration_minutes }} Menit</span>
                                </span>
                                <span class="text-slate-300">•</span>
                                <span class="inline-flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>Terapis: {{ ucfirst($order->preferred_gender ?? 'Bebas') }}</span>
                                </span>
                                <span class="text-slate-300">•</span>
                                <span>Untuk: <strong class="text-slate-700 font-semibold">{{ $order->recipient_name }}</strong></span>
                            </div>
                        </div>

                        <!-- Footer: Price & Actions -->
                        <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between gap-3">
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-semibold">Total Tagihan</span>
                                <span class="text-base font-black text-slate-900 font-mono leading-tight">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex items-center space-x-2">
                                @if ($order->status === \App\Enums\OrderStatus::MenungguPembayaran)
                                    <button type="button" @click="openCancelModal({{ $order->id }}, '{{ $order->order_code }}', false)"
                                            class="min-h-[40px] px-3 py-1.5 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 text-xs font-semibold transition cursor-pointer">
                                        <span>Batalkan</span>
                                    </button>
                                    <a href="{{ route('pelanggan.orders.show', $order) }}" 
                                       class="min-h-[40px] px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:scale-95 text-white font-bold text-xs shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                                        <span>Bayar Sekarang</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @elseif ($order->status === \App\Enums\OrderStatus::MencariMitra)
                                    <button type="button" @click="openCancelModal({{ $order->id }}, '{{ $order->order_code }}', true)"
                                            class="min-h-[40px] px-3 py-1.5 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 text-xs font-semibold transition cursor-pointer">
                                        <span>Batalkan</span>
                                    </button>
                                    <a href="{{ route('pelanggan.orders.show', $order) }}" 
                                       class="min-h-[40px] px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-xs shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                                        <span>Lacak Terapis</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @else
                                    <a href="{{ route('pelanggan.orders.show', $order) }}" 
                                       class="min-h-[40px] px-3.5 py-2 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 font-semibold text-xs transition flex items-center space-x-1 cursor-pointer">
                                        <span>Lihat Rincian</span>
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-slate-50/70 rounded-3xl p-8 border border-slate-200/80 text-center space-y-4 shadow-xs my-2">
                        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100/80 text-emerald-700 flex items-center justify-center shadow-xs">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-slate-900 tracking-tight">Tidak Ada Pesanan Berjalan</h4>
                            <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto leading-relaxed">Saat ini Anda tidak memiliki pesanan aktif yang sedang diproses.</p>
                        </div>
                        <a href="{{ route('pelanggan.catalog.index') }}" 
                           class="inline-flex items-center justify-center min-h-[44px] px-6 py-2.5 rounded-full bg-emerald-700 hover:bg-emerald-800 active:scale-95 text-white font-bold text-xs shadow-md shadow-emerald-700/20 transition cursor-pointer">
                            <span>Pesan Layanan Sekarang</span>
                            <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Tab 2: Selesai & Batal -->
            <div x-show="tab === 'history'" class="space-y-3" style="display: none;">
                @forelse ($pastOrders as $order)
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-2.5">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <div class="flex items-center space-x-2 text-xs">
                                <span class="font-mono text-slate-400 font-bold text-[11px]">{{ $order->order_code }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-500 font-medium">{{ $order->created_at?->translatedFormat('d M Y') }}</span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold shrink-0 {{ $order->status->badgeClasses() }}">
                                {{ $order->status->label() }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-extrabold text-slate-900 leading-snug">{{ $order->service_name }}</h4>
                                <span class="text-xs text-slate-500">Penerima: {{ $order->recipient_name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black font-mono text-slate-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        @if ($order->cancellation_reason)
                            <div class="p-2.5 bg-rose-50/70 rounded-xl border border-rose-200/60 text-[11px] text-rose-800">
                                <strong>Alasan Batal:</strong> {{ $order->cancellation_reason }}
                            </div>
                        @endif

                        <div class="pt-1">
                            <a href="{{ route('pelanggan.orders.show', $order) }}" 
                               class="w-full min-h-[38px] py-2 px-3 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                <span>Lihat Rincian & Nota</span>
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="bg-slate-50/70 rounded-3xl p-8 border border-slate-200/80 text-center space-y-3 shadow-xs my-2">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Belum ada riwayat pesanan yang selesai atau dibatalkan.</p>
                    </div>
                @endforelse
            </div>

            <!-- Modal Dialog Konfirmasi Pembatalan -->
            <div x-show="showCancelModal" style="display: none;"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div @click.away="showCancelModal = false" class="bg-white rounded-3xl max-w-sm w-full p-5 space-y-4 shadow-2xl border border-slate-200">
                    <div class="w-12 h-12 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="text-center">
                        <h4 class="text-sm font-extrabold text-slate-900">Batalkan Pesanan?</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Apakah Anda yakin ingin membatalkan pesanan <span class="font-mono font-bold text-slate-900" x-text="cancelOrderCode"></span>?
                        </p>
                        <p x-show="cancelHasRefund" class="text-[11px] text-emerald-700 font-semibold mt-1">
                            Dana pembayaran akan dikembalikan 100% ke saldo dompet Anda.
                        </p>
                    </div>
                    <form :action="'/pelanggan/orders/' + cancelOrderId + '/cancel'" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-left text-[11px] font-semibold text-slate-700 mb-1">Alasan Pembatalan (Opsional):</label>
                            <input type="text" name="reason" placeholder="Contoh: Ingin ganti waktu / paket..."
                                   class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 py-2">
                        </div>
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button type="button" @click="showCancelModal = false"
                                    class="min-h-[44px] py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50 transition cursor-pointer">
                                Kembali
                            </button>
                            <button type="submit"
                                    class="min-h-[44px] py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                Ya, Batalkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </div>
</x-pwa-layout>
