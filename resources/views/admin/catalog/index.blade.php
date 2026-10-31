<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ $currentStatus === 'aktif' ? __('Katalog Layanan Pijat') : __('Katalog Layanan Nonaktif') }}
        </h2>
    </x-slot>

    <div x-data="catalogManagement()" class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Alert Notifikasi -->
        @if (session('success') || session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-2xs flex items-center justify-between text-xs font-semibold">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') ?? session('status') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-2xs text-xs">
                <div class="flex items-center gap-2 mb-1 font-semibold">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Terdapat kesalahan pada input:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-6 text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Ringkasan Statistik Tenang -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Jenis Layanan</p>
                <div class="flex items-baseline gap-1.5 mt-1">
                    <span class="text-2xl font-bold text-slate-800">{{ $activeCount + $inactiveCount }}</span>
                    <span class="text-xs text-slate-400">layanan terdaftar</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Layanan Aktif</p>
                <div class="flex items-baseline gap-1.5 mt-1">
                    <span class="text-2xl font-bold text-emerald-600">{{ $activeCount }}</span>
                    <span class="text-xs text-slate-400">tersedia dipesan</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Layanan Nonaktif</p>
                <div class="flex items-baseline gap-1.5 mt-1">
                    <span class="text-2xl font-bold text-slate-700">{{ $inactiveCount }}</span>
                    <span class="text-xs text-slate-400">diarsipkan</span>
                </div>
            </div>
        </div>

        <!-- Toolbar Kontrol: Status Tabs, Search & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Filter Status Tabs (Meniru gaya tab yang sudah ada) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                <a href="{{ route('admin.catalog.index', ['status' => 'aktif']) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $currentStatus === 'aktif' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                    @if ($currentStatus === 'nonaktif')
                        &larr; Kembali ke Katalog Aktif
                    @else
                        Katalog Aktif
                    @endif
                    <span class="ml-1 opacity-90 font-mono text-[11px]">({{ $activeCount }})</span>
                </a>

                <a href="{{ route('admin.catalog.index', ['status' => 'nonaktif']) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $currentStatus === 'nonaktif' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                    @if ($currentStatus === 'aktif')
                        Lihat Katalog Nonaktif
                    @else
                        Katalog Nonaktif
                    @endif
                    <span class="ml-1 opacity-90 font-mono text-[11px]">({{ $inactiveCount }})</span>
                </a>
            </div>

            <!-- Search Bar & Tombol Tambah Layanan (Hijau Emerald) -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative w-full sm:w-64">
                    <input type="text" x-model="searchQuery" placeholder="Cari layanan..."
                        class="w-full text-xs rounded-xl border-slate-200 pl-8 pr-3 py-2.5 focus:border-emerald-500 focus:ring-emerald-500 bg-white placeholder:text-xs placeholder-slate-400 shadow-2xs">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <button type="button" @click="$dispatch('open-create-modal')"
                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition inline-flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Layanan</span>
                </button>
            </div>
        </div>

        <!-- Rich Service Cards Grid (2 Kolom) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @forelse ($services as $service)
                @php
                    $nameLower = strtolower($service->name);
                @endphp
                <div x-show="matchesSearch('{{ strtolower($service->name) }}', '{{ strtolower($service->description ?? '') }}')"
                    class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-sm transition-all p-6 flex flex-col justify-between space-y-5">
                    
                    <div class="space-y-4">
                        <!-- Top Header: Icon + Title + Status Dot/Badge -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0">
                                    @if (str_contains($nameLower, 'stone'))
                                        <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343a7.975 7.975 0 012.344 5.657 7.975 7.975 0 01-2.343 5.657z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                                        </svg>
                                    @elseif (str_contains($nameLower, 'refleksi') || str_contains($nameLower, 'kaki'))
                                        <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @elseif (str_contains($nameLower, 'herbal') || str_contains($nameLower, 'aromaterapi') || str_contains($nameLower, 'aromatherapy'))
                                        <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-base leading-tight">{{ $service->name }}</h4>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $service->packages->count() }} Pilihan Paket</p>
                                </div>
                            </div>

                            <!-- Status Dot/Badge -->
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium shrink-0 {{ $service->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $service->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-slate-600 leading-relaxed line-clamp-2 min-h-[2.5rem]">
                            {{ $service->description ?: 'Belum ada deskripsi layanan.' }}
                        </p>

                        <!-- Paket Durasi & Tarif Table -->
                        <div class="border border-slate-200/80 rounded-xl overflow-hidden bg-slate-50/30">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100/70 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200/70">
                                    <tr>
                                        <th class="py-2 px-3.5">Durasi</th>
                                        <th class="py-2 px-3.5">Tarif</th>
                                        <th class="py-2 px-3.5 text-right">Aksi Paket</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/50">
                                    @forelse ($service->packages as $pkg)
                                        <tr class="{{ $pkg->is_active ? 'bg-white' : 'bg-slate-50/60 text-slate-400' }}">
                                            <td class="py-2.5 px-3.5 font-semibold {{ $pkg->is_active ? 'text-slate-800' : 'text-slate-400' }}">
                                                {{ $pkg->duration_minutes }} Menit
                                            </td>
                                            <td class="py-2.5 px-3.5 font-bold {{ $pkg->is_active ? 'text-slate-900' : 'text-slate-400' }}">
                                                {{ $pkg->formatted_price }}
                                            </td>
                                            <td class="py-2.5 px-3.5 text-right">
                                                <form method="POST" action="{{ route('admin.catalog.packages.toggle', $pkg) }}" class="inline-block">
                                                    @csrf
                                                    @if ($pkg->is_active)
                                                        <button type="button" 
                                                            onclick="confirmAction({
                                                                title: 'Nonaktifkan Paket?',
                                                                message: 'Paket ini tidak akan muncul pada pilihan pemesanan customer.',
                                                                type: 'warning',
                                                                confirmText: 'Nonaktifkan',
                                                                form: this.closest('form')
                                                            })"
                                                            class="text-[11px] font-medium text-slate-400 hover:text-rose-600 transition cursor-pointer">
                                                            Nonaktifkan
                                                        </button>
                                                    @else
                                                        <button type="submit" 
                                                            class="text-[11px] font-medium text-emerald-600 hover:text-emerald-700 hover:underline cursor-pointer">
                                                            Aktifkan
                                                        </button>
                                                    @endif
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-3 px-3.5 text-center text-slate-400 italic text-[11px]">Belum ada paket durasi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Card Footer: Actions -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5">
                            <button type="button" 
                                @click="openEditModal(@js($service), @js($service->packages))"
                                class="px-2.5 py-1.5 rounded-lg font-medium text-slate-700 hover:text-emerald-700 hover:bg-slate-100 transition cursor-pointer">
                                Edit Layanan
                            </button>
                            <form method="POST" action="{{ route('admin.catalog.toggle', $service) }}" class="inline-block">
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
                                        class="px-2.5 py-1.5 rounded-lg font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition cursor-pointer">
                                        Nonaktifkan
                                    </button>
                                @else
                                    <button type="submit" 
                                        class="px-2.5 py-1.5 rounded-lg font-medium text-emerald-700 hover:text-emerald-800 hover:bg-emerald-50 transition cursor-pointer">
                                        Aktifkan Kembali
                                    </button>
                                @endif
                            </form>
                        </div>

                        <form method="POST" action="{{ route('admin.catalog.destroy', $service) }}" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="button" 
                                onclick="confirmAction({
                                    title: 'Hapus Layanan?',
                                    message: 'Layanan beserta seluruh paket durasinya akan dihapus permanen.',
                                    type: 'danger',
                                    confirmText: 'Hapus Layanan',
                                    form: this.closest('form')
                                })"
                                class="px-2.5 py-1.5 rounded-lg font-medium text-rose-600 hover:text-rose-700 hover:bg-rose-50 transition cursor-pointer">
                                Hapus
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 p-12 text-center text-slate-400">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    @if ($currentStatus === 'nonaktif')
                        <h4 class="font-bold text-slate-700 text-xs">Tidak Ada Layanan Nonaktif</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">Seluruh layanan pijat saat ini berstatus aktif dan tersedia untuk dipesan.</p>
                        <a href="{{ route('admin.catalog.index', ['status' => 'aktif']) }}"
                            class="mt-3 inline-flex items-center text-xs font-semibold text-emerald-700 hover:underline">
                            &larr; Kembali ke Katalog Aktif
                        </a>
                    @else
                        <h4 class="font-bold text-slate-700 text-xs">Belum Ada Layanan di Katalog</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">Mulai dengan menambahkan layanan baru ke dalam sistem.</p>
                        <button type="button" @click="$dispatch('open-create-modal')"
                            class="mt-3 inline-flex items-center text-xs font-semibold text-emerald-700 hover:underline cursor-pointer">
                            + Tambah Layanan Sekarang
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- ==================== MODAL TAMBAH LAYANAN BARU ==================== -->
        <div x-show="isCreateModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
            @keydown.escape.window="isCreateModalOpen = false">
            
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 w-full max-w-xl overflow-hidden my-8"
                @click.outside="isCreateModalOpen = false">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-sm">Tambah Layanan Baru</h3>
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
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Deskripsi Layanan
                        </label>
                        <textarea name="description" rows="3" placeholder="Jelaskan manfaat, teknik pemijatan, dan perlengkapan..."
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
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
                                            class="w-full text-xs font-semibold rounded-lg border-slate-300 py-1.5 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] text-slate-500 font-medium mb-0.5">Tarif Harga (Rp)</label>
                                        <input type="number" :name="'packages['+idx+'][price]'" x-model="pkg.price" required min="0" step="5000" placeholder="100000"
                                            class="w-full text-xs font-semibold rounded-lg border-slate-300 py-1.5 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
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
                            class="px-4 py-2 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs transition cursor-pointer">
                            Simpan Layanan Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL EDIT LAYANAN ==================== -->
        <div x-show="isEditModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
            @keydown.escape.window="isEditModalOpen = false">
            
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200/80 w-full max-w-xl overflow-hidden my-8"
                @click.outside="isEditModalOpen = false">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-sm">Edit Layanan & Durasi Paket</h3>
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
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Deskripsi Layanan
                        </label>
                        <textarea name="description" x-model="editingService.description" rows="3"
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
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
                                            class="w-full text-xs font-semibold rounded-lg border-slate-300 py-1.5 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] text-slate-500 font-medium mb-0.5">Tarif Harga (Rp)</label>
                                        <input type="number" :name="'packages['+idx+'][price]'" x-model="pkg.price" required min="0" step="5000"
                                            class="w-full text-xs font-semibold rounded-lg border-slate-300 py-1.5 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
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
                            class="px-4 py-2 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs transition cursor-pointer">
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