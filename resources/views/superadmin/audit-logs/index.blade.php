<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Log Audit Terpusat Sistem') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{ 
            detailModalOpen: false, 
            activeLog: null,
            openDetail(log) {
                this.activeLog = log;
                this.detailModalOpen = true;
            },
            getChanges() {
                if (!this.activeLog) return [];
                const oldVals = this.activeLog.old_values || {};
                const newVals = this.activeLog.new_values || {};

                const ignoredKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'password', 'email_verified_at'];
                const keys = Array.from(new Set([...Object.keys(oldVals), ...Object.keys(newVals)]))
                    .filter(k => !ignoredKeys.includes(k));

                const labels = {
                    'title': 'Judul Dokumen',
                    'version': 'Nomor Versi',
                    'summary': 'Ringkasan',
                    'content': 'Isi Teks Dokumen',
                    'key': 'Nama Parameter',
                    'value': 'Nilai',
                    'val': 'Nilai',
                    'status': 'Status Akun / Sistem',
                    'name': 'Nama Lengkap',
                    'email': 'Alamat Email',
                    'phone': 'Nomor WhatsApp / HP',
                    'points': 'Poin Penalti / Sanksi',
                    'is_active': 'Status Keaktifan',
                    'retention_days': 'Batas Waktu Retensi (Hari)',
                    'cutoff': 'Batas Waktu Kedaluwarsa',
                    'deleted_messages_count': 'Total Chat Dihapus',
                    'cleared_medical_count': 'Catatan Medis Dikosongkan'
                };

                const formatVal = (k, v) => {
                    if (v === null || v === undefined || v === '') return '—';
                    if (typeof v === 'boolean') return v ? 'Aktif' : 'Nonaktif';
                    if (k === 'content') return v ? `Naskah dokumen (${v.length} karakter)` : '—';
                    if (typeof v === 'string' && v.length > 80) return v.slice(0, 80) + '...';
                    if (typeof v === 'object') return JSON.stringify(v);
                    return String(v);
                };

                return keys.map(k => {
                    const oldV = oldVals[k] !== undefined ? oldVals[k] : null;
                    const newV = newVals[k] !== undefined ? newVals[k] : null;

                    return {
                        label: labels[k] || k.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                        from: formatVal(k, oldV),
                        to: formatVal(k, newV),
                        hasChanged: JSON.stringify(oldV) !== JSON.stringify(newV),
                        isNewOnly: (oldV === null || oldV === undefined || oldV === '') && (newV !== null && newV !== undefined && newV !== '')
                    };
                }).filter(item => item.hasChanged);
            }
         }">

        <!-- Filter Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <form method="GET" action="{{ route('superadmin.audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Modul -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Modul Sistem</label>
                    <select name="module" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2">
                        <option value="">Semua Modul</option>
                        @foreach ($modules as $mod)
                            <option value="{{ $mod }}" @selected(request('module') === $mod)>{{ ucfirst($mod) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Aktor -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Aktor (Admin / User)</label>
                    <select name="user_id" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2">
                        <option value="">Semua Aktor</option>
                        @foreach ($actors as $actor)
                            <option value="{{ $actor->id }}" @selected(request('user_id') == $actor->id)>
                                {{ $actor->name }} ({{ $actor->role->value ?? $actor->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Rentang Tanggal -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Mulai Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2">
                </div>

                <div class="flex items-center gap-2">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                               class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2">
                    </div>
                    <button type="submit" 
                            class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-xl transition shadow-xs cursor-pointer shrink-0 mt-5">
                        Filter
                    </button>
                    @if (request()->hasAny(['module', 'user_id', 'start_date', 'end_date']))
                        <a href="{{ route('superadmin.audit-logs.index') }}" 
                           class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition mt-5"
                           title="Reset Filter">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Logs Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Riwayat Rekam Jejak Audit</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Merekam setiap mutasi data sensitif, parameter, dan status sistem.</p>
                </div>
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-xl">
                    {{ $logs->total() }} Catatan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Waktu Kejadian</th>
                            <th class="px-5 py-3">Aktor</th>
                            <th class="px-5 py-3">Modul & Aksi</th>
                            <th class="px-5 py-3">Alasan Perubahan</th>
                            <th class="px-5 py-3">IP Address</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap font-mono text-[11px] text-slate-500">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($log->user)
                                        <div class="font-semibold text-slate-800">{{ $log->user->name }}</div>
                                        <div class="text-[10px] text-slate-400 capitalize">{{ $log->user->role->value ?? $log->user->role }}</div>
                                    @else
                                        <span class="font-semibold text-slate-400 italic">Sistem Otomatis</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded-md font-semibold text-[10px] bg-slate-100 text-slate-700 uppercase">
                                            {{ $log->module }}
                                        </span>
                                        <span class="text-slate-400">&bull;</span>
                                        <span class="font-medium text-slate-600">{{ $log->action }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 max-w-xs truncate text-slate-700">
                                    {{ $log->reason ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                    {{ $log->ip_address ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <button type="button" 
                                            @click="openDetail({{ json_encode($log) }})"
                                            class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline cursor-pointer">
                                        Rincian Perubahan &rarr;
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-slate-400 text-xs">
                                    Belum ada log audit yang terekam sesuai filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

        <!-- Detail Modal Dialog (Kartu Aktivitas Audit yang Bersih & Manusiawi) -->
        <div x-show="detailModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak
             style="display: none;"
             @keydown.escape.window="detailModalOpen = false">
            <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden" @click.stop>
                
                <!-- Header Modal -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            Rincian Aktivitas Audit
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5" x-text="activeLog ? 'Waktu: ' + (activeLog.created_at || '') : ''"></p>
                    </div>
                    <button type="button" @click="detailModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Body Modal -->
                <div class="p-6 space-y-5 text-xs">
                    
                    <!-- Info Pelaksana & Modul -->
                    <div class="grid grid-cols-2 gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200/70">
                        <div>
                            <span class="text-slate-400 text-[11px] block">Aktor Pelaksana</span>
                            <span class="font-semibold text-slate-800 text-xs" x-text="activeLog?.user ? activeLog.user.name : 'Sistem Otomatis'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Modul & Tindakan</span>
                            <span class="font-semibold text-slate-800 text-xs uppercase" x-text="activeLog ? activeLog.module + ' (' + activeLog.action + ')' : '-'"></span>
                        </div>
                        <div class="col-span-2 pt-2 border-t border-slate-200/60">
                            <span class="text-slate-400 text-[11px] block">Catatan Alasan Resmi</span>
                            <p class="text-slate-800 font-medium text-xs mt-0.5 leading-relaxed" x-text="activeLog?.reason || 'Tidak ada catatan alasan khusus.'"></p>
                        </div>
                    </div>

                    <!-- Perubahan Nilai Data -->
                    <div class="space-y-2.5">
                        <h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider">
                            Perubahan Nilai Data
                        </h4>

                        <template x-if="getChanges().length > 0">
                            <div class="divide-y divide-slate-100 border border-slate-200/80 rounded-xl overflow-hidden">
                                <template x-for="item in getChanges()" :key="item.label">
                                    <div class="p-3 bg-white space-y-1">
                                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="item.label"></div>
                                        <div class="flex items-center gap-2 text-xs flex-wrap">
                                            <template x-if="!item.isNewOnly">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-slate-400 line-through" x-text="item.from"></span>
                                                    <span class="text-slate-300 font-bold">&rarr;</span>
                                                    <span class="font-semibold text-emerald-800" x-text="item.to"></span>
                                                </div>
                                            </template>
                                            <template x-if="item.isNewOnly">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-slate-400 text-[11px]">Ditetapkan:</span>
                                                    <span class="font-semibold text-emerald-800" x-text="item.to"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="getChanges().length === 0">
                            <div class="py-6 text-center text-slate-400 text-xs bg-slate-50 rounded-xl border border-slate-100">
                                Tidak ada mutasi nilai data lama dan baru pada peristiwa ini.
                            </div>
                        </template>
                    </div>

                    <!-- Metadata Tambahan (IP & User Agent) -->
                    <div class="text-[11px] text-slate-400 pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span>IP: <strong class="text-slate-600 font-mono" x-text="activeLog?.ip_address || '-'"></strong></span>
                        <span class="truncate max-w-[200px]" x-text="activeLog?.user_agent ? activeLog.user_agent : ''"></span>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="button" 
                            @click="detailModalOpen = false" 
                            class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
