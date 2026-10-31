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
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Audit Poin Sanksi: {{ $user->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="{ adjustModalOpen: false }">
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-xl flex items-center gap-2 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl shadow-2xs">
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
        @endphp

        <!-- Top Profile & Sanction Status Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- User Information Card -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-5">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-slate-800 text-base leading-tight">{{ $user->name }}</h3>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $user->email }}</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-500">
                            {{ $user->isMitra() ? 'Mitra Terapis' : 'Pelanggan' }}
                        </span>
                    </div>

                    <div class="mt-4 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Nomor Telepon</span>
                            <span class="font-semibold text-slate-700">{{ $user->phone_number ?? ($user->phone ?? '-') }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Wilayah Layanan</span>
                            <span class="font-semibold text-slate-700">{{ $regionName }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-slate-400">Status Akun Sistem</span>
                            <span class="font-semibold {{ $user->status === \App\Enums\UserStatus::Diblokir ? 'text-rose-600' : ($user->status === \App\Enums\UserStatus::Ditangguhkan ? 'text-orange-600' : 'text-emerald-600') }}">
                                {{ $user->status->label() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <button type="button" 
                            @click="adjustModalOpen = true"
                            class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Sesuaikan Poin Manual</span>
                    </button>
                </div>
            </div>

            <!-- Sanction Status Card (2 Columns) -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm">Akumulasi Poin Sanksi</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Tingkat pelanggaran dan status pembatasan akun saat ini.</p>
                        </div>
                        <span class="text-xs font-bold font-mono px-3 py-1 bg-slate-100 text-slate-700 rounded-lg">
                            {{ $points }} / 150 Poin
                        </span>
                    </div>

                    <!-- Status Banner jika aktif sanksi -->
                    @if ($user->suspended_until && $user->suspended_until->isFuture())
                        <div class="mt-4 p-4 bg-amber-50/70 border border-amber-200 rounded-xl flex items-center gap-3 text-xs text-amber-900">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                            <div class="flex-1">
                                <div class="font-bold">Masa Pembekuan Sementara (Suspend)</div>
                                <div class="text-[11px] text-amber-700 mt-0.5">
                                    Berlaku s/d <strong>{{ $user->suspended_until->translatedFormat('d F Y - H:i') }} WIB</strong> 
                                    ({{ ceil(now()->diffInHours($user->suspended_until) / 24) }} hari tersisa)
                                </div>
                            </div>
                        </div>
                    @elseif ($user->status === \App\Enums\UserStatus::Diblokir)
                        <div class="mt-4 p-4 bg-rose-50/70 border border-rose-200 rounded-xl flex items-center gap-3 text-xs text-rose-900">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                            <div class="flex-1">
                                <div class="font-bold">Akun Diblokir Permanen</div>
                                <div class="text-[11px] text-rose-700 mt-0.5">
                                    Akun telah mencapai ambang batas sanksi 150 poin. Seluruh akses operasional dihentikan.
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 p-4 bg-emerald-50/70 border border-emerald-200 rounded-xl flex items-center gap-3 text-xs text-emerald-900">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <div class="flex-1">
                                <div class="font-bold">Status Akun Normal & Aktif</div>
                                <div class="text-[11px] text-emerald-700 mt-0.5">
                                    Tidak ada sanksi pembekuan yang sedang berjalan pada akun ini.
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Ambang Batas Tingkat Sanksi (Struktur Tenang) -->
                    <div class="mt-4 border border-slate-100 rounded-xl overflow-hidden">
                        <div class="grid grid-cols-2 sm:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 text-xs text-center bg-slate-50/60">
                            <div class="p-3 {{ $points >= 30 && $points < 60 ? 'bg-amber-50/60 font-semibold' : '' }}">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">30 Poin</span>
                                <span class="text-xs text-slate-700 font-medium mt-0.5 block">Peringatan</span>
                            </div>
                            <div class="p-3 {{ $points >= 60 && $points < 100 ? 'bg-orange-50/60 font-semibold' : '' }}">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">60 Poin</span>
                                <span class="text-xs text-slate-700 font-medium mt-0.5 block">Suspend 3 Hari</span>
                            </div>
                            <div class="p-3 {{ $points >= 100 && $points < 150 ? 'bg-orange-50/60 font-semibold' : '' }}">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">100 Poin</span>
                                <span class="text-xs text-slate-700 font-medium mt-0.5 block">Suspend 7 Hari</span>
                            </div>
                            <div class="p-3 {{ $points >= 150 ? 'bg-rose-50/60 font-semibold' : '' }}">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">150 Poin</span>
                                <span class="text-xs text-slate-700 font-medium mt-0.5 block">Banned Permanen</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-[11px] text-slate-400">
                    Mitra dapat memulihkan poin sanksi secara otomatis: setiap pesanan selesai dengan rating bintang 5 memotong 1 poin.
                </div>
            </div>
        </div>

        <!-- Ledger Audit History Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Riwayat Transaksi Poin (Audit Trail)</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Log mutasi poin otomatis dan penyesuaian manual petugas.</p>
                </div>
                <span class="text-xs font-semibold text-slate-500 font-mono">
                    {{ $ledgers->total() }} Catatan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3 whitespace-nowrap">Waktu</th>
                            <th class="px-5 py-3 whitespace-nowrap">Tipe Mutasi</th>
                            <th class="px-5 py-3 text-center whitespace-nowrap">Poin</th>
                            <th class="px-5 py-3 text-center whitespace-nowrap">Saldo</th>
                            <th class="px-5 py-3">Keterangan / Alasan</th>
                            <th class="px-5 py-3 whitespace-nowrap">Referensi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($ledgers as $ledger)
                            @php
                                $typeLabel = match($ledger->type) {
                                    'violation' => 'Pelanggaran Aduan',
                                    'merit_redemption' => 'Penebusan Rating',
                                    'appeal_reversal' => 'Banding Diterima',
                                    'admin_adjustment' => 'Penyesuaian Petugas',
                                    default => 'Mutasi',
                                };
                                $typeDot = match($ledger->type) {
                                    'violation' => 'bg-rose-500',
                                    'merit_redemption' => 'bg-emerald-500',
                                    'appeal_reversal' => 'bg-blue-500',
                                    'admin_adjustment' => 'bg-purple-500',
                                    default => 'bg-slate-400',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- Timestamp -->
                                <td class="px-5 py-3.5 whitespace-nowrap text-slate-500">
                                    <div class="font-semibold text-slate-700">{{ $ledger->created_at->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $ledger->created_at->format('H:i') }} WIB</div>
                                </td>

                                <!-- Mutation Type (Calm Dot) -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $typeDot }}"></span>
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
                                <td class="px-5 py-3.5 text-slate-700 max-w-xs leading-relaxed">
                                    {{ $ledger->reason }}
                                </td>

                                <!-- Reference / Actor -->
                                <td class="px-5 py-3.5 whitespace-nowrap text-slate-500 text-[11px]">
                                    @if ($ledger->complaint)
                                        <a href="{{ route('admin.complaints.show', $ledger->complaint) }}" class="text-emerald-700 hover:text-emerald-800 hover:underline font-semibold">
                                            Aduan {{ $ledger->complaint->complaint_code }}
                                        </a>
                                    @elseif ($ledger->order)
                                        <span class="font-medium text-slate-700">Pesanan {{ $ledger->order->order_code }}</span>
                                    @elseif ($ledger->actor)
                                        <span class="text-slate-600">Admin: <strong class="text-slate-700">{{ $ledger->actor->name }}</strong></span>
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
                                <th class="px-5 py-3">Poin Sanksi</th>
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
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $complaint->status->dotClass() }}"></span>
                                            {{ $complaint->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.complaints.show', $complaint) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1">
                                            <span>Periksa</span>
                                            <span>&rarr;</span>
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
                            <p class="text-xs text-slate-400 mt-0.5">{{ $user->name }} &bull; Saldo saat ini: {{ $points }} poin</p>
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
                                        <div class="text-[10px] text-slate-400">Keringanan / pemulihan</div>
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
                                   class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 placeholder:text-xs placeholder-slate-400">
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
                                      class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 placeholder:text-xs placeholder-slate-400"></textarea>
                            <p class="text-[11px] text-slate-400 mt-1">Catatan ini akan tersimpan permanen di riwayat audit sistem.</p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="adjustModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-xs cursor-pointer">
                                Terapkan Penyesuaian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

