<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.appeals.index') }}" 
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition"
               title="Kembali ke Daftar Banding">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="font-bold text-xl text-slate-800 leading-tight">
                        Banding {{ $appeal->appeal_code }}
                    </h2>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                        <span class="w-1.5 h-1.5 rounded-full {{ match($appeal->status) {
                            \App\Enums\AppealStatus::Menunggu => 'bg-amber-500',
                            \App\Enums\AppealStatus::Disetujui => 'bg-emerald-500',
                            \App\Enums\AppealStatus::Ditolak => 'bg-rose-500',
                        } }}"></span>
                        {{ $appeal->status->label() }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">
                    Wilayah {{ $appeal->region?->name ?? '-' }} &bull; Diajukan {{ $appeal->created_at->format('d M Y, H:i') }} WIB
                </p>
            </div>
        </div>
    </x-slot>

    @php
        $user = $appeal->user;
        $defaultReversePoints = $appeal->complaint?->penalty_points_applied ?? min($user->penalty_points, 35);
        if ($defaultReversePoints <= 0) {
            $defaultReversePoints = 15;
        }
    @endphp

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{ 
             actionType: 'approve', 
             pointsToReverse: {{ $defaultReversePoints }}, 
             currentBalance: {{ $user->penalty_points }}, 
             previewPhoto: null, 
             previewTitle: '' 
         }">

        <!-- Flash Notifications -->
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-xl flex items-center gap-2 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded-xl flex items-center gap-2 shadow-2xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl shadow-2xs">
                <div class="font-bold mb-1">Terdapat kesalahan input:</div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Split Workspace -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LEFT COLUMN (7 Cols): Kasus, Pembelaan & Dokumen Bukti -->
            <div class="lg:col-span-7 space-y-6">

                <!-- 1. Kasus Pelanggaran Asal (Jika Terhubung Aduan) -->
                @if ($appeal->complaint)
                    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kasus Pelanggaran Asal</span>
                            <a href="{{ route('admin.complaints.show', $appeal->complaint) }}" 
                               class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                                <span>Buka Aduan</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>

                        <div class="flex items-start justify-between gap-4 pt-1">
                            <div>
                                <div class="font-bold text-slate-800 text-sm">
                                    {{ $appeal->complaint->complaint_code }} &bull; {{ $appeal->complaint->complaintCategory?->name ?? 'Pelanggaran Operasional' }}
                                </div>
                                <div class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    {{ $appeal->complaint->title }}
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-bold text-rose-600 text-xs">
                                    +{{ $appeal->complaint->penalty_points_applied }} Poin Sanksi
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- 2. Alasan Pembelaan & Berkas Banding -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-6">
                    <!-- Alasan / Penjelasan Tertulis -->
                    <div class="space-y-2">
                        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider text-slate-400">
                            Alasan & Keterangan Pembelaan
                        </h3>
                        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line bg-slate-50/70 p-4 rounded-xl border border-slate-100">
                            {{ $appeal->reason }}
                        </p>
                    </div>

                    <!-- Surat Pernyataan / Dokumen Utama -->
                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-slate-800 text-sm">Surat Pernyataan Pemohon</h4>
                            <a href="{{ asset('storage/' . $appeal->statement_letter_path) }}" 
                               target="_blank" 
                               class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                                <span>Unduh Berkas</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>
                        </div>

                        @if ($appeal->isStatementLetterImage())
                            <!-- Pratinjau Foto Surat Langsung -->
                            <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100 cursor-pointer group relative"
                                 @click="previewPhoto = '{{ asset('storage/' . $appeal->statement_letter_path) }}'; previewTitle = 'Foto Surat Pernyataan'">
                                <img src="{{ asset('storage/' . $appeal->statement_letter_path) }}" 
                                     alt="Surat Pernyataan" 
                                     class="max-h-96 w-full object-contain mx-auto transition group-hover:scale-[1.01]">
                                <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition">
                                    Klik untuk Memperbesar Foto
                                </div>
                            </div>
                        @else
                            <!-- Tampilan Kartu PDF Bersih -->
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/80 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-red-100 text-red-600 font-bold text-xs">PDF</div>
                                    <div class="text-xs font-semibold text-slate-800 truncate max-w-xs sm:max-w-md">
                                        {{ basename($appeal->statement_letter_path) }}
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $appeal->statement_letter_path) }}" 
                                   target="_blank" 
                                   class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold rounded-lg text-xs transition">
                                    Buka Dokumen
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- 3. Foto Bukti Pendukung (Jika Ada) -->
                    @if ($appeal->evidence_photos && count($appeal->evidence_photos) > 0)
                        <div class="pt-4 border-t border-slate-100 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-slate-800 text-sm">
                                    Foto Bukti Pendukung ({{ count($appeal->evidence_photos) }})
                                </h4>
                                <span class="text-xs text-slate-400">Klik untuk melihat resolusi penuh</span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach ($appeal->evidence_photos as $idx => $photo)
                                    <div @click="previewPhoto = '{{ asset('storage/' . $photo) }}'; previewTitle = 'Bukti Pendukung #{{ $idx + 1 }}'"
                                         class="group relative aspect-video rounded-xl overflow-hidden border border-slate-200 bg-slate-100 cursor-pointer">
                                        <img src="{{ asset('storage/' . $photo) }}" 
                                             alt="Bukti" 
                                             class="w-full h-full object-cover transition group-hover:scale-105">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition">
                                            Perbesar
                                        </div>
                                        <div class="absolute bottom-1 left-1.5 px-1.5 py-0.5 rounded bg-slate-900/70 text-white text-[10px] font-medium">
                                            Bukti #{{ $idx + 1 }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 4. Riwayat Putusan Petugas (Jika Sudah Diputuskan) -->
                @if (! $appeal->isMenunggu())
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-slate-800 text-sm">Keputusan Petugas Peninjau</h4>
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                <span class="w-1.5 h-1.5 rounded-full {{ match($appeal->status) {
                                    \App\Enums\AppealStatus::Menunggu => 'bg-amber-500',
                                    \App\Enums\AppealStatus::Disetujui => 'bg-emerald-500',
                                    \App\Enums\AppealStatus::Ditolak => 'bg-rose-500',
                                } }}"></span>
                                {{ $appeal->status->label() }}
                            </span>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $appeal->admin_notes }}
                        </div>

                        <div class="pt-2 text-[11px] text-slate-400 flex items-center justify-between">
                            <span>Oleh: <strong class="text-slate-700">{{ $appeal->reviewer?->name ?? 'Admin' }}</strong></span>
                            <span>{{ $appeal->reviewed_at?->format('d/m/Y H:i') }} WIB</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- RIGHT COLUMN (5 Cols): Profil Akun & Panel Eksekusi Putusan Langsung -->
            <div class="lg:col-span-5 space-y-6">

                <!-- 1. Dossier Profil Pemohon -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-slate-800 text-sm">Profil Akun Pemohon</h4>
                        <span class="text-xs font-semibold text-slate-500">
                            {{ $user->isMitra() ? 'Mitra Pijat' : 'Pelanggan' }}
                        </span>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Nama</span>
                            <span class="font-bold text-slate-800">{{ $user->name }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Kontak</span>
                            <span class="font-medium text-slate-700">{{ $user->phone ?? $user->email }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Wilayah</span>
                            <span class="font-medium text-slate-700">{{ $appeal->region?->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Status Akun</span>
                            <span class="font-semibold {{ $user->status === \App\Enums\UserStatus::Diblokir ? 'text-rose-600' : ($user->status === \App\Enums\UserStatus::Ditangguhkan ? 'text-amber-600' : 'text-emerald-600') }}">
                                {{ $user->status->label() }}
                                @if ($user->suspended_until && $user->status === \App\Enums\UserStatus::Ditangguhkan)
                                    <span class="text-[10px] font-normal text-slate-400 block text-right">
                                        s/d {{ $user->suspended_until->format('d/m/Y') }}
                                    </span>
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-slate-400">Akumulasi Poin Sanksi</span>
                            <span class="font-bold text-slate-800">
                                {{ $user->penalty_points }} Poin
                            </span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <a href="{{ route('admin.penalties.show', $user) }}" 
                           class="w-full py-2 px-3 bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-800 font-semibold rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                            <span>Buka Riwayat Ledger Akun</span>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <!-- 2. Panel Tindakan Putusan Langsung (In-Place Decision Form) -->
                @if ($appeal->isMenunggu())
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm">Putusan Peninjauan</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Tentukan keputusan banding setelah meneliti surat pernyataan dan bukti.</p>
                        </div>

                        <!-- Segmented Switch: Setujui vs Tolak -->
                        <div class="grid grid-cols-2 p-1 bg-slate-100 rounded-xl gap-1">
                            <button type="button" 
                                    @click="actionType = 'approve'"
                                    :class="actionType === 'approve' ? 'bg-white text-slate-800 shadow-2xs font-bold' : 'text-slate-500 font-medium hover:text-slate-700'"
                                    class="py-2 text-xs rounded-lg transition text-center cursor-pointer">
                                Setujui Banding
                            </button>
                            <button type="button" 
                                    @click="actionType = 'reject'"
                                    :class="actionType === 'reject' ? 'bg-white text-slate-800 shadow-2xs font-bold' : 'text-slate-500 font-medium hover:text-slate-700'"
                                    class="py-2 text-xs rounded-lg transition text-center cursor-pointer">
                                Tolak Banding
                            </button>
                        </div>

                        <!-- FORM A: SETUJUI BANDING -->
                        <form x-show="actionType === 'approve'" 
                              method="POST" 
                              action="{{ route('admin.appeals.approve', $appeal) }}" 
                              class="space-y-4 pt-1">
                            @csrf

                            <!-- Input Pengurangan Poin -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="points_to_reverse" class="text-xs font-bold text-slate-700">Poin yang Dipulihkan</label>
                                    <span class="text-xs font-semibold text-emerald-600">
                                        Saldo Baru: <span x-text="Math.max(0, currentBalance - (parseInt(pointsToReverse) || 0))"></span> Poin
                                    </span>
                                </div>
                                <input type="number" 
                                       id="points_to_reverse" 
                                       name="points_to_reverse" 
                                       x-model="pointsToReverse" 
                                       min="1" 
                                       max="150" 
                                       required 
                                       class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                                <template x-if="Math.max(0, currentBalance - (parseInt(pointsToReverse) || 0)) < 60 && '{{ $user->status->value }}' === 'ditangguhkan'">
                                    <p class="text-[11px] text-emerald-700 mt-1 font-medium">
                                        &check; Akun akan otomatis kembali berstatus <strong>Terverifikasi (Aktif)</strong>.
                                    </p>
                                </template>
                            </div>

                            <!-- Catatan Persetujuan -->
                            <div>
                                <label for="admin_notes_approve" class="block text-xs font-bold text-slate-700 mb-1">Catatan Persetujuan</label>
                                <textarea id="admin_notes_approve" 
                                          name="admin_notes" 
                                          rows="3" 
                                          required 
                                          placeholder="Tuliskan pertimbangan diterimanya banding..."
                                          class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                            </div>

                            <button type="submit" 
                                    class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-xs transition cursor-pointer">
                                Setujui & Pulihkan Poin
                            </button>
                        </form>

                        <!-- FORM B: TOLAK BANDING -->
                        <form x-show="actionType === 'reject'" 
                              method="POST" 
                              action="{{ route('admin.appeals.reject', $appeal) }}" 
                              class="space-y-4 pt-1" 
                              style="display: none;">
                            @csrf

                            <div>
                                <label for="admin_notes_reject" class="block text-xs font-bold text-slate-700 mb-1">Alasan Penolakan</label>
                                <textarea id="admin_notes_reject" 
                                          name="admin_notes" 
                                          rows="4" 
                                          required 
                                          placeholder="Jelaskan alasan bukti pendukung tidak memenuhi syarat..."
                                          class="w-full text-xs rounded-xl border-slate-200 focus:ring-rose-500 focus:border-rose-500"></textarea>
                                <p class="text-[11px] text-slate-400 mt-1">Alasan ini akan menjadi catatan resmi pada berkas pemohon.</p>
                            </div>

                            <button type="submit" 
                                    class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-xs transition cursor-pointer">
                                Tolak Permohonan Banding
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </div>

        <!-- Lightbox Pratinjau Foto Penuh -->
        <div x-show="previewPhoto" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs transition" 
             style="display: none;"
             @keydown.escape.window="previewPhoto = null">
            <div class="fixed inset-0" @click="previewPhoto = null"></div>

            <div class="relative max-w-4xl w-full bg-white rounded-2xl overflow-hidden shadow-2xl z-10" @click.stop>
                <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
                    <div>
                        <div class="font-bold text-sm" x-text="previewTitle || 'Pratinjau Foto'"></div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Tekan ESC atau klik di luar untuk menutup</div>
                    </div>
                    <button type="button" @click="previewPhoto = null" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 bg-slate-950 flex items-center justify-center max-h-[75vh] overflow-auto">
                    <img :src="previewPhoto" alt="Preview" class="max-h-[70vh] w-auto object-contain rounded-lg">
                </div>
                <div class="p-3 bg-white border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-500">Resolusi dokumen asli</span>
                    <a :href="previewPhoto" target="_blank" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                        Buka di Tab Baru
                    </a>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
