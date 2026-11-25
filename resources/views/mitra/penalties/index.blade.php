<x-pwa-layout title="Status Sanksi & Poin Pelanggaran - Pijat.In" headerTitle="Sanksi & Poin" :backUrl="route('mitra.profile')">
    <div x-data="{ showRulesModal: false, showAppealModal: false }" class="py-2 space-y-4">
        @php
            $pts = (int) $user->penalty_points;
            $maxPts = 150;
            $percentage = min(100, max(0, round(($pts / $maxPts) * 100)));

            $tierInfo = match (true) {
                $pts >= 150 => [
                    'label' => 'Akun Diblokir Permanen',
                    'dotColor' => 'bg-rose-600',
                    'barColor' => 'bg-rose-600',
                ],
                $pts >= 100 => [
                    'label' => 'Penangguhan 7 Hari',
                    'dotColor' => 'bg-rose-500',
                    'barColor' => 'bg-rose-500',
                ],
                $pts >= 60 => [
                    'label' => 'Penangguhan 3 Hari',
                    'dotColor' => 'bg-amber-500',
                    'barColor' => 'bg-amber-500',
                ],
                $pts >= 30 => [
                    'label' => 'Peringatan Sistem',
                    'dotColor' => 'bg-amber-400',
                    'barColor' => 'bg-amber-400',
                ],
                default => [
                    'label' => 'Akun Sehat & Normal',
                    'dotColor' => 'bg-emerald-500',
                    'barColor' => 'bg-emerald-600',
                ],
            };
        @endphp

        <!-- Kartu Indikator Performa & Poin Sanksi -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Poin Sanksi</span>
                        <!-- Tombol Icon Tanda Seru Proporsional & Mudah Ditekan -->
                        <button type="button" @click="showRulesModal = true"
                                class="w-6 h-6 rounded-full border border-slate-300 text-slate-600 hover:text-emerald-700 hover:border-emerald-600 flex items-center justify-center transition cursor-pointer"
                                title="Lihat Aturan Ambang Batas Sanksi"
                                aria-label="Lihat Aturan Ambang Batas Sanksi">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-slate-900 font-mono tracking-tight">{{ $pts }}</span>
                        <span class="text-xs font-semibold text-slate-400">/ {{ $maxPts }} Poin Maksimal</span>
                    </div>
                </div>

                <!-- Status Akun (Dot indicator, polos tanpa background warna) -->
                <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-800 shrink-0 pt-0.5">
                    <span class="w-2 h-2 rounded-full {{ $tierInfo['dotColor'] }}"></span>
                    <span>{{ $tierInfo['label'] }}</span>
                </div>
            </div>

            <!-- Visual Stepped Progress Bar -->
            <div class="space-y-1.5 pt-1">
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden p-0.5 border border-slate-200/60">
                    <div class="{{ $tierInfo['barColor'] }} h-full rounded-full transition-all duration-500"
                         style="width: {{ max(4, $percentage) }}%"></div>
                </div>
                <div class="flex justify-between text-[10px] font-medium text-slate-400 px-0.5">
                    <span>0 (Normal)</span>
                    <span>30 (Peringatan)</span>
                    <span>60 (3 Hari)</span>
                    <span>100 (7 Hari)</span>
                    <span>150 (Blokir)</span>
                </div>
            </div>

            <!-- Alert Penangguhan Jika Ada (Tanpa background warna mencolok) -->
            @if ($user->suspended_until && $user->suspended_until->isFuture())
                <div class="p-3.5 rounded-xl border border-slate-200 text-xs text-slate-700 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="leading-relaxed">
                        <strong class="font-semibold text-slate-900">Akun Sedang Ditangguhkan</strong>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Masa penangguhan berlaku sampai <strong>{{ $user->suspended_until->translatedFormat('d M Y, H:i') }} WIB</strong>. Anda tidak dapat menerima pesanan baru selama periode ini.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Info Pemulihan / Ajukan Banding -->
            <div class="pt-3 border-t border-slate-100">
                @if ($pendingAppeal)
                    <!-- Kartu Status Banding Sederhana (Polos tanpa background warna pada badge/teks) -->
                    <div @click="showAppealModal = true"
                         class="p-3 bg-white hover:bg-slate-50 border border-slate-200/90 rounded-xl flex items-center justify-between gap-3 cursor-pointer transition select-none group">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-slate-900">Pengajuan Banding</h4>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                        Sedang Ditinjau
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5">Ketuk untuk melihat detail pengajuan</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                @elseif ($pts > 0)
                    <!-- Tombol Ajukan Banding -->
                    <div class="space-y-2">
                        <a href="{{ route('mitra.penalties.appeal.create') }}"
                           class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Ajukan Banding Surat Pernyataan</span>
                        </a>
                        <p class="text-[11px] text-slate-400 text-center leading-relaxed">
                            Memiliki alasan force majeure atau kendala darurat? Anda dapat mengajukan pemulihan poin kepada Admin Wilayah.
                        </p>
                    </div>
                @else
                    <!-- Catatan Positif untuk Mitra Tanpa Sanksi -->
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Setiap ulasan bintang 5 dari pelanggan otomatis memulihkan 1 poin sanksi jika ada riwayat penalti.</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Riwayat Catatan Poin Sanksi -->
        <div class="space-y-2.5">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Riwayat Catatan Poin</h3>
                <span class="text-[11px] text-slate-400">{{ $ledgers->total() }} catatan</span>
            </div>

            @if ($ledgers->isEmpty())
                <div class="bg-white rounded-2xl p-7 border border-slate-200/90 text-center space-y-2">
                    <h4 class="text-xs font-semibold text-slate-800">Catatan Poin Bersih</h4>
                    <p class="text-[11px] text-slate-500 max-w-xs mx-auto leading-relaxed">
                        Belum ada catatan penalti. Terima kasih telah menjaga kualitas layanan dan mematuhi SOP kerja dengan baik!
                    </p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($ledgers as $ledger)
                        <div class="bg-white rounded-2xl p-3.5 border border-slate-200/90 shadow-2xs flex items-center justify-between gap-3 text-xs">
                            <div class="space-y-0.5 min-w-0 flex-1">
                                <span class="font-semibold text-slate-900 leading-snug block">{{ $ledger->reason }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $ledger->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </div>
                            
                            <!-- Poin (Polos tanpa background warna, tipografi bersih) -->
                            <div class="shrink-0 text-right">
                                @if ($ledger->points > 0)
                                    <span class="font-mono font-bold text-sm text-rose-600">
                                        +{{ $ledger->points }}
                                    </span>
                                @else
                                    <span class="font-mono font-bold text-sm text-emerald-700">
                                        {{ $ledger->points }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-2">
                        {{ $ledgers->links() }}
                    </div>
                </div>
            @endif
        </div>

        <!-- 1. MODAL ATURAN AMBANG BATAS SANKSI (Bersih, tanpa kotak-kotak bersarang) -->
        <div x-show="showRulesModal"
             style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
             @keydown.escape.window="showRulesModal = false">
            <div class="bg-white rounded-3xl max-w-sm w-full p-5 space-y-4 shadow-2xl border border-slate-200"
                 @click.away="showRulesModal = false">
                
                <!-- Header Modal -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Aturan Ambang Batas</h3>
                    <button type="button" @click="showRulesModal = false"
                            class="w-7 h-7 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Daftar Ambang Batas Polos & Rapi -->
                <div class="divide-y divide-slate-100 text-xs">
                    <div class="py-2.5 first:pt-0">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">30 Poin Pelanggaran</span>
                            <span class="font-medium text-slate-600">Peringatan Sistem</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                            Peringatan resmi pada akun mitra. Akun tetap dapat menerima pesanan dan beroperasi normal.
                        </p>
                    </div>

                    <div class="py-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">60 Poin Pelanggaran</span>
                            <span class="font-medium text-slate-600">Penangguhan 3 Hari</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                            Akun ditangguhkan sementara selama 3 hari kalender dari penugasan pesanan.
                        </p>
                    </div>

                    <div class="py-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">100 Poin Pelanggaran</span>
                            <span class="font-medium text-slate-600">Penangguhan 7 Hari</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                            Akun ditangguhkan selama 7 hari kalender. Diwajibkan mengajukan permohonan banding pemulihan.
                        </p>
                    </div>

                    <div class="py-2.5 last:pb-0">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">150 Poin Pelanggaran</span>
                            <span class="font-medium text-slate-600">Pemutusan Kemitraan</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                            Batas maksimal pelanggaran tercapai. Akun dinonaktifkan secara permanen dari Pijat.In.
                        </p>
                    </div>
                </div>

                <!-- Catatan Pemulihan Poin -->
                <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-500 leading-relaxed">
                    Setiap pesanan yang diselesaikan dengan ulasan bintang 5 otomatis memulihkan 1 poin sanksi.
                </div>

                <!-- Tombol Tutup -->
                <button type="button" @click="showRulesModal = false"
                        class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>

        <!-- 2. MODAL DETAIL PENGAJUAN BANDING (Bersih tanpa kotak-kotak berwarna) -->
        @if ($pendingAppeal)
            <div x-show="showAppealModal"
                 style="display: none;"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
                 @keydown.escape.window="showAppealModal = false">
                <div class="bg-white rounded-3xl max-w-sm w-full p-5 space-y-4 shadow-2xl border border-slate-200"
                     @click.away="showAppealModal = false">
                    
                    <!-- Header Modal -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Detail Pengajuan Banding</h3>
                            <span class="text-[11px] text-slate-400">Diajukan pada {{ $pendingAppeal->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                        </div>
                        <button type="button" @click="showAppealModal = false"
                                class="w-7 h-7 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Status Review Polos -->
                    <div class="text-xs text-slate-700 space-y-1">
                        <div class="flex items-center gap-1.5 font-semibold text-slate-900">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span>Menunggu Peninjauan Admin Wilayah</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Dokumen surat pernyataan Anda sedang dalam antrean verifikasi. Keputusan peninjauan akan dikirim melalui notifikasi.
                        </p>
                    </div>

                    <!-- Alasan yang Diajukan -->
                    <div class="space-y-1 text-xs pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Kronologi / Alasan</span>
                        <p class="text-slate-700 leading-relaxed text-xs whitespace-pre-line max-h-36 overflow-y-auto">
                            {{ $pendingAppeal->reason }}
                        </p>
                    </div>

                    <!-- Berkas Surat Pernyataan -->
                    <div class="space-y-2 text-xs pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Berkas Terlampir</span>
                        
                        @if ($pendingAppeal->statement_letter_path)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($pendingAppeal->statement_letter_path) }}" target="_blank"
                               class="py-1 text-slate-700 hover:text-emerald-700 flex items-center justify-between transition cursor-pointer">
                                <span class="truncate text-xs font-medium">Surat Pernyataan Banding</span>
                                <span class="text-[11px] font-semibold text-emerald-700 shrink-0">Buka Berkas &rarr;</span>
                            </a>
                        @endif

                        @if ($pendingAppeal->evidence_photos && count($pendingAppeal->evidence_photos) > 0)
                            <div class="flex gap-2 pt-1">
                                @foreach ($pendingAppeal->evidence_photos as $photo)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($photo) }}" target="_blank" class="w-14 h-14 rounded-lg overflow-hidden border border-slate-200 shrink-0 hover:opacity-80 transition">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($photo) }}" alt="Bukti Pendukung" class="w-full h-full object-cover">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Tombol Tutup -->
                    <button type="button" @click="showAppealModal = false"
                            class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        @endif
    </div>
</x-pwa-layout>
