<x-pwa-layout title="Detail Pesanan - Pijat.In" headerTitle="Detail Pesanan" :backUrl="route('pelanggan.dashboard')">
    <div class="py-2 space-y-4">
        
        <!-- Status Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nomor Pesanan</span>
                    <h2 class="text-sm font-extrabold text-slate-900 font-mono">{{ $order->order_code }}</h2>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    {{ $order->status->label() }}
                </span>
            </div>

            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-950 flex items-center justify-between">
                <span class="font-medium">Total Tagihan:</span>
                <span class="text-base font-black font-mono text-emerald-900">
                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                </span>
            </div>
        </div>

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
                <span class="text-slate-500">Jenis Kelamin:</span>
                <span class="font-bold text-emerald-800 capitalize">
                    {{ $order->recipient_gender }} (Mitra Wajib Sesama Gender)
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
                class="w-full min-h-[48px] py-3.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center space-x-2">
                <span>Kembali ke Beranda</span>
            </a>
        </div>

    </div>
</x-pwa-layout>

