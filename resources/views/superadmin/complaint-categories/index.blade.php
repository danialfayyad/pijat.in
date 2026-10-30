<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800 leading-tight">
            {{ __('Master Kategori Aduan') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{
             modalOpen: false,
             mode: 'create',
             formAction: '{{ route('superadmin.complaint-categories.store') }}',
             formData: {
                 id: null,
                 name: '',
                 penalty_points: 15,
                 description: '',
                 is_active: true
             },
             openModal(payload) {
                 this.mode = payload.mode || 'create';
                 if (this.mode === 'edit') {
                     this.formData.id = payload.category.id;
                     this.formData.name = payload.category.name;
                     this.formData.penalty_points = payload.category.penalty_points || 15;
                     this.formData.description = payload.category.description || '';
                     this.formData.is_active = Boolean(payload.category.is_active);
                     this.formAction = '/superadmin/complaint-categories/' + payload.category.id;
                 } else {
                     this.formData.id = null;
                     this.formData.name = '';
                     this.formData.penalty_points = 15;
                     this.formData.description = '';
                     this.formData.is_active = true;
                     this.formAction = '{{ route('superadmin.complaint-categories.store') }}';
                 }
                 this.modalOpen = true;
             },
             closeModal() {
                 this.modalOpen = false;
             }
         }"
         @open-category-modal.window="openModal($event.detail)">

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
                    <span class="text-xs font-medium">Total Kategori</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($totalCount) }}</div>
                <div class="text-[11px] text-slate-400">Semua jenis aduan</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Kategori Aktif</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-2xl font-bold text-emerald-700">{{ number_format($activeCount) }}</div>
                <div class="text-[11px] text-emerald-600 font-medium">Dapat dipilih pelapor</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-1">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-medium">Kategori Nonaktif</span>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <div class="text-2xl font-bold text-slate-600">{{ number_format($inactiveCount) }}</div>
                <div class="text-[11px] text-slate-400">Diarsipkan / tidak muncul</div>
            </div>
        </div>

        <!-- Master Table Card -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <!-- Header Controls -->
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="font-bold text-slate-800 text-base">
                    Daftar Kategori Aduan
                </h3>

                <div class="flex items-center gap-2.5">
                    <!-- Filter & Search Form -->
                    <form method="GET" action="{{ route('superadmin.complaint-categories.index') }}" class="flex items-center gap-2.5">
                        <div class="relative w-56 sm:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Cari nama kategori..." 
                                   class="w-full text-xs sm:text-sm border-slate-200 rounded-xl pl-9 pr-3.5 py-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs placeholder-slate-400">
                        </div>

                        <select name="status" onchange="this.form.submit()"
                                class="text-xs sm:text-sm font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>

                        @if (request('search') || request('status'))
                            <a href="{{ route('superadmin.complaint-categories.index') }}" 
                               title="Reset Filter"
                               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </form>

                    <!-- Tombol Tambah Kategori di Tabel -->
                    <button type="button" 
                            @click="$dispatch('open-category-modal', { mode: 'create' })"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs sm:text-sm shadow-xs transition flex items-center gap-1.5 cursor-pointer shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah Kategori</span>
                    </button>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Nama Kategori</th>
                            <th class="px-5 py-3 text-center">Bobot Poin</th>
                            <th class="px-5 py-3 text-center">Kasus Aduan</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($categories as $category)
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- Nama Kategori -->
                                <td class="px-5 py-3.5 font-bold text-slate-800">
                                    {{ $category->name }}
                                </td>

                                <!-- Bobot Poin Sanksi -->
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    <span class="font-bold text-rose-600 text-xs">+{{ $category->penalty_points }}</span>
                                    <span class="text-slate-400 text-[11px] font-normal">poin</span>
                                </td>

                                <!-- Kasus Aduan Terkait -->
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    @if ($category->complaints_count > 0)
                                        <span class="font-semibold text-slate-700 text-xs">{{ $category->complaints_count }}</span>
                                        <span class="text-slate-400 text-[11px]">kasus</span>
                                    @else
                                        <span class="text-slate-300 font-normal text-xs">&mdash;</span>
                                    @endif
                                </td>

                                <!-- Status Toggle -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <form method="POST" action="{{ route('superadmin.complaint-categories.toggle', $category) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                title="Klik untuk {{ $category->is_active ? 'menonaktifkan' : 'mengaktifkan' }}"
                                                class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-medium transition cursor-pointer hover:bg-slate-100 {{ $category->is_active ? 'text-emerald-700' : 'text-slate-400' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $category->is_active ? 'bg-emerald-500 ring-2 ring-emerald-100' : 'bg-slate-300' }}"></span>
                                            <span>{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </button>
                                    </form>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center space-x-2">
                                        <!-- Edit Button -->
                                        <button type="button" 
                                                @click="openModal({ mode: 'edit', category: {{ json_encode($category) }} })"
                                                class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition cursor-pointer">
                                            Edit
                                        </button>
                                        <span class="text-slate-200">&bull;</span>

                                        <!-- Delete Form with Confirmation Dialog -->
                                        <form id="delete-category-{{ $category->id }}" method="POST" action="{{ route('superadmin.complaint-categories.destroy', $category) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    onclick="confirmAction({
                                                        title: 'Hapus Kategori Aduan',
                                                        targetName: '{{ $category->name }}',
                                                        message: '{{ $category->complaints_count > 0 ? 'Kategori ini memiliki ' . $category->complaints_count . ' riwayat aduan. Sistem akan otomatis menonaktifkannya demi menjaga integritas data riwayat.' : 'Apakah Anda yakin ingin menghapus kategori aduan ini?' }}',
                                                        type: 'danger',
                                                        confirmText: 'Ya, Lanjutkan',
                                                        cancelText: 'Batal',
                                                        form: document.getElementById('delete-category-{{ $category->id }}')
                                                    })"
                                                    class="text-xs font-semibold text-rose-500 hover:text-rose-700 transition cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">Belum ada kategori aduan</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Tambahkan kategori aduan baru agar dapat dipilih oleh pelapor.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Tambah / Edit Kategori -->
        <div x-show="modalOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                 @click="closeModal()"></div>

            <!-- Modal Dialog Content -->
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200/80 space-y-5"
                     @click.stop>
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-slate-800 text-base" x-text="mode === 'create' ? 'Tambah Kategori Aduan' : 'Edit Kategori Aduan'"></h3>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Form -->
                    <form :action="formAction" method="POST" class="space-y-4">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <!-- Nama Kategori -->
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 block">
                                Nama Kategori <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   x-model="formData.name" 
                                   required 
                                   maxlength="100"
                                   placeholder="Contoh: Kualitas Layanan Tidak Sesuai" 
                                   class="w-full text-xs border-slate-200 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs placeholder-slate-400">
                        </div>

                        <!-- Bobot Poin Sanksi -->
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 block">
                                Bobot Poin Sanksi <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="penalty_points" 
                                       x-model="formData.penalty_points" 
                                       required 
                                       min="1" 
                                       max="150"
                                       placeholder="15" 
                                       class="w-full text-xs border-slate-200 rounded-xl px-3 py-2 pr-12 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs placeholder-slate-400 font-bold">
                                <span class="absolute right-3 top-2 text-xs font-semibold text-slate-400 pointer-events-none">Poin</span>
                            </div>
                            <p class="text-[10px] text-slate-400">Poin ini otomatis dijatuhkan ke akun pelanggar jika aduan terbukti bersalah.</p>
                        </div>

                        <!-- Status Aktif Checkbox (Hanya saat edit) -->
                        <template x-if="mode === 'edit'">
                            <div class="flex items-center space-x-2 pt-1">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" 
                                       id="is_active_checkbox" 
                                       name="is_active" 
                                       value="1" 
                                       x-model="formData.is_active"
                                       class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <label for="is_active_checkbox" class="text-xs font-semibold text-slate-700 cursor-pointer">
                                    Kategori Aktif (Dapat dipilih oleh pelapor)
                                </label>
                            </div>
                        </template>

                        <!-- Actions Buttons -->
                        <div class="flex items-center justify-end space-x-2.5 pt-3 border-t border-slate-100">
                            <button type="button" 
                                    @click="closeModal()" 
                                    class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200/80 rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition cursor-pointer">
                                <span x-text="mode === 'create' ? 'Simpan Kategori' : 'Perbarui Kategori'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>

