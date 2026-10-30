<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.complaints.index') }}" 
                   class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition"
                   title="Kembali ke Daftar Aduan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl font-bold text-slate-800 leading-tight">
                            Aduan {{ $complaint->complaint_code }}
                        </h2>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                            <span class="w-1.5 h-1.5 rounded-full {{ $complaint->status->dotClass() }}"></span>
                            {{ $complaint->status->label() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Wilayah {{ $complaint->region?->name ?? '-' }} &bull; Dilaporkan {{ $complaint->created_at->format('d M Y, H:i') }} WIB
                    </p>
                </div>
            </div>

            @if ($complaint->isPending())
                <form method="POST" action="{{ route('admin.complaints.process', $complaint) }}">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-xs transition inline-flex items-center gap-1.5 cursor-pointer">
                        <span>Mulai Investigasi</span>
                        <span>&rarr;</span>
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{ 
             actionTab: 'resolve',
             selectedResolution: 'terbukti_bersalah',
             isLainnya: {{ $complaint->isLainnyaCategory() ? 'true' : 'false' }},
             defaultPoints: {{ $complaint->getDefaultPenaltyPoints() }},
             manualPoints: {{ $complaint->getDefaultPenaltyPoints() }},
             previewPhoto: null,
             previewTitle: ''
         }">

        <!-- Status Alerts -->
        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2.5 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1 shadow-2xs">
                <div class="font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Terjadi kesalahan saat memproses putusan aduan:</span>
                </div>
                <ul class="list-disc list-inside pl-6 text-rose-700 space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Split-Screen Workspace (7 Cols Left, 5 Cols Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LEFT COLUMN (7 Cols): Dossier Aduan & Bukti -->
            <div class="lg:col-span-7 space-y-6">

                <!-- 1. Pihak yang Terlibat (Pelapor vs Terlapor) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Pihak yang Terlibat</h3>
                        <span class="text-xs font-semibold text-slate-700">
                            {{ $complaint->category_label }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-1">
                        <!-- Pelapor -->
                        <div class="space-y-1.5">
                            <span class="text-[11px] font-semibold text-slate-400 block uppercase tracking-wide">
                                Pelapor ({{ ucfirst($complaint->complainant_role) }})
                            </span>
                            <div class="font-bold text-slate-800 text-sm">
                                {{ $complaint->complainant?->name ?? 'Pelapor' }}
                            </div>
                            <div class="text-xs text-slate-500 font-mono">
                                {{ $complaint->complainant?->phone ?? 'Tanpa nomor telepon' }}
                            </div>
                            @if ($complaint->complainant?->phone)
                                @php
                                    $phoneClean = preg_replace('/[^0-9]/', '', $complaint->complainant->phone);
                                    if (str_starts_with($phoneClean, '0')) {
                                        $phoneClean = '62' . substr($phoneClean, 1);
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $phoneClean }}?text=Halo%20{{ urlencode($complaint->complainant->name) }},%20kami%20dari%20Admin%20Pijat.In%20terkait%20aduan%20{{ $complaint->complaint_code }}" 
                                   target="_blank" 
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline pt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/>
                                    </svg>
                                    <span>WhatsApp Pelapor &rarr;</span>
                                </a>
                            @endif
                        </div>

                        <!-- Terlapor -->
                        <div class="space-y-1.5 sm:border-l sm:border-slate-100 sm:pl-5">
                            <span class="text-[11px] font-semibold text-slate-400 block uppercase tracking-wide">
                                Terlapor ({{ $complaint->complainant_role === 'pelanggan' ? 'Mitra Terapis' : 'Pelanggan' }})
                            </span>
                            <div class="font-bold text-slate-800 text-sm">
                                {{ $complaint->target?->name ?? '-' }}
                            </div>
                            <div class="text-xs text-slate-500 font-mono">
                                {{ $complaint->target?->phone ?? 'Tanpa nomor telepon' }}
                            </div>
                            @if ($complaint->target?->phone)
                                @php
                                    $phoneTargetClean = preg_replace('/[^0-9]/', '', $complaint->target->phone);
                                    if (str_starts_with($phoneTargetClean, '0')) {
                                        $phoneTargetClean = '62' . substr($phoneTargetClean, 1);
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $phoneTargetClean }}?text=Halo%20{{ urlencode($complaint->target->name) }},%20kami%20dari%20Admin%20Pijat.In%20terkait%20klarifikasi%20aduan%20{{ $complaint->complaint_code }}" 
                                   target="_blank" 
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline pt-0.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/>
                                    </svg>
                                    <span>WhatsApp Terlapor &rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 2. Uraian & Kronologi Kasus -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="space-y-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Subjek Keluhan</span>
                        <h3 class="text-base font-bold text-slate-800 leading-snug">{{ $complaint->title }}</h3>
                    </div>

                    <div class="pt-3 border-t border-slate-100 space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Kronologi / Penjelasan Pelapor</span>
                        <div class="text-xs text-slate-700 leading-relaxed whitespace-pre-line bg-slate-50/70 p-4 rounded-xl border border-slate-100">
                            {{ $complaint->description }}
                        </div>
                    </div>

                    <!-- Lampiran Bukti Foto -->
                    <div class="pt-3 border-t border-slate-100 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Lampiran Bukti Foto</span>
                            @if ($complaint->evidence_photos && count($complaint->evidence_photos) > 0)
                                <span class="text-xs text-slate-400">{{ count($complaint->evidence_photos) }} foto</span>
                            @endif
                        </div>

                        @if ($complaint->evidence_photos && count($complaint->evidence_photos) > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach ($complaint->evidence_photos as $idx => $photo)
                                    <div class="group relative aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-100 cursor-pointer"
                                         @click="previewPhoto = '{{ asset($photo) }}'; previewTitle = 'Bukti Foto #{{ $idx + 1 }}'">
                                        <img src="{{ asset($photo) }}" alt="Bukti aduan" class="w-full h-full object-cover transition group-hover:scale-105">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-semibold transition">
                                            Perbesar
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">Tidak ada lampiran foto yang diunggah oleh pelapor.</p>
                        @endif
                    </div>

                    <!-- Linimasa Status Penanganan -->
                    <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                        <div>
                            <span class="text-slate-400">Diterima:</span>
                            <span class="font-medium text-slate-700 ml-1">{{ $complaint->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Mulai Diproses:</span>
                            <span class="font-medium text-slate-700 ml-1">{{ $complaint->handled_at ? $complaint->handled_at->format('d/m/Y H:i') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400">PIC Penanggung Jawab:</span>
                            <span class="font-medium text-slate-700 ml-1">{{ $complaint->handledBy?->name ?? 'Belum ada PIC' }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN (5 Cols): Konteks Pesanan & Panel Keputusan In-Place -->
            <div class="lg:col-span-5 space-y-6">

                <!-- 1. Konteks Pesanan Terkait -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Konteks Pesanan</h3>
                        @if ($complaint->order)
                            <a href="{{ route('admin.orders.show', $complaint->order) }}" 
                               target="_blank"
                               class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1">
                                <span>Buka Pesanan</span>
                                <span>&rarr;</span>
                            </a>
                        @endif
                    </div>

                    @if ($complaint->order)
                        <div class="space-y-3 text-xs">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-mono font-bold text-slate-800 text-sm">{{ $complaint->order->order_code }}</span>
                                    <span class="text-slate-400 block mt-0.5">{{ $complaint->order->service_name }} &bull; {{ $complaint->order->duration_minutes }} Menit</span>
                                </div>
                                <span class="inline-flex items-center gap-1.5 font-medium text-slate-700">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $complaint->order->status->dotClass() }}"></span>
                                    {{ $complaint->order->status->label() }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                                <div>
                                    <span class="text-slate-400 block">Total Tarif</span>
                                    <span class="font-bold text-emerald-700 text-sm mt-0.5 block">Rp {{ number_format($complaint->order->total_price, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    @php
                                        $mitraShare = $complaint->order->total_price * (1 - ($complaint->order->commission_percent / 100));
                                    @endphp
                                    <span class="text-slate-400 block">Hak Mitra</span>
                                    <span class="font-bold text-slate-700 text-sm mt-0.5 block">Rp {{ number_format($mitraShare, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-100">
                                <span class="text-slate-400 block">Lokasi Pelayanan</span>
                                <span class="font-medium text-slate-700 mt-0.5 block leading-relaxed">{{ $complaint->order->service_address_text }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-xs text-slate-400">
                            Pesanan terkait tidak ditemukan atau telah dihapus.
                        </div>
                    @endif
                </div>

                <!-- 2. Panel Putusan & Tindak Lanjut -->
                @if ($complaint->isResolved() || $complaint->isRejected())
                    <!-- Tampilan Putusan Final Resmi -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $complaint->isResolved() ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Putusan Final Kasus
                                </h3>
                            </div>
                            <span class="text-[11px] text-slate-400">
                                {{ $complaint->resolved_at?->format('d M Y, H:i') }} WIB
                            </span>
                        </div>

                        <div class="space-y-1">
                            <span class="text-slate-400 text-xs block">Bentuk Putusan:</span>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-800">{{ $complaint->resolutionLabel() }}</span>
                                @if ($complaint->penalty_points_applied > 0)
                                    <span class="font-bold text-rose-600 text-xs">
                                        +{{ $complaint->penalty_points_applied }} Poin Sanksi
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 space-y-1.5">
                            <span class="text-slate-400 text-xs block">Catatan Investigasi & Alasan:</span>
                            <div class="text-xs text-slate-700 leading-relaxed whitespace-pre-line bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
                                {{ $complaint->admin_notes }}
                            </div>
                        </div>

                        <div class="pt-2 text-[11px] text-slate-400">
                            Ditetapkan oleh: <span class="font-semibold text-slate-700">{{ $complaint->handledBy?->name ?? 'Admin Bertugas' }}</span>
                        </div>
                    </div>
                @else
                    <!-- In-Place Decision Workspace Form (Tanpa Modal Popup) -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-bold text-slate-800">Tindak Lanjut & Putusan Kasus</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Tentukan keputusan akhir setelah klarifikasi kedua belah pihak.</p>
                        </div>

                        <!-- Segmented Switcher: Putusan Solusi vs Tolak -->
                        <div class="grid grid-cols-2 p-1 bg-slate-100 rounded-xl gap-1">
                            <button type="button" 
                                    @click="actionTab = 'resolve'"
                                    :class="actionTab === 'resolve' ? 'bg-white text-slate-800 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                                    class="py-2 text-xs rounded-lg transition text-center cursor-pointer">
                                Putuskan Solusi
                            </button>
                            <button type="button" 
                                    @click="actionTab = 'reject'"
                                    :class="actionTab === 'reject' ? 'bg-white text-slate-800 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                                    class="py-2 text-xs rounded-lg transition text-center cursor-pointer">
                                Tolak / Abaikan
                            </button>
                        </div>

                        <!-- FORM A: Putusan Solusi (Resolve) -->
                        <form x-show="actionTab === 'resolve'" 
                              method="POST" 
                              action="{{ route('admin.complaints.resolve', $complaint) }}" 
                              class="space-y-4">
                            @csrf
                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-700 block">Pilihan Bentuk Putusan *</label>
                                
                                <!-- Opsi 1: Terbukti Bersalah -->
                                <label class="flex items-start p-3 rounded-xl border cursor-pointer transition text-xs"
                                       :class="selectedResolution === 'terbukti_bersalah' ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200 hover:bg-slate-50'">
                                    <input type="radio" name="resolution_type" value="terbukti_bersalah" x-model="selectedResolution" required class="mt-0.5 text-rose-600 focus:ring-rose-500">
                                    <div class="ml-2.5 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-slate-800">Aduan Terbukti Bersalah</span>
                                            <template x-if="!isLainnya">
                                                <span class="font-bold text-rose-600 text-[11px]">+<span x-text="defaultPoints"></span> Poin</span>
                                            </template>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                            Terlapor melanggar SOP. Poin pelanggaran otomatis diakumulasikan ke akun terlapor.
                                        </p>

                                        <!-- Khusus Kategori Lainnya: Manual Points Input -->
                                        <template x-if="isLainnya && selectedResolution === 'terbukti_bersalah'">
                                            <div class="mt-2.5 pt-2 border-t border-rose-200/60 space-y-1">
                                                <label class="text-[11px] font-bold text-rose-900 block">Bobot Poin (1 s/d 150):</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="number" 
                                                           name="manual_points" 
                                                           x-model="manualPoints" 
                                                           min="1" 
                                                           max="150" 
                                                           required
                                                           class="w-20 text-xs font-bold border-rose-300 rounded-lg py-1 px-2.5 focus:ring-rose-500 focus:border-rose-500 bg-white">
                                                    <span class="text-xs text-rose-700 font-medium">Poin sanksi</span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </label>

                                <!-- Opsi 2: Refund Penuh Pelanggan -->
                                <label class="flex items-start p-3 rounded-xl border cursor-pointer transition text-xs"
                                       :class="selectedResolution === 'refund_penuh' ? 'border-emerald-300 bg-emerald-50/30' : 'border-slate-200 hover:bg-slate-50'">
                                    <input type="radio" name="resolution_type" value="refund_penuh" x-model="selectedResolution" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                    <div class="ml-2.5">
                                        <span class="font-bold text-slate-800 block">Refund Penuh ke Pelanggan</span>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                            Aduan sah. Hak mitra dipotong dan dana dikembalikan penuh ke pelanggan.
                                        </p>
                                    </div>
                                </label>

                                <!-- Opsi 3: Damai / Mediasi Bersama -->
                                <label class="flex items-start p-3 rounded-xl border cursor-pointer transition text-xs"
                                       :class="selectedResolution === 'damai_tanpa_tindakan' ? 'border-sky-300 bg-sky-50/30' : 'border-slate-200 hover:bg-slate-50'">
                                    <input type="radio" name="resolution_type" value="damai_tanpa_tindakan" x-model="selectedResolution" class="mt-0.5 text-sky-600 focus:ring-sky-500">
                                    <div class="ml-2.5">
                                        <span class="font-bold text-slate-800 block">Selesai Damai / Mediasi Bersama</span>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                            Kedua belah pihak sepakat damai tanpa sanksi penalti maupun kompensasi dana.
                                        </p>
                                    </div>
                                </label>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-slate-700 block">Catatan Hasil Investigasi & Pertimbangan *</label>
                                <textarea name="admin_notes" 
                                          rows="3" 
                                          required 
                                          placeholder="Tuliskan ringkasan mediasi dan dasar pertimbangan putusan (min. 10 karakter)..."
                                          class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                            </div>

                            <button type="submit" 
                                    class="w-full py-2.5 px-4 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>Simpan Putusan Selesai</span>
                                <span>&rarr;</span>
                            </button>
                        </form>

                        <!-- FORM B: Tolak Aduan (Reject) -->
                        <form x-show="actionTab === 'reject'" 
                              method="POST" 
                              action="{{ route('admin.complaints.reject', $complaint) }}" 
                              class="space-y-4">
                            @csrf
                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-slate-700 block">Alasan Penolakan / Pengabaian *</label>
                                <textarea name="admin_notes" 
                                          rows="3" 
                                          required 
                                          placeholder="Jelaskan alasan mengapa aduan ini ditolak atau dinyatakan tidak valid..."
                                          class="w-full text-xs rounded-xl border-slate-200 focus:border-rose-500 focus:ring-rose-500"></textarea>
                            </div>

                            <label class="flex items-start p-3 rounded-xl bg-amber-50/60 border border-amber-200 cursor-pointer">
                                <input type="checkbox" name="is_false_complaint" value="1" class="mt-0.5 rounded text-amber-600 focus:ring-amber-500">
                                <span class="ml-2 text-xs text-amber-900 font-medium leading-relaxed">
                                    Tandai sebagai <strong>Laporan Palsu / Mengada-ada</strong>.
                                </span>
                            </label>

                            <button type="submit" 
                                    class="w-full py-2.5 px-4 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>Tolak Laporan Ini</span>
                                <span>&rarr;</span>
                            </button>
                        </form>
                    </div>
                @endif

            </div>

        </div>

        <!-- Lightbox Pratinjau Bukti Foto -->
        <div x-show="previewPhoto" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
             @keydown.escape.window="previewPhoto = null">
            <div @click.away="previewPhoto = null" 
                 class="bg-white rounded-2xl max-w-2xl w-full p-4 shadow-2xl space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider" x-text="previewTitle"></h3>
                    <button type="button" @click="previewPhoto = null" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="rounded-xl overflow-hidden bg-slate-100 max-h-[70vh] flex items-center justify-center">
                    <img :src="previewPhoto" alt="Pratinjau Foto" class="max-h-[70vh] w-auto object-contain mx-auto">
                </div>
            </div>
        </div>

    </div>
</x-app-layout>