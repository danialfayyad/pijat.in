<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800 leading-tight">
            {{ __('Pusat Manajemen FAQ & Bantuan Resmi') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{
             modalOpen: false,
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
                <div class="text-[11px] text-slate-400">Seluruh artikel panduan</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">FAQ Pelanggan</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <div class="text-2xl font-bold text-blue-700">{{ number_format($counts['pelanggan']) }}</div>
                <div class="text-[11px] text-slate-400">Tampil di portal pelanggan</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">FAQ Mitra</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-2xl font-bold text-emerald-700">{{ number_format($counts['mitra']) }}</div>
                <div class="text-[11px] text-slate-400">Tampil di portal terapis mitra</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">FAQ Umum</span>
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                </div>
                <div class="text-2xl font-bold text-indigo-700">{{ number_format($counts['umum']) }}</div>
                <div class="text-[11px] text-slate-400">Tampil untuk semua pengguna</div>
            </div>
        </div>

        <!-- Filter Target Audiens Tabs (Di Luar Tabel, Sesuai Standar Sistem) -->
        <div class="flex items-center gap-2.5 overflow-x-auto pb-1 scrollbar-none">
            <!-- Tab Semua -->
            <a href="{{ route('superadmin.faqs.index', array_merge(request()->except('audience', 'page'), ['audience' => 'semua'])) }}"
               class="px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ $currentAudience === 'semua' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/90' }}">
                Semua
            </a>

            <!-- Tab Pelanggan -->
            <a href="{{ route('superadmin.faqs.index', array_merge(request()->except('audience', 'page'), ['audience' => 'pelanggan'])) }}"
               class="px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ $currentAudience === 'pelanggan' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/90' }}">
                Pelanggan
            </a>

            <!-- Tab Mitra -->
            <a href="{{ route('superadmin.faqs.index', array_merge(request()->except('audience', 'page'), ['audience' => 'mitra'])) }}"
               class="px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-bold transition whitespace-nowrap {{ $currentAudience === 'mitra' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/90' }}">
                Mitra
            </a>
        </div>

        <!-- Master Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Header Toolbar -->
            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Daftar Pertanyaan & Jawaban (FAQ)</h3>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    <!-- Search & Filter Form -->
                    <form method="GET" action="{{ route('superadmin.faqs.index') }}" class="flex items-center gap-2 flex-wrap">
                        <input type="hidden" name="audience" value="{{ $currentAudience }}">

                        <!-- Search Input -->
                        <div class="relative w-48 sm:w-56">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}" 
                                   placeholder="Cari pertanyaan/jawaban..." 
                                   class="w-full text-xs border border-slate-200 rounded-xl pl-8 pr-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        </div>

                        <!-- Filter Kategori Dropdown -->
                        @if ($categories->isNotEmpty())
                            <select name="category" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 py-1.5 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" @selected($selectedCategory === $cat)>{{ $cat }}</option>
                                @endforeach
                            </select>
                        @endif
                    </form>

                    <!-- Tambah FAQ Button -->
                    <button type="button"
                            @click="openModal({ mode: 'create' })"
                            class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <span>Tambah FAQ</span>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5 w-12 text-center">No</th>
                            <th class="px-5 py-3.5">Pertanyaan & Jawaban</th>
                            <th class="px-5 py-3.5 w-32">Kategori</th>
                            <th class="px-5 py-3.5 w-28 text-center">Target</th>
                            <th class="px-5 py-3.5 w-24 text-center">Status</th>
                            <th class="px-5 py-3.5 w-28 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($faqs as $item)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-4 text-center font-mono text-slate-400">
                                    {{ $loop->iteration + ($faqs->currentPage() - 1) * $faqs->perPage() }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-800 text-sm">
                                        {{ $item->question }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                        {{ $item->answer }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $item->category }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    @if ($item->target_audience === 'pelanggan')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            Pelanggan
                                        </span>
                                    @elseif ($item->target_audience === 'mitra')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Mitra
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            Semua
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <form method="POST" action="{{ route('superadmin.faqs.toggle', $item) }}">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold transition cursor-pointer {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-400 hover:bg-slate-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $item->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                            <span>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right space-x-1.5">
                                    <button type="button"
                                            @click="openModal({
                                                mode: 'edit',
                                                faq: {{ json_encode($item) }}
                                            })"
                                            class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition"
                                            title="Edit FAQ">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <form method="POST" action="{{ route('superadmin.faqs.destroy', $item) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel FAQ ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus FAQ">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400 text-xs">
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
    </div>
</x-app-layout>

