<x-pwa-layout title="Pesanan - Pijat.In" headerTitle="Pesanan" :headerSubtitle="false" :backUrl="route('pelanggan.dashboard')">
    <div class="space-y-4 py-1"
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

        @if (session('success'))
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs font-medium flex items-center gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-900 text-xs font-medium flex items-center gap-2.5">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Segmented Tab Switcher -->
        <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-200/60 rounded-xl">
            <button type="button" @click="tab = 'active'"
                    class="py-2 px-3 rounded-lg text-xs font-bold transition cursor-pointer flex items-center justify-center gap-1.5"
                    :class="tab === 'active' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-800'">
                <span>Berjalan</span>
                @if ($activeOrders->isNotEmpty())
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                        {{ $activeOrders->count() }}
                    </span>
                @endif
            </button>
            <button type="button" @click="tab = 'history'"
                    class="py-2 px-3 rounded-lg text-xs font-bold transition cursor-pointer flex items-center justify-center gap-1.5"
                    :class="tab === 'history' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-800'">
                <span>Riwayat</span>
                @if ($pastOrders->isNotEmpty())
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200/80 text-slate-600">
                        {{ $pastOrders->count() }}
                    </span>
                @endif
            </button>
        </div>

        <!-- Tab 1: Pesanan Berjalan -->
        <div x-show="tab === 'active'" class="space-y-3">
            @forelse ($activeOrders as $order)
                <div data-order-code="{{ $order->order_code }}"
                     class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $order->status->textColorClass() }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ str_replace('animate-pulse', '', $order->status->dotClass()) }}"></span>
                            <span>{{ $order->status->label() }}</span>
                        </span>
                        <span class="text-[11px] text-slate-400">
                            {{ $order->created_at?->translatedFormat('d M Y, H:i') }}
                        </span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-800 truncate">
                                {{ $order->service_name }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">
                                {{ $order->order_type->value === 'instan' ? 'Instan' : $order->local_scheduled_start_time?->translatedFormat('d M, H:i') . ' ' . $order->timezone_abbr }}
                                &bull; {{ $order->duration_minutes }} mnt
                                &bull; {{ $order->recipient_name }}
                            </p>
                        </div>
                        <span class="text-sm font-bold font-mono text-slate-800 shrink-0">
                            Rp {{ number_format($order->total_price, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-end gap-2">
                        @if ($order->status === \App\Enums\OrderStatus::MenungguPembayaran)
                            <button type="button" @click="openCancelModal({{ $order->id }}, '{{ $order->order_code }}', false)"
                                    class="px-3 py-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 text-xs font-semibold transition cursor-pointer">
                                Batalkan
                            </button>
                            <a href="{{ route('pelanggan.orders.show', $order) }}"
                               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                                Bayar Sekarang
                            </a>
                        @elseif ($order->status === \App\Enums\OrderStatus::MencariMitra)
                            <button type="button" @click="openCancelModal({{ $order->id }}, '{{ $order->order_code }}', true)"
                                    class="px-3 py-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 text-xs font-semibold transition cursor-pointer">
                                Batalkan
                            </button>
                            <a href="{{ route('pelanggan.orders.show', $order) }}"
                               class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                                Lacak Terapis
                            </a>
                        @else
                            <a href="{{ route('pelanggan.orders.show', $order) }}"
                               class="px-3.5 py-2 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 font-semibold text-xs transition cursor-pointer">
                                Detail Pesanan
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs text-center space-y-3">
                    <p class="text-sm font-bold text-slate-800">Belum ada pesanan berjalan</p>
                    <a href="{{ route('pelanggan.catalog.index') }}"
                       class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                        Pesan Layanan
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Tab 2: Riwayat -->
        <div x-show="tab === 'history'" class="space-y-2.5" style="display: none;">
            @forelse ($pastOrders as $order)
                <a href="{{ route('pelanggan.orders.show', $order) }}"
                   data-order-code="{{ $order->order_code }}"
                   class="block bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-emerald-300 transition group space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $order->status->textColorClass() }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ str_replace('animate-pulse', '', $order->status->dotClass()) }}"></span>
                            <span>{{ $order->status->label() }}</span>
                        </span>
                        <span class="text-[11px] text-slate-400">{{ $order->created_at?->translatedFormat('d M Y, H:i') }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-800 group-hover:text-emerald-600 transition truncate">
                                {{ $order->service_name }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $order->recipient_name }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-sm font-bold font-mono text-slate-800">
                                Rp {{ number_format($order->total_price, 0, ',', '.') }}
                            </span>
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs text-center">
                    <p class="text-xs text-slate-500">Belum ada riwayat pesanan.</p>
                </div>
            @endforelse
        </div>

        <!-- Modal Dialog Konfirmasi Pembatalan -->
        <div x-show="showCancelModal" style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showCancelModal = false" class="bg-white rounded-3xl max-w-sm w-full p-6 space-y-4 shadow-2xl border border-slate-200/90">
                <div class="w-12 h-12 mx-auto rounded-full bg-rose-100 text-rose-600 ring-8 ring-rose-50 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="text-center">
                    <h4 class="text-sm font-bold text-slate-800">Batalkan Pesanan?</h4>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Batalkan pesanan <span class="font-mono font-bold text-slate-800" x-text="cancelOrderCode"></span>?
                    </p>
                    <p x-show="cancelHasRefund" class="text-[11px] text-emerald-700 font-semibold mt-1">
                        Dana akan dikembalikan 100% ke saldo dompet Anda.
                    </p>
                </div>
                <form :action="'/pelanggan/orders/' + cancelOrderId + '/cancel'" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-left text-xs font-semibold text-slate-700 mb-1">Alasan (Opsional)</label>
                        <input type="text" name="reason" placeholder="Contoh: Ganti waktu atau paket"
                               class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2">
                    </div>
                    <div class="grid grid-cols-2 gap-2.5 pt-1">
                        <button type="button" @click="showCancelModal = false"
                                class="w-full py-2.5 px-3 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200/80 rounded-xl transition cursor-pointer">
                            Kembali
                        </button>
                        <button type="submit"
                                class="w-full py-2.5 px-3 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition cursor-pointer">
                            Ya, Batalkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-pwa-layout>
