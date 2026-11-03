<x-pwa-layout title="Detail Tiket Aduan - Pijat.In" headerTitle="Status Aduan" :backUrl="route('pelanggan.complaints.index')">
    <div class="py-2 space-y-4">
        <!-- Status Card -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">Nomor Tiket</span>
                    <h2 class="text-sm font-extrabold text-slate-900 font-mono">{{ $complaint->complaint_code }}</h2>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $complaint->status->badgeClasses() }}">
                    {{ $complaint->status->label() }}
                </span>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1.5 text-xs">
                <div class="flex items-center justify-between text-slate-600">
                    <span>Kategori:</span>
                    <strong class="text-slate-900">{{ $complaint->category_label }}</strong>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span>Kode Pesanan:</span>
                    <a href="{{ route('pelanggan.orders.show', $complaint->order_id) }}" class="font-mono font-bold text-emerald-700 hover:underline">
                        {{ $complaint->order?->order_code }}
                    </a>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span>Tanggal Aduan:</span>
                    <span class="text-slate-700">{{ $complaint->created_at->translatedFormat('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Complaint Content -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Uraian Aduan</h3>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-slate-900">{{ $complaint->title }}</h4>
                <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{{ $complaint->description }}</p>
            </div>

            @if (!empty($complaint->evidence_photos))
                <div class="pt-2 border-t border-slate-100 space-y-2">
                    <span class="text-xs font-bold text-slate-700 block">Bukti Foto:</span>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($complaint->evidence_photos as $photo)
                            <a href="{{ asset('storage/' . $photo) }}" target="_blank" class="block aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                                <img src="{{ asset('storage/' . $photo) }}" class="w-full h-full object-cover" alt="Bukti aduan">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Investigation Result if Resolved -->
        @if ($complaint->isResolved() || $complaint->isRejected())
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 space-y-2">
                <div class="flex items-center space-x-2 text-emerald-900">
                    <svg class="w-5 h-5 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-sm font-extrabold text-emerald-950">Keputusan Hasil Investigasi Admin</h3>
                </div>
                <div class="p-3 bg-white/80 rounded-xl border border-emerald-100 space-y-1 text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Bentuk Putusan:</span>
                        <strong class="text-emerald-900 font-bold capitalize">{{ str_replace('_', ' ', $complaint->resolution_type) }}</strong>
                    </div>
                    @if ($complaint->admin_notes)
                        <div class="pt-1.5 border-t border-slate-100">
                            <span class="text-slate-500 block text-[11px] font-semibold">Catatan Admin:</span>
                            <p class="text-slate-800 mt-0.5 leading-relaxed">{{ $complaint->admin_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5 flex items-center space-x-2 text-xs text-amber-900">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                <span>Tiket aduan sedang dalam antrean pemeriksaan oleh Admin Wilayah.</span>
            </div>
        @endif

        <a href="{{ route('pelanggan.complaints.index') }}"
            class="w-full min-h-[42px] py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center justify-center">
            <span>Kembali ke Riwayat Aduan</span>
        </a>
    </div>
</x-pwa-layout>

