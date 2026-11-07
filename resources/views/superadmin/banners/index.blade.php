<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800 leading-tight">
            {{ __('Manajemen Banner Promosi Pelanggan') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{
             modalOpen: false,
             mode: 'create',
             formAction: '{{ route('superadmin.banners.store') }}',
             bannerType: 'template',
             actionPreset: '/pelanggan/catalog',
             customUrl: '',
             actionPresets: {{ json_encode($actionPresets) }},
             bannerData: {
                 id: null,
                 title: '',
                 type: 'template',
                 badge_text: '',
                 subtitle: '',
                 description: '',
                 promo_code: '',
                 action_url: '/pelanggan/catalog',
                 action_text: 'Lihat Katalog',
                 background_color: 'emerald',
                 sort_order: 0,
                 is_active: true,
                 starts_at: '',
                 ends_at: '',
                 image_url: null
             },
             handleActionPresetChange() {
                 if (this.actionPreset === 'custom') {
                     this.bannerData.action_url = this.customUrl;
                 } else {
                     this.bannerData.action_url = this.actionPreset;
                     const preset = this.actionPresets[this.actionPreset];
                     if (preset && preset.default_text) {
                         const knownDefaults = Object.values(this.actionPresets).map(p => p.default_text);
                         if (!this.bannerData.action_text || knownDefaults.includes(this.bannerData.action_text)) {
                             this.bannerData.action_text = preset.default_text;
                         }
                     }
                 }
             },
             openModal(payload) {
                 this.mode = payload.mode || 'create';
                 if (this.mode === 'edit') {
                     const b = payload.banner;
                     this.bannerData = {
                         id: b.id,
                         title: b.title || '',
                         type: b.type || 'template',
                         badge_text: b.badge_text || '',
                         subtitle: b.subtitle || '',
                         description: b.description || '',
                         promo_code: b.promo_code || '',
                         action_url: b.action_url || '',
                         action_text: b.action_text || '',
                         background_color: b.background_color || 'emerald',
                         sort_order: b.sort_order ?? 0,
                         is_active: Boolean(b.is_active),
                         starts_at: b.starts_at ? b.starts_at.substring(0, 16) : '',
                         ends_at: b.ends_at ? b.ends_at.substring(0, 16) : '',
                         image_url: b.image_url || null
                     };
                     this.bannerType = b.type || 'template';
                     this.formAction = '/superadmin/banners/' + b.id;

                     const knownUrls = Object.keys(this.actionPresets).filter(k => k !== '');
                     if (!b.action_url) {
                         this.actionPreset = '';
                         this.customUrl = '';
                     } else if (knownUrls.includes(b.action_url)) {
                         this.actionPreset = b.action_url;
                         this.customUrl = '';
                     } else {
                         this.actionPreset = 'custom';
                         this.customUrl = b.action_url;
                     }
                 } else {
                     this.actionPreset = '/pelanggan/catalog';
                     this.customUrl = '';
                     this.bannerData = {
                         id: null,
                         title: '',
                         type: 'template',
                         badge_text: '',
                         subtitle: '',
                         description: '',
                         promo_code: '',
                         action_url: '/pelanggan/catalog',
                         action_text: 'Lihat Katalog',
                         background_color: 'emerald',
                         sort_order: 0,
                         is_active: true,
                         starts_at: '',
                         ends_at: '',
                         image_url: null
                     };
                     this.bannerType = 'template';
                     this.formAction = '{{ route('superadmin.banners.store') }}';
                 }
                 this.modalOpen = true;
             },
             closeModal() {
                 this.modalOpen = false;
             }
         }">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold shadow-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <p>&bull; {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- 3 Metric Cards (Grid 1 x 3) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Total Banner</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($totalCount) }}</div>
                <div class="text-[11px] text-slate-400">Semua banner terdaftar</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Banner Aktif Tayang</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-2xl font-bold text-emerald-700">{{ number_format($activeCount) }}</div>
                <div class="text-[11px] text-emerald-600 font-medium">Tampil di beranda customer</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Banner Nonaktif</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <div class="text-2xl font-bold text-slate-600">{{ number_format($inactiveCount) }}</div>
                <div class="text-[11px] text-slate-400">Diarsipkan / tidak muncul</div>
            </div>
        </div>

        <!-- Master Table Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <!-- Header Controls: Judul di Atas, Filter & Tombol di Bawah Teks (Sesuai Desain Halaman Poin Sanksi) -->
            <div class="p-5 border-b border-slate-100 space-y-3.5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">
                            Daftar Banner Promosi
                        </h3>
                    </div>

                    <!-- Tombol Tambah Banner di Ujung Kanan Atas Header -->
                    <button type="button" 
                            @click="openModal({ mode: 'create' })"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs sm:text-sm shadow-xs transition flex items-center gap-1.5 cursor-pointer shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah Banner</span>
                    </button>
                </div>

                <!-- Filters & Search Form (Di Bawah Teks Judul) -->
                <form method="GET" action="{{ route('superadmin.banners.index') }}" class="flex flex-wrap items-center gap-3">
                    <!-- 1. Search Input (Posisi Pertama) -->
                    <div class="relative w-56 sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari banner, promo, voucher..." 
                               class="w-full text-xs border-slate-200 rounded-xl pl-9 pr-3.5 py-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs placeholder:text-xs placeholder-slate-400">
                    </div>

                    <!-- 2. Filter Format Dropdown (Posisi Kedua) -->
                    <div class="flex items-center space-x-1.5 shrink-0">
                        <label for="type_filter" class="text-xs font-semibold text-slate-600 shrink-0">Format:</label>
                        <select id="type_filter" name="type" onchange="this.form.submit()"
                                class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                            <option value="">Semua Format</option>
                            <option value="template" {{ request('type') === 'template' ? 'selected' : '' }}>Template Teks</option>
                            <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Poster Gambar</option>
                        </select>
                    </div>

                    <!-- 3. Status Filter (Segmented Tab Sesuai Ukuran Dropdown Format) -->
                    <div class="flex items-center space-x-1.5 shrink-0">
                        <label class="text-xs font-semibold text-slate-600 shrink-0">Status:</label>
                        <div class="bg-slate-100/90 p-0.5 rounded-xl inline-flex items-center gap-0.5 shrink-0 border border-slate-200/80">
                            <a href="{{ route('superadmin.banners.index', array_merge(request()->except('status', 'page'), ['status' => request('status') === 'active' ? '' : 'active'])) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'active' ? 'bg-white text-emerald-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-800' }}">
                                Aktif
                            </a>
                            <a href="{{ route('superadmin.banners.index', array_merge(request()->except('status', 'page'), ['status' => request('status') === 'inactive' ? '' : 'inactive'])) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'inactive' ? 'bg-white text-slate-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-800' }}">
                                Nonaktif
                            </a>
                        </div>
                    </div>

                    <!-- 4. Reset Button -->
                    @if (request('search') || request('type') || request('status'))
                        <a href="{{ route('superadmin.banners.index') }}" 
                           title="Reset Filter"
                           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Table of Banners (Identik dengan Tabel Poin Sanksi) -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Judul Banner</th>
                            <th class="px-5 py-3">Format</th>
                            <th class="px-5 py-3">Tautan Tujuan</th>
                            <th class="px-5 py-3">Periode</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($banners as $banner)
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- Judul Banner Bersih -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="font-bold text-slate-800 text-xs">{{ $banner->title }}</span>
                                </td>

                                <!-- Format -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="font-medium text-slate-700 text-xs">
                                        @if ($banner->type === 'image')
                                            Poster Gambar
                                        @else
                                            Template ({{ ucfirst($banner->background_color) }})
                                        @endif
                                    </span>
                                </td>

                                <!-- Tautan Tujuan -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($banner->action_url)
                                        <span class="font-medium text-slate-700 text-xs">
                                            {{ $banner->action_destination_label }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Tanpa tautan</span>
                                    @endif
                                </td>

                                <!-- Periode -->
                                <td class="px-5 py-3.5 whitespace-nowrap text-slate-600">
                                    @if ($banner->starts_at || $banner->ends_at)
                                        <div>{{ $banner->starts_at ? $banner->starts_at->format('d/m/Y') : 'Mulai sekarang' }}</div>
                                        <div class="text-[11px] text-slate-400">s/d {{ $banner->ends_at ? $banner->ends_at->format('d/m/Y') : 'Permanen' }}</div>
                                    @else
                                        <span class="text-slate-500">Selalu Tayang</span>
                                    @endif
                                </td>

                                <!-- Status Sanksi / Banner -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <form method="POST" action="{{ route('superadmin.banners.toggle', $banner) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                title="Klik untuk {{ $banner->is_active ? 'menonaktifkan' : 'mengaktifkan' }}"
                                                class="inline-flex items-center gap-1.5 text-xs font-medium cursor-pointer transition hover:opacity-80 {{ $banner->is_active ? 'text-emerald-700' : 'text-slate-400' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $banner->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            <span>{{ $banner->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </button>
                                    </form>
                                </td>

                                <!-- Action -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center space-x-2">
                                        <button type="button" 
                                                @click="openModal({ mode: 'edit', banner: {{ json_encode($banner) }} })"
                                                class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition cursor-pointer">
                                            Edit
                                        </button>
                                        <span class="text-slate-200">&bull;</span>
                                        <form method="POST" action="{{ route('superadmin.banners.destroy', $banner) }}" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus banner ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-xs font-semibold text-rose-500 hover:text-rose-700 transition cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">Tidak ada banner promosi</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Belum ada banner yang sesuai dengan filter pencarian Anda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($banners->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $banners->links() }}
                </div>
            @endif
        </div>

        <!-- ==================== MODAL FORM BANNER (TAMBAH & EDIT) ==================== -->
        <div x-show="modalOpen" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
             @keydown.escape.window="closeModal()">
            
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 w-full max-w-xl max-h-[90vh] flex flex-col my-auto overflow-hidden"
                 @click.outside="closeModal()">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 shrink-0">
                    <h3 class="font-bold text-slate-800 text-sm" x-text="mode === 'edit' ? 'Edit Banner Promosi' : 'Tambah Banner Promosi Baru'"></h3>
                    <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
                </div>

                <!-- Modal Form -->
                <form method="POST" :action="formAction" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                    @csrf
                    <template x-if="mode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="p-6 space-y-4 overflow-y-auto flex-1 overscroll-contain">
                        
                        <!-- Pilihan Format Banner -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Format Tampilan Banner</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="border rounded-xl p-3 flex items-start gap-2.5 cursor-pointer transition"
                                       :class="bannerType === 'template' ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-200 hover:bg-slate-50'">
                                    <input type="radio" name="type" value="template" x-model="bannerType" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <span class="block text-xs font-bold text-slate-900">Template Teks Dinamis</span>
                                        <span class="block text-[11px] text-slate-500 mt-0.5">Judul, deskripsi, voucher, dan warna gradasi otomatis.</span>
                                    </div>
                                </label>
                                <label class="border rounded-xl p-3 flex items-start gap-2.5 cursor-pointer transition"
                                       :class="bannerType === 'image' ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-200 hover:bg-slate-50'">
                                    <input type="radio" name="type" value="image" x-model="bannerType" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <span class="block text-xs font-bold text-slate-900">Poster Gambar Penuh</span>
                                        <span class="block text-[11px] text-slate-500 mt-0.5">Unggah gambar poster promo desain Anda.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Judul Banner -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Judul Banner <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" x-model="bannerData.title" required placeholder="Contoh: Voucher Relaksasi Spesial Weekend"
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <!-- Khusus Template Teks -->
                        <div x-show="bannerType === 'template'" class="space-y-4 pt-1 border-t border-slate-100">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Badge / Tag Promo</label>
                                    <input type="text" name="badge_text" x-model="bannerData.badge_text" placeholder="Contoh: Promo Spesial"
                                           class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kode Voucher (Opsional)</label>
                                    <input type="text" name="promo_code" x-model="bannerData.promo_code" placeholder="Contoh: HEMAT25"
                                           class="w-full text-xs font-mono uppercase rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Sub Judul / Headline</label>
                                <input type="text" name="subtitle" x-model="bannerData.subtitle" placeholder="Contoh: Diskon Rp 25.000 untuk Pesanan Pertama"
                                       class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi Singkat</label>
                                <textarea name="description" x-model="bannerData.description" rows="2" placeholder="Ketentuan ringkas promo..."
                                          class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Warna Latar Belakang</label>
                                <select name="background_color" x-model="bannerData.background_color" class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="emerald">Emerald Hijau</option>
                                    <option value="teal">Deep Teal</option>
                                    <option value="slate">Dark Slate</option>
                                    <option value="amber">Warm Amber</option>
                                    <option value="indigo">Indigo Royal</option>
                                    <option value="rose">Soft Rose</option>
                                </select>
                            </div>
                        </div>

                        <!-- Khusus Poster Gambar -->
                        <div x-show="bannerType === 'image'" class="space-y-3 pt-1 border-t border-slate-100">
                            <template x-if="mode === 'edit' && bannerData.image_url">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Gambar Saat Ini</label>
                                    <div class="relative w-48 h-24 rounded-xl overflow-hidden border border-slate-200 bg-slate-50">
                                        <img :src="bannerData.image_url" class="w-full h-full object-cover">
                                    </div>
                                </div>
                            </template>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    <span x-text="mode === 'edit' ? 'Ganti Poster Gambar (Opsional)' : 'Unggah Poster Gambar'"></span>
                                </label>
                                <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                       class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer border border-slate-300 rounded-xl bg-white">
                                <p class="text-[11px] text-slate-400 mt-1">Rasio rekomendasi 2:1 atau 16:9. Gambar otomatis dikompres oleh sistem.</p>
                            </div>
                        </div>

                        <!-- Tautan Aksi & Jadwal -->
                        <div class="border-t border-slate-100 pt-4 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Tautan Aksi (Halaman Menu)
                                    </label>
                                    <select x-model="actionPreset" @change="handleActionPresetChange()"
                                            class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white cursor-pointer">
                                        <option value="">Tanpa Tautan (Hanya Informasi)</option>
                                        <optgroup label="Halaman & Fitur Pelanggan">
                                            @foreach ($actionPresets as $path => $meta)
                                                @if ($path !== '')
                                                    <option value="{{ $path }}">{{ $meta['label'] }}</option>
                                                @endif
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Kustom">
                                            <option value="custom">Tautan Kustom / Link Luar...</option>
                                        </optgroup>
                                    </select>
                                    <p class="text-[11px] text-slate-400 mt-1">Halaman tujuan saat pelanggan mengklik banner.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Label Tombol Aksi</label>
                                    <input type="text" name="action_text" x-model="bannerData.action_text" placeholder="Contoh: Pesan Sekarang"
                                           class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    <p class="text-[11px] text-slate-400 mt-1">Teks pada tombol (misal: "Lihat Katalog", "Pesan Sekarang").</p>
                                </div>
                            </div>

                            <!-- Input Tautan Kustom (Hanya muncul jika memilih opsi Tautan Kustom) -->
                            <div x-show="actionPreset === 'custom'" x-cloak>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Masukkan URL / Tautan Kustom
                                </label>
                                <input type="text" x-model="customUrl" 
                                       @input="bannerData.action_url = customUrl"
                                       placeholder="Contoh: https://wa.me/628123456789 atau /halaman-khusus"
                                       class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <p class="text-[11px] text-slate-400 mt-1">Dapat berupa tautan eksternal (WhatsApp, medsos, website) atau rute internal.</p>
                            </div>

                            <!-- Input tersembunyi yang menyimpan nilai akhir action_url untuk dikirim ke backend -->
                            <input type="hidden" name="action_url" :value="bannerData.action_url">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mulai Tayang (Opsional)</label>
                                    <input type="datetime-local" name="starts_at" x-model="bannerData.starts_at"
                                           class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Selesai Tayang (Opsional)</label>
                                    <input type="datetime-local" name="ends_at" x-model="bannerData.ends_at"
                                           class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Urutan Prioritas</label>
                                    <input type="number" name="sort_order" x-model="bannerData.sort_order" min="0"
                                           class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                                <div class="pt-2 sm:pt-4">
                                    <label class="inline-flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" :checked="bannerData.is_active"
                                               class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-xs font-semibold text-slate-700">Status Aktif Tayang</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Actions -->
                    <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50 flex items-center justify-end space-x-2 shrink-0">
                        <button type="button" @click="closeModal()"
                                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs transition cursor-pointer">
                            <span x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Banner'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
