<x-pwa-layout title="Status Sanksi & Poin Pelanggaran - Pijat.In" headerTitle="Sanksi & Poin" :backUrl="route('pelanggan.dashboard')">
    <div class="py-2 space-y-4">
        <!-- Status Card -->
        @php
            $pts = $user->penalty_points;
            $statusLevel = match (true) {
                $pts >= 150 => ['label' => 'Diblokir Permanen', 'color' => 'bg-rose-100 text-rose-800 border-rose-300'],
                $pts >= 100 => ['label' => 'Tersuspend (7 Hari)', 'color' => 'bg-amber-100 text-amber-800 border-amber-300'],
                $pts >= 60  => ['label' => 'Tersuspend (3 Hari)', 'color' => 'bg-amber-100 text-amber-800 border-amber-300'],
                $pts >= 30  => ['label' => 'Peringatan Sistem', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-300'],
                default     => ['label' => 'Normal / Akun Baik', 'color' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
            };
        @endphp

        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Poin Sanksi</span>
                    <div class="flex items-baseline space-x-1.5 mt-0.5">
                        <span class="text-3xl font-black text-slate-900 font-mono">{{ $pts }}</span>
                        <span class="text-xs text-slate-400 font-bold">/ 150 Poin Max</span>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusLevel['color'] }}">
                    {{ $statusLevel['label'] }}
                </span>
            </div>

            @if ($user->suspended_until && $user->suspended_until->isFuture())
                <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span>Akun ditangguhkan sampai: <strong>{{ $user->suspended_until->translatedFormat('d M Y pk. H:i') }}</strong></span>
                </div>
            @endif

            <div class="pt-2 border-t border-slate-100 grid grid-cols-4 gap-1.5 text-center text-[10px] font-medium text-slate-500">
                <div class="p-1.5 rounded-lg bg-slate-50">
                    <span class="block font-bold text-slate-700">30 Pts</span>
                    <span>Peringatan</span>
                </div>
                <div class="p-1.5 rounded-lg bg-slate-50">
                    <span class="block font-bold text-slate-700">60 Pts</span>
                    <span>Suspend 3H</span>
                </div>
                <div class="p-1.5 rounded-lg bg-slate-50">
                    <span class="block font-bold text-slate-700">100 Pts</span>
                    <span>Suspend 7H</span>
                </div>
                <div class="p-1.5 rounded-lg bg-slate-50">
                    <span class="block font-bold text-slate-700">150 Pts</span>
                    <span>Blokir Akun</span>
                </div>
            </div>

            <!-- Ajukan Banding Action -->
            @if ($pts > 0)
                <div class="pt-2">
                    @if ($pendingAppeal)
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold">Pengajuan Banding {{ $pendingAppeal->appeal_code }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-200 text-blue-800">Menunggu Admin</span>
                            </div>
                            <p class="text-[11px] text-blue-700">Berkas surat pernyataan banding Anda sedang ditinjau oleh Admin Wilayah.</p>
                        </div>
                    @else
                        <a href="{{ route('pelanggan.penalties.appeal.create') }}"
                           class="w-full min-h-[42px] py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Ajukan Banding Surat Pernyataan</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Riwayat Poin Pelanggaran -->
        <div class="space-y-2">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider px-1">Riwayat Catatan Poin</h3>

            @if ($ledgers->isEmpty())
                <div class="bg-white rounded-2xl p-6 border border-slate-200/90 text-center text-xs text-slate-400">
                    Tidak ada catatan riwayat sanksi. Akun Anda dalam kondisi bersih.
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($ledgers as $ledger)
                        <div class="bg-white rounded-2xl p-3.5 border border-slate-200/90 shadow-xs flex items-center justify-between text-xs">
                            <div class="space-y-0.5 pr-2">
                                <span class="font-bold text-slate-800 block">{{ $ledger->reason }}</span>
                                <span class="text-[10px] text-slate-400">{{ $ledger->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </div>
                            <span class="font-mono font-black text-sm shrink-0 {{ $ledger->points > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                                {{ $ledger->points > 0 ? '+' . $ledger->points : $ledger->points }}
                            </span>
                        </div>
                    @endforeach

                    <div class="pt-1">
                        {{ $ledgers->links() }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Riwayat Pengajuan Banding -->
        @if ($appeals->isNotEmpty())
            <div class="space-y-2 pt-2">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider px-1">Riwayat Pengajuan Banding</h3>
                <div class="space-y-2">
                    @foreach ($appeals as $app)
                        <div class="bg-white rounded-2xl p-3.5 border border-slate-200/90 shadow-xs space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-slate-900 font-mono">{{ $app->appeal_code }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $app->status->badgeClasses() }}">
                                    {{ $app->status->label() }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-600 line-clamp-2">{{ $app->reason }}</p>
                            @if ($app->admin_notes)
                                <div class="p-2 rounded-lg bg-slate-50 border border-slate-100 text-[10px] text-slate-700">
                                    <strong>Keputusan Admin:</strong> {{ $app->admin_notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-pwa-layout>

