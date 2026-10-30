<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.penalties.index') }}" 
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition"
               title="Kembali ke Daftar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    Audit Poin Sanksi: {{ $user->name }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    ID Akun #{{ $user->id }} &bull; Peran: {{ $user->isMitra() ? 'Mitra Terapis' : 'Pelanggan' }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="{ adjustModalOpen: false }">
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold rounded-2xl flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-2xl">
                <div class="font-bold mb-1">Terjadi kesalahan pada input:</div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $regionName = $user->isMitra() 
                ? ($user->partnerProfile?->region?->name ?? 'Belum ditentukan')
                : ($user->customerAddresses?->first()?->region?->name ?? 'Belum ditentukan');

            $points = $user->penalty_points;
            $progressPercent = min(100, round(($points / 150) * 100));
        @endphp

        <!-- Top Profile & Sanction Status Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- User Information Card -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-lg">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-base leading-tight">{{ $user->name }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $user->isMitra() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                            {{ $user->isMitra() ? 'Mitra' : 'Pelanggan' }}
                        </span>
                    </div>

                    <div class="mt-4 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between py-1">
                            <span class="text-slate-400">Nomor Telepon</span>
                            <span class="font-semibold text-slate-700">{{ $user->phone_number ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-slate-400">Wilayah Layanan</span>
                            <span class="font-semibold text-slate-700">{{ $regionName }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-slate-400">Status Akun Sistem</span>
                            <span class="font-bold {{ $user->status === \App\Enums\UserStatus::Diblokir ? 'text-rose-600' : ($user->status === \App\Enums\UserStatus::Ditangguhkan ? 'text-orange-600' : 'text-emerald-600') }}">
                                {{ $user->status->label() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center gap-2">
                    <button type="button" 
                            @click="adjustModalOpen = true"
                            class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Sesuaikan Poin Manual</span>
                    </button>
                </div>
            </div>

            <!-- Sanction Gauge & Status Card (2 Columns) -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h4 class="font-bold text-slate-800 text-sm">Akumulasi Poin Sanksi & Tingkat Pembatasan</h4>
                        <span class="text-xs font-bold px-3 py-1 rounded-xl {{ $points >= 150 ? 'bg-rose-100 text-rose-800' : ($points >= 60 ? 'bg-orange-100 text-orange-800' : ($points >= 30 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700')) }}">
                            {{ $points }} / 150 Poin
                        </span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-500 mb-1.5">
                            <span>Ambang Batas Sanksi</span>
                            <span>{{ $progressPercent }}% Kapasitas Banned</span>
                        </div>
                        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200">
                            <div class="h-full rounded-full transition-all duration-300 {{ $points >= 150 ? 'bg-rose-600' : ($points >= 60 ? 'bg-orange-500' : ($points >= 30 ? 'bg-amber-500' : 'bg-emerald-500')) }}" 
                                 style="width: {{ $progressPercent }}%"></div>
                        </div>
                    </div>

                    <!-- Threshold Milestone Markers -->
                    <div class="grid grid-cols-4 gap-2 mt-4 text-[11px]">
                        <div class="p-2.5 rounded-xl border {{ $points >= 30 ? 'bg-amber-50/70 border-amber-200 text-amber-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                            <div class="font-bold">30 Poin</div>
                            <div class="text-[10px] mt-0.5">Peringatan</div>
                        </div>
                        <div class="p-2.5 rounded-xl border {{ $points >= 60 ? 'bg-orange-50/70 border-orange-200 text-orange-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                            <div class="font-bold">60 Poin</div>
                            <div class="text-[10px] mt-0.5">Suspend 3 Hari</div>
                        </div>
                        <div class="p-2.5 rounded-xl border {{ $points >= 100 ? 'bg-orange-100/70 border-orange-300 text-orange-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                            <div class="font-bold">100 Poin</div>
                            <div class="text-[10px] mt-0.5">Suspend 7 Hari</div>
                        </div>
                        <div class="p-2.5 rounded-xl border {{ $points >= 150 ? 'bg-rose-50/70 border-rose-200 text-rose-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                            <div class="font-bold">150 Poin</div>
                            <div class="text-[10px] mt-0.5">Banned Permanen</div>
                        </div>
                    </div>

                    <!-- Suspension Status Banner (if suspended) -->
                    @if ($user->suspended_until && $user->suspended_until->isFuture())
                        <div class="mt-4 p-3.5 bg-orange-50 border border-orange-200 rounded-xl flex items-center justify-between text-xs text-orange-900">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-5 h-5 text-orange-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <div class="font-bold">Akun Sedang Dalam Masa Pembekuan (Suspend)</div>
                                    <div class="text-[11px] text-orange-700 mt-0.5">
                                        Berlaku sampai: <strong>{{ $user->suspended_until->translatedFormat('d F Y - H:i') }} WIB</strong> 
                                        ({{ ceil(now()->diffInHours($user->suspended_until) / 24) }} hari tersisa)
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif ($user->status === \App\Enums\UserStatus::Diblokir)
                        <div class="mt-4 p-3.5 bg-rose-50 border border-rose-200 rounded-xl flex items-center gap-2.5 text-xs text-rose-900">
                            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            <div>
                                <div class="font-bold">Akun Telah Diblokir Permanen</div>
                                <div class="text-[11px] text-rose-700 mt-0.5">
                                    Akun melampaui batas maksimal sanksi. Akses login dan operasional ditutup total.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-4 text-[11px] text-slate-400 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Mitra dapat menebus poin sanksi secara otomatis: setiap pesanan selesai dengan rating bintang 5 memotong 1 poin.</span>
                </div>
            </div>
        </div>

        <!-- Ledger Audit History Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Riwayat Transaksi Poin (Audit Trail)</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Log pencatatan mutasi poin otomatis dan penyesuaian manual petugas.</p>
                </div>
                <span class="text-xs font-semibold px-3 py-1 bg-slate-100 text-slate-600 rounded-lg">
                    Total {{ $ledgers->total() }} Catatan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Waktu</th>
                            <th class="px-5 py-3">Tipe Mutasi</th>
                            <th class="px-5 py-3 text-center">Poin</th>
                            <th class="px-5 py-3 text-center">Saldo</th>
                            <th class="px-5 py-3">Keterangan / Alasan</th>
                            <th class="px-5 py-3">Petugas / Referensi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($ledgers as $ledger)
                            @php
                                $typeBadgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
                                $typeLabel = 'Mutasi';

                                if ($ledger->type === 'violation') {
                                    $typeBadgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                                    $typeLabel = 'Pelanggaran Aduan';
                                } elseif ($ledger->type === 'merit_redemption') {
                                    $typeBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                    $typeLabel = 'Penebusan Bintang 5';
                                } elseif ($ledger->type === 'appeal_reversal') {
                                    $typeBadgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                                    $typeLabel = 'Banding Diterima';
                                } elseif ($ledger->type === 'admin_adjustment') {
                                    $typeBadgeClass = 'bg-purple-50 text-purple-700 border-purple-200';
                                    $typeLabel = 'Penyesuaian Petugas';
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- Timestamp -->
                                <td class="px-5 py-3.5 whitespace-nowrap text-slate-500">
                                    <div class="font-bold text-slate-700">{{ $ledger->created_at->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $ledger->created_at->format('H:i:s') }} WIB</div>
                                </td>

                                <!-- Mutation Type -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $typeBadgeClass }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>

                                <!-- Points Delta -->
                                <td class="px-5 py-3.5 text-center whitespace-nowrap font-bold">
                                    @if ($ledger->points > 0)
                                        <span class="text-rose-600 font-bold">+{{ $ledger->points }}</span>
                                    @elseif ($ledger->points < 0)
                                        <span class="text-emerald-600 font-bold">{{ $ledger->points }}</span>
                                    @else
                                        <span class="text-slate-500">0</span>
                                    @endif
                                </td>

                                <!-- Balance Before -> After -->
                                <td class="px-5 py-3.5 text-center whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                    {{ $ledger->balance_before }} &rarr; <strong class="text-slate-800">{{ $ledger->balance_after }}</strong>
                                </td>

                                <!-- Reason -->
                                <td class="px-5 py-3.5 text-slate-700 max-w-xs">
                                    {{ $ledger->reason }}
                                </td>

                                <!-- Reference / Actor -->
                                <td class="px-5 py-3.5 whitespace-nowrap text-slate-500 text-[11px]">
                                    @if ($ledger->complaint)
                                        <a href="{{ route('admin.complaints.show', $ledger->complaint) }}" class="text-emerald-600 hover:underline font-bold">
                                            Aduan: {{ $ledger->complaint->complaint_code }}
                                        </a>
                                    @elseif ($ledger->order)
                                        <span class="font-semibold text-slate-700">Pesanan: {{ $ledger->order->order_code }}</span>
                                    @elseif ($ledger->actor)
                                        <span class="text-slate-600">Admin: <strong>{{ $ledger->actor->name }}</strong></span>
                                    @else
                                        <span class="text-slate-400">Sistem Otomatis</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada transaksi poin yang tercatat untuk akun ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($ledgers->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $ledgers->links() }}
                </div>
            @endif
        </div>

        <!-- Recent Complaints Targeting this User -->
        @if ($recentComplaints->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Riwayat Laporan Aduan Terkait</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Laporan pengaduan di mana pengguna ini menjadi pihak terlapor.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3">Kode Aduan</th>
                                <th class="px-5 py-3">Kategori</th>
                                <th class="px-5 py-3">Pelapor</th>
                                <th class="px-5 py-3">Poin Diterapkan</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($recentComplaints as $complaint)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-3.5 font-bold text-slate-800">{{ $complaint->complaint_code }}</td>
                                    <td class="px-5 py-3.5 text-slate-600">{{ $complaint->complaintCategory?->name ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-slate-600">{{ $complaint->complainant?->name ?? 'Anonim' }}</td>
                                    <td class="px-5 py-3.5 font-bold text-rose-600">
                                        {{ $complaint->penalty_points_applied ? '+' . $complaint->penalty_points_applied . ' Poin' : '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold {{ $complaint->status->badgeClasses() }}">
                                            {{ $complaint->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.complaints.show', $complaint) }}" class="text-emerald-600 hover:underline font-bold text-xs">
                                            Lihat Aduan &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Modal Penyesuaian Poin Manual -->
        <div x-show="adjustModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="adjustModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden" @click.stop>
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-slate-800 text-base">Penyesuaian Poin Sanksi Manual</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Akun: {{ $user->name }} (Saldo sekarang: {{ $points }} poin)</p>
                        </div>
                        <button type="button" @click="adjustModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.penalties.adjust', $user) }}" class="p-6 space-y-4">
                        @csrf

                        <!-- Tipe Penyesuaian -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">Tindakan Penyesuaian</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition">
                                    <input type="radio" name="type" value="add" required class="text-emerald-600 focus:ring-emerald-500">
                                    <div class="ml-2.5">
                                        <div class="text-xs font-bold text-slate-800">+ Tambah Poin</div>
                                        <div class="text-[10px] text-slate-400">Penalti pelanggaran khusus</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition">
                                    <input type="radio" name="type" value="deduct" required class="text-emerald-600 focus:ring-emerald-500">
                                    <div class="ml-2.5">
                                        <div class="text-xs font-bold text-slate-800">- Kurangi Poin</div>
                                        <div class="text-[10px] text-slate-400">Keringanan / amnesti regional</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Jumlah Poin -->
                        <div>
                            <label for="points" class="block text-xs font-bold text-slate-700 mb-1">Jumlah Poin</label>
                            <input type="number" 
                                   id="points" 
                                   name="points" 
                                   min="1" 
                                   max="150" 
                                   required 
                                   placeholder="Contoh: 15"
                                   class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <p class="text-[11px] text-slate-400 mt-1">Masukkan angka bulat positif (1 - 150).</p>
                        </div>

                        <!-- Alasan / Justifikasi -->
                        <div>
                            <label for="reason" class="block text-xs font-bold text-slate-700 mb-1">Alasan Penyesuaian (Audit Trail)</label>
                            <textarea id="reason" 
                                      name="reason" 
                                      rows="3" 
                                      required 
                                      placeholder="Jelaskan dasar pertimbangan penyesuaian poin sanksi ini..."
                                      class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                            <p class="text-[11px] text-slate-400 mt-1">Catatan ini akan tersimpan permanen di riwayat audit sistem.</p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="adjustModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-xs">
                                Terapkan Penyesuaian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

