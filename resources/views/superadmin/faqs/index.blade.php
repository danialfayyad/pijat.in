<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800 leading-tight">
            {{ __('Pusat Manajemen FAQ & Bantuan Resmi') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{
             modalOpen: false,
             detailOpen: false,
             selectedFaq: null,
             mode: 'create',
             formAction: '{{ route('superadmin.faqs.store') }}',
             methodSpoof: 'POST',
             formData: {
                 id: null,
                 target_audience: 'semua',
                 category: '',
                 question: '',
                 answer: '',
                 is_active: true
             },
             openDetail(faq) {
                 this.selectedFaq = faq;
                 this.detailOpen = true;
             },
             closeDetail() {
                 this.detailOpen = false;
                 this.selectedFaq = null;
             },
             openModal(payload) {
                 this.mode = payload.mode || 'create';
                 if (this.mode === 'edit') {
                     this.formData.id = payload.faq.id;
                     this.formData.target_audience = payload.faq.target_audience || 'semua';
                     this.formData.category = payload.faq.category || '';
                     this.formData.question = payload.faq.question || '';
                     this.formData.answer = payload.faq.answer || '';
                     this.formData.is_active = Boolean(payload.faq.is_active);
                     this.formAction = '/superadmin/faqs/' + payload.faq.id;
                     this.methodSpoof = 'PUT';
                 } else {
                     this.formData.id = null;
                     this.formData.target_audience = 'semua';
                     this.formData.category = '';
                     this.formData.question = '';
                     this.formData.answer = '';
                     this.formData.is_active = true;
                     this.formAction = '{{ route('superadmin.faqs.store') }}';
                     this.methodSpoof = 'POST';
                 }
                 this.modalOpen = true;
             },
             closeModal() {
                 this.modalOpen = false;
             }
         }">

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold shadow-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <p>&bull; {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Total FAQ</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($counts['semua']) }}</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">FAQ Pelanggan</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <div class="text-2xl font-bold text-blue-700">{{ number_format($counts['pelanggan']) }}</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">FAQ Mitra</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-2xl font-bold text-emerald-700">{{ number_format($counts['mitra']) }}</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">FAQ Umum</span>
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                </div>
                <div class="text-2xl font-bold text-indigo-700">{{ number_format($counts['umum']) }}</div>
            </div>
        </div>

        <!-- Master Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Header Controls: Judul di Atas, Filter & Tombol di Bawah Teks (Sesuai Desain Standar Sistem) -->
            <div class="p-5 border-b border-slate-100 space-y-3.5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">
                            Daftar Pertanyaan Bantuan (FAQ)
                        </h3>
                    </div>

                    <!-- Tombol Tambah FAQ di Ujung Kanan Atas Header -->
                    <button type="button" 
                            @click="openModal({ mode: 'create' })"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs sm:text-sm shadow-xs transition flex items-center gap-1.5 cursor-pointer shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah FAQ</span>
                    </button>
                </div>

                <!-- Filters & Search Form (Di Bawah Teks Judul) -->
                <form method="GET" action="{{ route('superadmin.faqs.index') }}" class="flex flex-wrap items-center gap-3">
                    <!-- 1. Search Input (Posisi Pertama) -->
                    <div class="relative w-56 sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}" 
                               placeholder="Cari pertanyaan bantuan..." 
                               class="w-full text-xs border-slate-200 rounded-xl pl-9 pr-3.5 py-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs placeholder:text-xs placeholder-slate-400">
                    </div>

                    <!-- 2. Filter Kategori Dropdown (Posisi Kedua) -->
                    @if ($categories->isNotEmpty())
                        <div class="flex items-center space-x-1.5 shrink-0">
                            <label for="category_filter" class="text-xs font-semibold text-slate-600 shrink-0">Kategori:</label>
                            <select id="category_filter" name="category" onchange="this.form.submit()"
                                    class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" @selected($selectedCategory === $cat)>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- 3. Target Audiens Segmented Tab (Ukuran proporsional sesuai standar sistem) -->
                    <div class="flex items-center space-x-1.5 shrink-0">
                        <label class="text-xs font-semibold text-slate-600 shrink-0">Target:</label>
                        <div class="bg-slate-100/90 p-0.5 rounded-xl inline-flex items-center gap-0.5 shrink-0 border border-slate-200/80">
                            <a href="{{ route('superadmin.faqs.index', array_merge(request()->except('audience', 'page'), ['audience' => 'semua'])) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $currentAudience === 'semua' ? 'bg-white text-emerald-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-800' }}">
                                Semua
                            </a>
                            <a href="{{ route('superadmin.faqs.index', array_merge(request()->except('audience', 'page'), ['audience' => 'pelanggan'])) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $currentAudience === 'pelanggan' ? 'bg-white text-emerald-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-800' }}">
                                Pelanggan
                            </a>
                            <a href="{{ route('superadmin.faqs.index', array_merge(request()->except('audience', 'page'), ['audience' => 'mitra'])) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $currentAudience === 'mitra' ? 'bg-white text-emerald-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-800' }}">
                                Mitra
                            </a>
                        </div>
                    </div>

                    <!-- 4. Reset Button -->
                    @if ($search || $selectedCategory || ($currentAudience !== 'semua'))
                        <a href="{{ route('superadmin.faqs.index') }}" 
                           title="Reset Filter"
                           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5">Pertanyaan</th>
                            <th class="px-5 py-3.5 w-48">Kategori</th>
                            <th class="px-5 py-3.5 w-32">Target</th>
                            <th class="px-5 py-3.5 w-24 text-center">Status</th>
                            <th class="px-5 py-3.5 w-28 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($faqs as $item)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-slate-800 text-xs">{{ $item->question }}</span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="font-medium text-slate-600 text-xs">{{ $item->category }}</span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($item->target_audience === 'pelanggan')
                                        <span class="font-semibold text-blue-700 text-xs">Pelanggan</span>
                                    @elseif ($item->target_audience === 'mitra')
                                        <span class="font-semibold text-emerald-700 text-xs">Mitra Terapis</span>
                                    @else
                                        <span class="font-medium text-slate-400 text-xs">Semua</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                    <form method="POST" action="{{ route('superadmin.faqs.toggle', $item) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                title="Klik untuk {{ $item->is_active ? 'menonaktifkan' : 'mengaktifkan' }}"
                                                class="inline-flex items-center gap-1.5 text-xs font-medium cursor-pointer transition hover:opacity-80 {{ $item->is_active ? 'text-emerald-700' : 'text-slate-400' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $item->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            <span>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center space-x-2">
                                        <button type="button"
                                                @click="openDetail({{ json_encode($item) }})"
                                                class="text-xs font-semibold text-slate-600 hover:text-slate-900 transition cursor-pointer">
                                            Detail
                                        </button>
                                        <span class="text-slate-200">&bull;</span>
                                        <button type="button"
                                                @click="openModal({
                                                    mode: 'edit',
                                                    faq: {{ json_encode($item) }}
                                                })"
                                                class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition cursor-pointer">
                                            Edit
                                        </button>
                                        <span class="text-slate-200">&bull;</span>
                                        <form method="POST" action="{{ route('superadmin.faqs.destroy', $item) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel FAQ ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-700 transition cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400 text-xs">
                                    Belum ada artikel FAQ pada kategori / filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($faqs->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $faqs->links() }}
                </div>
            @endif
        </div>

        <!-- Alpine Modal: Tambah / Edit FAQ -->
        <div x-show="modalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 space-y-4" @click.away="closeModal()">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-base text-slate-800" x-text="mode === 'edit' ? 'Edit Artikel FAQ' : 'Tambah Artikel FAQ Baru'">
                    </h3>
                    <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form method="POST" :action="formAction" class="space-y-4">
                    @csrf
                    <template x-if="methodSpoof === 'PUT'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Target Audiens -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Audiens <span class="text-rose-500">*</span></label>
                        <select name="target_audience" x-model="formData.target_audience" required class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                            <option value="semua">Semua (Umum)</option>
                            <option value="pelanggan">Pelanggan</option>
                            <option value="mitra">Mitra Terapis</option>
                        </select>
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Topik <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="category" 
                               x-model="formData.category"
                               required 
                               list="category-suggestions"
                               placeholder="Contoh: Pemesanan, Pembayaran, Kemitraan, Sanksi"
                               class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                        <datalist id="category-suggestions">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>

                    <!-- Pertanyaan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pertanyaan <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="question" 
                               x-model="formData.question"
                               required 
                               placeholder="Contoh: Bagaimana cara membatalkan pesanan pijat?"
                               class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <!-- Jawaban -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jawaban Solusi <span class="text-rose-500">*</span></label>
                        <textarea name="answer" 
                                  x-model="formData.answer"
                                  rows="4" 
                                  required 
                                  placeholder="Jelaskan langkah solusi yang jelas dan mudah dipahami..."
                                  class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <!-- Status Aktif -->
                    <div class="pt-1">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" x-model="formData.is_active" class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs font-semibold text-slate-700">Publikasikan (Aktif)</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="closeModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                            Simpan FAQ
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alpine Modal: Detail FAQ -->
        <div x-show="detailOpen" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 space-y-4" @click.away="closeDetail()">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-base text-slate-800">Rincian Artikel FAQ</h3>
                        <template x-if="selectedFaq?.is_active">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Aktif</span>
                            </span>
                        </template>
                        <template x-if="!selectedFaq?.is_active">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                <span>Nonaktif</span>
                            </span>
                        </template>
                    </div>
                    <button type="button" @click="closeDetail()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4 text-xs" x-show="selectedFaq">
                    <!-- Meta: Kategori & Target Audiens -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold" x-text="selectedFaq?.category"></span>
                        <span class="px-2.5 py-1 rounded-lg font-semibold"
                              :class="{
                                  'bg-blue-50 text-blue-700': selectedFaq?.target_audience === 'pelanggan',
                                  'bg-emerald-50 text-emerald-700': selectedFaq?.target_audience === 'mitra',
                                  'bg-slate-100 text-slate-600': selectedFaq?.target_audience === 'semua'
                              }"
                              x-text="selectedFaq?.target_audience === 'pelanggan' ? 'Audiens: Pelanggan' : (selectedFaq?.target_audience === 'mitra' ? 'Audiens: Mitra Terapis' : 'Audiens: Semua')">
                        </span>
                    </div>

                    <!-- Pertanyaan -->
                    <div>
                        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Pertanyaan</div>
                        <div class="font-bold text-slate-900 text-sm leading-snug" x-text="selectedFaq?.question"></div>
                    </div>

                    <!-- Jawaban Solusi -->
                    <div>
                        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Jawaban Solusi</div>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 leading-relaxed whitespace-pre-line text-xs" x-text="selectedFaq?.answer"></div>
                    </div>

                    <!-- Info Penulis & Pembaruan -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span x-text="selectedFaq?.creator ? 'Dibuat oleh: ' + selectedFaq.creator.name : ''"></span>
                        <span x-text="selectedFaq?.created_at ? 'Diperbarui: ' + new Date(selectedFaq.updated_at || selectedFaq.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : ''"></span>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="closeDetail()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

