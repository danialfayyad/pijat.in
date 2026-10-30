<x-pwa-layout title="Pelacakan Pesanan - Pijat.In" headerTitle="Pelacakan Pesanan" :backUrl="route('pelanggan.dashboard')">
    <div class="py-2 space-y-4">
        
        <!-- Status Card with Tracker (CUS-10) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">Kode Pesanan</span>
                    <h2 class="text-sm font-extrabold text-slate-900 font-mono">{{ $order->order_code }}</h2>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $order->status->badgeClasses() }}">
                    @if ($order->order_type->value === 'terjadwal' && $order->status === \App\Enums\OrderStatus::MencariMitra)
                        Bursa Mitra
                    @else
                        {{ $order->status->label() }}
                    @endif
                </span>
            </div>

            <!-- Visual Progress Tracker -->
            @php
                $statusSteps = [
                    \App\Enums\OrderStatus::MenungguPembayaran->value => 1,
                    \App\Enums\OrderStatus::MencariMitra->value => 2,
                    \App\Enums\OrderStatus::Dijadwalkan->value => 3,
                    \App\Enums\OrderStatus::MenujuLokasi->value => 4,
                    \App\Enums\OrderStatus::Tiba->value => 5,
                    \App\Enums\OrderStatus::SedangBerlangsung->value => 6,
                    \App\Enums\OrderStatus::Selesai->value => 7,
                ];
                $currentStep = $statusSteps[$order->status->value] ?? 1;
            @endphp

            <div class="pt-2">
                <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-2">
                    <span class="{{ $currentStep >= 2 ? 'text-emerald-700' : '' }}">
                        {{ $order->order_type->value === 'terjadwal' ? 'Bursa Mitra' : 'Cari Mitra' }}
                    </span>
                    <span class="{{ $currentStep >= 3 ? 'text-emerald-700' : '' }}">Dijadwalkan</span>
                    <span class="{{ $currentStep >= 4 ? 'text-emerald-700' : '' }}">Menuju Lokasi</span>
                    <span class="{{ $currentStep >= 7 ? 'text-emerald-700' : '' }}">Selesai</span>
                </div>
                <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-emerald-600 h-full rounded-full transition-all duration-500" 
                         style="width: {{ min(100, max(15, ($currentStep / 7) * 100)) }}%"></div>
                </div>
            </div>

            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-950 flex items-center justify-between">
                <span class="font-medium">Total Tagihan:</span>
                <span class="text-base font-black font-mono text-emerald-900">
                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- 0. Kondisi: Menunggu Pembayaran (CUS-09) -->
        @if ($order->status === \App\Enums\OrderStatus::MenungguPembayaran)
            @php
                $userWallet = auth()->user()->getOrCreateWallet();
                $hasSufficient = (float) $userWallet->balance >= (float) $order->total_price;
                $deficit = (float) $order->total_price - (float) $userWallet->balance;
            @endphp
            <div class="bg-white rounded-2xl p-5 border border-amber-200 shadow-xs space-y-4">
                <div class="flex items-center space-x-3 text-amber-900">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 leading-tight">Konfirmasi Pembayaran Pesanan</h3>
                        <p class="text-xs text-slate-600 mt-0.5">Selesaikan pembayaran saldo untuk mulai mencari mitra terapis.</p>
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Saldo Dompet Anda:</span>
                        <span class="font-extrabold text-slate-900 font-mono">{{ $userWallet->formatted_balance }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Total Tagihan:</span>
                        <span class="font-extrabold text-slate-900 font-mono">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                    </div>
                    @if (! $hasSufficient)
                        <div class="flex items-center justify-between text-rose-600 pt-1 border-t border-slate-200/60 font-semibold">
                            <span>Kekurangan Saldo:</span>
                            <span class="font-bold font-mono">Rp {{ number_format($deficit, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <div class="space-y-2">
                    @if ($hasSufficient)
                        <form method="POST" action="{{ route('pelanggan.orders.pay', $order) }}">
                            @csrf
                            <button type="submit"
                                class="w-full min-h-[44px] py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                                <span>Bayar Sekarang ({{ $userWallet->formatted_balance }})</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('pelanggan.wallet.topup') }}"
                            class="w-full min-h-[44px] py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                            <span>Isi Saldo Dompet (+Rp {{ number_format($deficit, 0, ',', '.') }})</span>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('pelanggan.orders.cancel', $order) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                        @csrf
                        <button type="submit"
                            class="w-full min-h-[44px] py-2.5 rounded-xl border border-slate-200 hover:border-rose-300 hover:bg-rose-50 text-slate-600 hover:text-rose-700 font-bold text-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Batalkan Pesanan Ini</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- 1A. Kondisi: Pesanan Instan - Sedang Mencari Mitra Terdekat (CUS-10: Hitung Mundur 30 Menit) -->
        @if ($order->status === \App\Enums\OrderStatus::MencariMitra && $order->order_type->value === 'instan')
            @php
                $searchDeadline = $order->created_at->addMinutes(30)->timestamp;
            @endphp
            <div class="bg-gradient-to-br from-blue-900 via-indigo-900 to-slate-900 rounded-2xl p-5 text-white shadow-sm space-y-3"
                 x-data="{
                    deadline: {{ $searchDeadline }},
                    minutes: 0,
                    seconds: 0,
                    timer: null,
                    updateCountdown() {
                        const now = Math.floor(Date.now() / 1000);
                        const diff = Math.max(0, this.deadline - now);
                        this.minutes = Math.floor(diff / 60);
                        this.seconds = diff % 60;
                    },
                    init() {
                        this.updateCountdown();
                        this.timer = setInterval(() => this.updateCountdown(), 1000);
                    }
                 }">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-400 animate-ping"></span>
                    <span class="text-xs font-bold text-blue-200 uppercase tracking-wider">Mencari Terapis Instan</span>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-white">Sistem Sedang Menghubungkan Terapis</h3>
                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                        Mencari mitra terapis berlisensi terdekat di wilayah operasional Anda yang siap berangkat saat ini.
                    </p>
                </div>

                <div class="p-3.5 bg-white/10 rounded-xl border border-white/15 flex items-center justify-between">
                    <span class="text-xs text-slate-200 font-medium">Batas Waktu Pencarian:</span>
                    <div class="text-lg font-black font-mono text-amber-300 tracking-wider">
                        <span x-text="String(minutes).padStart(2, '0')"></span>:<span x-text="String(seconds).padStart(2, '0')"></span>
                    </div>
                </div>

                <form method="POST" action="{{ route('pelanggan.orders.cancel', $order) }}" onsubmit="return confirm('Batalkan pencarian mitra? Saldo dompet sebesar Rp {{ number_format($order->total_price, 0, ',', '.') }} akan segera dikembalikan 100% ke dompet Anda.');" class="pt-1">
                    @csrf
                    <button type="submit"
                        class="w-full min-h-[44px] py-2.5 rounded-xl bg-white/10 hover:bg-rose-600/80 border border-white/20 text-white font-bold text-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Batalkan Pencarian (Pengembalian Dana 100%)</span>
                    </button>
                </form>
            </div>
        @endif

        <!-- 1B. Kondisi: Pesanan Terjadwal - Menunggu Mitra Mengambil Jadwal di Bursa (MIT-06) -->
        @if ($order->status === \App\Enums\OrderStatus::MencariMitra && $order->order_type->value === 'terjadwal')
            <div class="bg-gradient-to-br from-emerald-950 via-teal-900 to-slate-900 rounded-2xl p-5 text-white shadow-sm space-y-3.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-bold text-emerald-200 uppercase tracking-wider">Bursa Jadwal Mitra</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/15 text-emerald-200 border border-white/20">
                        Pesanan Terjadwal
                    </span>
                </div>

                <div>
                    <h3 class="text-base font-extrabold text-white">Menunggu Konfirmasi Terapis</h3>
                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                        Pesanan Anda telah dipublikasikan ke Bursa Jadwal Mitra Pijat.In. Mitra terapis di wilayah Anda akan mengambil slot jadwal ini sesuai ketersediaan.
                    </p>
                </div>

                <!-- Detail Jadwal yang Dipesan -->
                <div class="p-3.5 bg-white/10 rounded-xl border border-white/15 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-300">Waktu Pelaksanaan:</span>
                        <span class="font-bold text-emerald-300 font-mono text-sm">
                            {{ $order->scheduled_start_time?->translatedFormat('d M Y, H:i') }} WIB
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-1.5 border-t border-white/10">
                        <span class="text-slate-300">Preferensi Terapis:</span>
                        <span class="font-semibold text-white capitalize">
                            {{ $order->preferred_gender === 'bebas' ? 'Siapa Saja (Pria / Wanita)' : 'Terapis ' . $order->preferred_gender }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-1.5 border-t border-white/10">
                        <span class="text-slate-300">Batas Klaim Bursa:</span>
                        <span class="font-medium text-amber-300">
                            H-2 Jam (pk. {{ $order->scheduled_start_time?->subHours(2)->translatedFormat('H:i') }} WIB)
                        </span>
                    </div>
                </div>

                <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-[11px] text-slate-300 leading-relaxed">
                    💡 Anda akan menerima notifikasi begitu mitra terapis mengonfirmasi jadwal ini. Anda juga dapat membatalkan pesanan kapan saja dengan garansi pengembalian saldo 100%.
                </div>

                <form method="POST" action="{{ route('pelanggan.orders.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan terjadwal? Saldo dompet sebesar Rp {{ number_format($order->total_price, 0, ',', '.') }} akan dikembalikan 100% ke dompet Anda.');" class="pt-1">
                    @csrf
                    <button type="submit"
                        class="w-full min-h-[44px] py-2.5 rounded-xl bg-white/10 hover:bg-rose-600/80 border border-white/20 text-white font-bold text-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Batalkan Pesanan (Pengembalian Dana 100%)</span>
                    </button>
                </form>
            </div>
        @endif

        <!-- 1C. Kondisi: Pesanan Terjadwal - Terapis Sudah Mengonfirmasi Jadwal (Dijadwalkan) -->
        @if ($order->status === \App\Enums\OrderStatus::Dijadwalkan)
            <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-emerald-950 rounded-2xl p-5 text-white shadow-sm space-y-3.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-bold text-emerald-200 uppercase tracking-wider">Jadwal Dikonfirmasi</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/15 text-emerald-200 border border-white/20">
                        {{ $order->scheduled_start_time?->diffForHumans() }}
                    </span>
                </div>

                <div>
                    <h3 class="text-base font-extrabold text-white">Terapis Siap Datang Sesuai Jadwal</h3>
                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                        Terapis telah mengonfirmasi kesediaan untuk jadwal layanan Anda pada <strong class="text-white">{{ $order->scheduled_start_time?->translatedFormat('l, d F Y pk. H:i') }} WIB</strong>. Terapis akan mulai berangkat menuju alamat Anda menjelang waktu tersebut.
                    </p>
                </div>

                <div class="p-3 bg-white/10 rounded-xl border border-white/15 flex items-center justify-between text-xs">
                    <span class="text-slate-300">Waktu Layanan:</span>
                    <span class="font-extrabold text-emerald-300 font-mono text-sm">
                        {{ $order->scheduled_start_time?->translatedFormat('d M Y, H:i') }} WIB
                    </span>
                </div>
            </div>
        @endif

        <!-- 2. Kondisi: Terapis Dalam Perjalanan (CUS-10: OTW & Estimasi Kedatangan) -->
        @if ($order->status === \App\Enums\OrderStatus::MenujuLokasi)
            <div class="bg-gradient-to-r from-purple-900 via-indigo-900 to-slate-900 rounded-2xl p-5 text-white shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-400 animate-pulse"></span>
                        <span class="text-xs font-bold text-purple-200 uppercase tracking-wider">Sedang Menuju Lokasi</span>
                    </div>
                    <span class="text-[11px] text-purple-200 font-semibold bg-white/10 px-2.5 py-1 rounded-full">GPS Live</span>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-white">Terapis Dalam Perjalanan</h3>
                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                        Terapis telah berangkat dan sedang menuju alamat Anda. Harap persiapkan tempat layanan.
                    </p>
                </div>
                <div class="p-3 bg-white/10 rounded-xl border border-white/15 flex items-center justify-between text-xs">
                    <span class="text-slate-200">Estimasi Kedatangan:</span>
                    <span class="font-extrabold text-amber-300 font-mono text-sm">~15 - 20 Menit</span>
                </div>
                <div class="text-[11px] text-purple-200/80 italic flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Posisi diperbarui setiap 30 detik</span>
                </div>
            </div>
        @endif

        <!-- 3. Profil Terapis (CUS-10: Tampil saat Dijadwalkan / OTW / Aktif, Tanpa NIK/Data Sensitif) -->
        @if ($order->mitra)
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-bold text-slate-900">Terapis Penanggung Jawab</h3>
                    <span class="text-[11px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        Terverifikasi
                    </span>
                </div>

                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-700 text-white font-extrabold text-base flex items-center justify-center shrink-0 shadow-xs">
                        {{ strtoupper(substr($order->mitra->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-extrabold text-slate-900 truncate">{{ $order->mitra->name }}</h4>
                        <div class="flex items-center space-x-2 mt-1 text-xs">
                            <span class="text-amber-600 font-bold flex items-center space-x-0.5">
                                <span>★</span>
                                <span>{{ number_format($order->mitra->partnerRating(), 1) }}</span>
                            </span>
                            <span class="text-slate-300">•</span>
                            <span class="text-slate-500 font-medium">
                                {{ $order->mitra->completedOrdersCount() }} Layanan Selesai
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Gender Terapis:</span>
                    <span class="font-bold text-slate-800 capitalize">Terapis {{ $order->mitra->gender }}</span>
                </div>

                <div class="pt-1">
                    <a href="tel:{{ $order->mitra->phone }}" 
                       class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>Hubungi Terapis</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- Detail Layanan & Waktu -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3.5 text-xs">
            <h3 class="text-xs font-bold text-slate-900 pb-2 border-b border-slate-100">Informasi Layanan</h3>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Nama Layanan:</span>
                <span class="font-bold text-slate-900">{{ $order->service_name }}</span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Durasi Pijat:</span>
                <span class="font-bold text-slate-900">{{ $order->duration_minutes }} Menit</span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Jenis Pesanan:</span>
                <span class="font-bold text-slate-900 capitalize">{{ $order->order_type->value }}</span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Waktu Layanan:</span>
                <span class="font-bold text-slate-900">
                    @if ($order->scheduled_start_time)
                        {{ $order->scheduled_start_time->translatedFormat('d M Y, H:i') }} WIB
                    @else
                        Segera (Instan)
                    @endif
                </span>
            </div>
        </div>

        <!-- Penerima Layanan (CUS-04) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3 text-xs">
            <h3 class="text-xs font-bold text-slate-900 pb-2 border-b border-slate-100">Penerima Layanan</h3>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Penerima:</span>
                <span class="font-bold text-slate-900">
                    {{ $order->recipient_name }}
                    <span class="text-[10px] text-slate-400 font-normal">({{ $order->recipient_type === 'myself' ? 'Diri Sendiri' : 'Orang Lain' }})</span>
                </span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Jenis Kelamin Penerima:</span>
                <span class="font-bold text-slate-900 capitalize">
                    {{ $order->recipient_gender }}
                </span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Preferensi Terapis:</span>
                <span class="font-bold text-emerald-800 capitalize">
                    @if(($order->preferred_gender ?? 'bebas') === 'bebas')
                        Bebas (Siapa Saja)
                    @else
                        Terapis {{ ucfirst($order->preferred_gender) }}
                    @endif
                </span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Telepon / WhatsApp:</span>
                <span class="font-bold text-slate-900 font-mono">{{ $order->recipient_phone ?? '-' }}</span>
            </div>
        </div>

        <!-- Alamat Kunjungan -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-2 text-xs">
            <h3 class="text-xs font-bold text-slate-900 pb-1 border-b border-slate-100">Alamat Kunjungan</h3>
            <p class="text-slate-700 leading-relaxed pt-1">{{ $order->service_address_text }}</p>
        </div>

        <!-- Catatan Medis & Persetujuan Risiko (CUS-07 & SYS-08) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-2.5 text-xs">
            <h3 class="text-xs font-bold text-slate-900 pb-1 border-b border-slate-100">Kepatuhan Medis & Risiko</h3>
            
            <div class="flex items-center justify-between">
                <span class="text-slate-500">Catatan Medis:</span>
                <span class="font-medium text-slate-800">
                    {{ $order->medical_notes ? 'Tercatat (Terenkripsi)' : 'Tidak ada catatan khusus' }}
                </span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Persetujuan Risiko:</span>
                <span class="font-bold text-emerald-800 font-mono">
                    {{ $order->risk_agreement_version ?? 'v1.0' }}
                </span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-slate-500">Waktu Persetujuan:</span>
                <span class="text-slate-700 font-mono text-[11px]">
                    {{ $order->consent_timestamp?->translatedFormat('d M Y, H:i:s') ?? '-' }}
                </span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-2 space-y-2">
            <a href="{{ route('pelanggan.dashboard') }}"
                class="w-full min-h-[48px] py-3.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center space-x-2 cursor-pointer">
                <span>Kembali ke Beranda</span>
            </a>
        </div>

    </div>
</x-pwa-layout>
