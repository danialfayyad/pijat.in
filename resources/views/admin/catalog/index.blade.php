<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                {{ $currentStatus === 'aktif' ? __('Katalog Layanan Pijat') : __('Katalog Layanan Nonaktif') }}
            </h2>
            <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $currentStatus === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                {{ $services->count() }} {{ $currentStatus === 'aktif' ? 'Layanan Aktif' : 'Layanan Nonaktif' }}
            </span>
        </div>
    </x-slot>

    <div x-data="catalogManagement()" class="py-8 px-6 sm:px-8 lg:px-10 space-y-6">

        <!-- Alert Notifikasi -->
        @if (session('success') || session('status'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-xl shadow-xs flex items-center justify-between text-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') ?? session('status') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-xl shadow-xs text-sm">
                <div class="flex items-center mb-1 font-semibold">
                    <svg class="w-5 h-5 mr-2 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Terdapat kesalahan pada input:
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 ml-7">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Ringkasan Statistik -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Layanan</p>
                <p class="text-2xl font-black text-slate-800 mt-1">{{ $activeCount + $inactiveCount }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Keseluruhan jenis pijat</p>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Layanan Aktif</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $activeCount }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Tersedia untuk dipesan</p>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Layanan Nonaktif</p>
                <p class="text-2xl font-black text-slate-800 mt-1">{{ $inactiveCount }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Diarsipkan / ditutup</p>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cakupan Wilayah</p>
                <p class="text-2xl font-black text-indigo-600 mt-1">Global</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Harga flat nasional</p>
            </div>
        </div>

        <!-- Daftar Layanan Pijat -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
                <div class="space-y-2">
                    <h3 class="font-bold text-slate-800 text-base leading-tight">
                        {{ $currentStatus === 'aktif' ? 'Daftar Layanan Aktif' : 'Daftar Layanan Nonaktif' }}
                    </h3>

                    <!-- Quick Filter Search (Rata kiri di bawah teks judul) -->
                    <div class="relative w-full sm:w-64">
                        <input type="text" x-model="searchQuery" placeholder="Cari nama layanan..."
                            class="w-full text-xs rounded-lg border-slate-200 pl-8 pr-3 py-2 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Tombol Aksi di Sebelah Kanan (Ukuran Lebar w-52, Tinggi h-10, & Rounded-lg Sama Persis) -->
                <div class="flex items-center space-x-2.5 shrink-0 self-start sm:self-end">
                    <!-- Tombol Navigasi / Redirect Antar Katalog (Tema Outline) -->
                    @if ($currentStatus === 'aktif')
                        <a href="{{ route('admin.catalog.index', ['status' => 'nonaktif']) }}" 
                            class="inline-flex items-center justify-center w-52 h-10 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition hover:shadow-xs cursor-pointer shrink-0">
                            <svg class="w-3.5 h-3.5 mr-1.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            <span>Lihat Katalog Nonaktif</span>
                            <span class="ml-2 px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-bold shrink-0">
                                {{ $inactiveCount }}
                            </span>
                        </a>
                    @else
                        <a href="{{ route('admin.catalog.index', ['status' => 'aktif']) }}" 
                            class="inline-flex items-center justify-center w-52 h-10 rounded-lg border border-emerald-600 bg-white hover:bg-emerald-50 text-emerald-700 text-xs font-semibold shadow-2xs transition hover:shadow-xs cursor-pointer shrink-0">
                            <svg class="w-3.5 h-3.5 mr-1.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali ke Katalog Aktif</span>
                            <span class="ml-2 px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold shrink-0">
                                {{ $activeCount }}
                            </span>
                        </a>
                    @endif

                    <!-- Tombol Tambah Layanan Baru (Solid Emerald, Ukuran Sama Persis w-52 h-10) -->
                    <button type="button" @click="$dispatch('open-create-modal')"
                        class="inline-flex items-center justify-center w-52 h-10 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-2xs transition hover:shadow-xs cursor-pointer shrink-0">
                        <svg class="w-3.5 h-3.5 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Layanan</span>
                    </button>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($services as $service)
                    <div x-show="matchesSearch('{{ strtolower($service->name) }}', '{{ strtolower($service->description ?? '') }}')"
                        class="p-5 sm:p-6 transition hover:bg-slate-50/70 {{ $service->is_active ? '' : 'bg-slate-50/50' }}">
                        
                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                            
                            <!-- Detail Layanan Kiri -->
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center space-x-2.5 flex-wrap gap-y-1">
                                    <h4 class="font-bold text-slate-900 text-lg leading-tight">{{ $service->name }}</h4>
                                    
                                    @if ($service->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-600 leading-relaxed max-w-3xl">
                                    {{ $service->description ?: 'Belum ada deskripsi layanan.' }}
                                </p>

                                <!-- Paket Durasi & Harga -->
                                <div class="pt-2">
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Pilihan Durasi & Tarif Harga:</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 max-w-2xl">
                                        @forelse ($service->packages as $pkg)
                                            <div class="flex items-center justify-between p-2.5 rounded-xl border {{ $pkg->is_active ? 'bg-white border-slate-200 shadow-2xs' : 'bg-slate-100 border-slate-200 opacity-60' }}">
                                                <div class="flex items-center space-x-2">
                                                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0">
                                                        {{ $pkg->duration_minutes }}'
                                                    </span>
                                                    <div>
                                                        <span class="text-xs font-bold text-slate-800 block">{{ $pkg->duration_minutes }} Menit</span>
                                                        <span class="text-xs font-semibold text-emerald-700">{{ $pkg->formatted_price }}</span>
                                                    </div>
                                                </div>

                                                <!-- Status Mini Toggle Paket -->
                                                <form method="POST" action="{{ route('admin.catalog.packages.toggle', $pkg) }}">
                                                    @csrf
                                                    @if ($pkg->is_active)
                                                        <button type="button" title="Nonaktifkan paket ini"
                                                            onclick="confirmAction({
                                                                title: 'Nonaktifkan Paket?',
                                                                message: 'Paket ini tidak akan muncul pada pilihan pemesanan customer.',
                                                                type: 'warning',
                                                                confirmText: 'Nonaktifkan',
                                                                form: this.closest('form')
                                                            })"
                                                            class="p-1 text-xs rounded-lg transition text-emerald-600 hover:bg-emerald-50 cursor-pointer">
                                                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">
                                                                Aktif
                                                            </span>
                                                        </button>
                                                    @else
                                                        <button type="submit" title="Aktifkan paket ini"
                                                            class="p-1 text-xs rounded-lg transition text-slate-400 hover:bg-slate-200 cursor-pointer">
                                                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-slate-200 text-slate-500">
                                                                Off
                                                            </span>
                                                        </button>
                                                    @endif
                                                </form>
                                            </div>
                                        @empty
                                            <p class="text-xs text-slate-400 italic">Belum ada paket durasi.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Aksi Kanan -->
                            <div class="flex items-center space-x-2 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                                <!-- Tombol Toggle Status Layanan -->
                                <form method="POST" action="{{ route('admin.catalog.toggle', $service) }}">
                                    @csrf
                                    @if ($service->is_active)
                                        <button type="button" 
                                            onclick="confirmAction({
                                                title: 'Nonaktifkan Layanan?',
                                                message: 'Layanan akan dipindahkan ke katalog nonaktif dan tidak dapat dipesan.',
                                                type: 'warning',
                                                confirmText: 'Nonaktifkan',
                                                form: this.closest('form')
                                            })"
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                                            Nonaktifkan
                                        </button>
                                    @else
                                        <button type="submit" 
                                            class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition cursor-pointer">
                                            Aktifkan Kembali
                                        </button>
                                    @endif
                                </form>

                                <!-- Tombol Edit -->
                                <button type="button" 
                                    @click="openEditModal(@js($service), @js($service->packages))"
                                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 text-slate-700 hover:bg-slate-100 shadow-2xs transition cursor-pointer">
                                    Edit
                                </button>

                                <!-- Tombol Hapus -->
                                <form method="POST" action="{{ route('admin.catalog.destroy', $service) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" title="Hapus Layanan"
                                        onclick="confirmAction({
                                            title: 'Hapus Layanan?',
                                            message: 'Layanan beserta seluruh paket durasinya akan dihapus permanen.',
                                            type: 'danger',
                                            confirmText: 'Hapus Layanan',
                                            form: this.closest('form')
                                        })"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        @if ($currentStatus === 'nonaktif')
                            <h4 class="font-bold text-slate-700 text-sm">Tidak Ada Layanan Nonaktif</h4>
                            <p class="text-xs text-slate-500 mt-1">Seluruh layanan pijat saat ini berstatus aktif dan tersedia untuk dipesan.</p>
                            <a href="{{ route('admin.catalog.index', ['status' => 'aktif']) }}"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl hover:bg-emerald-700 transition">
                                ← Lihat Layanan Aktif
                            </a>
                        @else
                            <h4 class="font-bold text-slate-700 text-sm">Belum Ada Layanan di Katalog</h4>
                            <p class="text-xs text-slate-500 mt-1">Mulai dengan menambahkan layanan baru ke dalam sistem.</p>
                            <button type="button" @click="$dispatch('open-create-modal')"
                                class="mt-4 px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl hover:bg-emerald-700 transition">
                                Tambah Layanan Sekarang
                            </button>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ==================== MODAL TAMBAH LAYANAN BARU ==================== -->
        <div x-show="isCreateModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs overflow-y-auto"
            @keydown.escape.window="isCreateModalOpen = false">
            
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 w-full max-w-xl overflow-hidden my-8"
                @click.outside="isCreateModalOpen = false">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm">
                            +
                        </div>
                        <h3 class="font-bold text-slate-800 text-base">Tambah Layanan Baru</h3>
                    </div>
                    <button type="button" @click="isCreateModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <!-- Modal Form -->
                <form method="POST" action="{{ route('admin.catalog.store') }}" class="p-6 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Nama Layanan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="Contoh: Aromatherapy Herbal Massage"
                            class="w-full text-sm rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Deskripsi Layanan
                        </label>
                        <textarea name="description" rows="3" placeholder="Jelaskan manfaat, teknik pemijatan, dan perlengkapan..."
                            class="w-full text-sm rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <!-- Pilihan Paket Durasi & Harga -->
                    <div class="border-t border-slate-100 pt-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Pilihan Durasi & Harga Paket <span class="text-rose-500">*</span>
                                </label>
                                <p class="text-[11px] text-slate-400">Harga tetap berlaku nasional tanpa add-on.</p>
                            </div>
                            <button type="button" @click="addCreatePackage()" 
                                class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-lg transition">
                                + Tambah Durasi
                            </button>
                        </div>

                        <div class="space-y-2.5">
                            <template x-for="(pkg, idx) in createPackages" :key="idx">
                                <div class="flex items-center space-x-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                    <div class="w-1/3">
                                        <label class="block text-[10px] text-slate-500 font-medium mb-0.5">Durasi (Menit)</label>
                                        <input type="number" :name="'packages['+idx+'][duration_minutes]'" x-model="pkg.duration_minutes" required min="15" max="360" step="15"
                                            class="w-full text-xs font-semibold rounded-lg border-slate-300 py-1.5 focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] text-slate-500 font-medium mb-0.5">Tarif Harga (Rp)</label>
                                        <input type="number" :name="'packages['+idx+'][price]'" x-model="pkg.price" required min="0" step="5000" placeholder="100000"
                                            class="w-full text-xs font-semibold rounded-lg border-slate-300 py-1.5 focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <button type="button" @click="removeCreatePackage(idx)" :disabled="createPackages.length <= 1"
                                        class="mt-4 p-1.5 text-slate-400 hover:text-rose-600 rounded-md disabled:opacity-30">
                                        &times;
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="border-t border-slate-100 pt-4 flex items-center justify-end space-x-2">
                        <button type="button" @click="isCreateModalOpen = false"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs transition cursor-pointer">
                            Simpan Layanan Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL EDIT LAYANAN ==================== -->
        <div x-show="isEditModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs overflow-y-auto"
            @keydown.escape.window="isEditModalOpen = false">
            
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 w-full max-w-xl overflow-hidden my-8"
                @click.outside="isEditModalOpen = false">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-sm">
                            ✎
                        </div>
                        <h3 class="font-bold text-slate-800 text-base">Edit Layanan & Durasi Paket</h3>
                    </div>
                    <button type="button" @click="isEditModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <!-- Modal Form -->
                <form method="POST" :action="editFormAction" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Nama Layanan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="editingService.name" required
                            class="w-full text-sm rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Deskripsi Layanan
                        </label>
                        <textarea name="description" x-model="editingService.description" rows="3"
                            class="w-full text-sm rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div>
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" :checked="editingService.is_active"
                                class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs font-semibold text-slate-700">Status Layanan Aktif (Tampil di Aplikasi Customer)</span>
                        </label>
                    </div>

                    <!-- Pilihan Paket Durasi & Harga -->
                    <div class="border-t border-slate-100 pt-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Paket Durasi & Tarif Harga <span class="text-rose-500">*</span>
                                </label>
                                <p class="text-[11px] text-slate-400">Atur durasi dan harga resmi per paket.</p>
                            </div>
                            <button type="button" @click="addEditPackage()" 
                                class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-lg transition">
                                + Tambah Durasi
                            </button>
                        </div>

                        <div class="space-y-2.5">
                            <template x-for="(pkg, idx) in editingPackages" :key="idx">
                                <div class="flex items-center space-x-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                    <input type="hidden" :name="'packages['+idx+'][id]'" :value="pkg.id || ''">
                                    
                                    <div class="w-1/3">
                                        <label class="block text-[10px] text-slate-500 font-medium mb-0.5">Durasi (Menit)</label>
                                        <input type="number" :name="'packages['+idx+'][duration_minutes]'" x-model="pkg.duration_minutes" required min="15" max="360" step="15"
                                            class="w-full text-xs font-semibold rounded-lg border-slate-300 py-1.5 focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] text-slate-500 font-medium mb-0.5">Tarif Harga (Rp)</label>
                                        <input type="number" :name="'packages['+idx+'][price]'" x-model="pkg.price" required min="0" step="5000"
                                            class="w-full text-xs font-semibold rounded-lg border-slate-300 py-1.5 focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div class="pt-4">
                                        <label class="inline-flex items-center cursor-pointer" title="Aktifkan/nonaktifkan paket">
                                            <input type="checkbox" :name="'packages['+idx+'][is_active]'" value="1" :checked="pkg.is_active"
                                                class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        </label>
                                    </div>
                                    <button type="button" @click="removeEditPackage(idx)" :disabled="editingPackages.length <= 1"
                                        class="mt-4 p-1.5 text-slate-400 hover:text-rose-600 rounded-md disabled:opacity-30">
                                        &times;
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="border-t border-slate-100 pt-4 flex items-center justify-end space-x-2">
                        <button type="button" @click="isEditModalOpen = false"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs transition cursor-pointer">
                            Perbarui Layanan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Script Alpine.js Component -->
    <script>
        function catalogManagement() {
            return {
                searchQuery: '',
                isCreateModalOpen: false,
                isEditModalOpen: false,
                editFormAction: '',
                editingService: {},
                editingPackages: [],
                createPackages: [
                    { duration_minutes: 60, price: 100000 },
                    { duration_minutes: 90, price: 140000 },
                    { duration_minutes: 120, price: 180000 },
                ],

                init() {
                    window.addEventListener('open-create-modal', () => {
                        this.isCreateModalOpen = true;
                    });
                },

                matchesSearch(name, desc) {
                    if (!this.searchQuery.trim()) return true;
                    const q = this.searchQuery.toLowerCase();
                    return name.includes(q) || desc.includes(q);
                },

                addCreatePackage() {
                    const last = this.createPackages[this.createPackages.length - 1];
                    const nextDuration = last ? parseInt(last.duration_minutes) + 30 : 60;
                    const nextPrice = last ? parseInt(last.price) + 40000 : 100000;
                    this.createPackages.push({ duration_minutes: nextDuration, price: nextPrice });
                },

                removeCreatePackage(idx) {
                    if (this.createPackages.length > 1) {
                        this.createPackages.splice(idx, 1);
                    }
                },

                openEditModal(service, packages) {
                    this.editingService = { ...service };
                    this.editingPackages = packages.map(p => ({
                        id: p.id,
                        duration_minutes: p.duration_minutes,
                        price: parseFloat(p.price),
                        is_active: Boolean(p.is_active)
                    }));
                    this.editFormAction = `/admin/catalog/${service.id}`;
                    this.isEditModalOpen = true;
                },

                addEditPackage() {
                    const last = this.editingPackages[this.editingPackages.length - 1];
                    const nextDuration = last ? parseInt(last.duration_minutes) + 30 : 60;
                    const nextPrice = last ? parseInt(last.price) + 40000 : 100000;
                    this.editingPackages.push({ id: null, duration_minutes: nextDuration, price: nextPrice, is_active: true });
                },

                removeEditPackage(idx) {
                    if (this.editingPackages.length > 1) {
                        this.editingPackages.splice(idx, 1);
                    }
                }
            }
        }
    </script>
</x-app-layout>
