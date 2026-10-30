<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                {{ __('Penugasan Admin Wilayah') }}
            </h2>
            <span class="text-xs bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full font-semibold">
                {{ $admins->count() }} Akun Admin
            </span>
        </div>
    </x-slot>

    <div class="py-8 px-6 sm:px-8 lg:px-10 space-y-6"
         x-data="{
            manageModalOpen: false,
            activeAdmin: null,
            selectedRevokeIds: [],
            modalSearchQuery: '',
            openManageModal(admin) {
                this.activeAdmin = admin;
                this.selectedRevokeIds = [];
                this.modalSearchQuery = '';
                this.manageModalOpen = true;
            },
            closeManageModal() {
                this.manageModalOpen = false;
                this.activeAdmin = null;
                this.selectedRevokeIds = [];
            },
            get modalFilteredRegions() {
                if (!this.activeAdmin || !this.activeAdmin.regions) return [];
                if (!this.modalSearchQuery.trim()) return this.activeAdmin.regions;
                const q = this.modalSearchQuery.toLowerCase();
                return this.activeAdmin.regions.filter(r => r.name.toLowerCase().includes(q));
            },
            toggleRevokeId(id) {
                if (this.selectedRevokeIds.includes(id)) {
                    this.selectedRevokeIds = this.selectedRevokeIds.filter(x => x !== id);
                } else {
                    this.selectedRevokeIds.push(id);
                }
            },
            selectAllModalFiltered() {
                const ids = this.modalFilteredRegions.map(r => r.id);
                this.selectedRevokeIds = Array.from(new Set([...this.selectedRevokeIds, ...ids]));
            },
            clearAllModalRevoke() {
                this.selectedRevokeIds = [];
            },
            submitBatchRevoke() {
                if (this.selectedRevokeIds.length === 0) return;
                window.confirmAction({
                    title: 'Cabut Wilayah Tugas?',
                    message: 'Hak akses operasional admin pada wilayah ini akan dicabut.',
                    type: 'danger',
                    confirmText: 'Cabut Wilayah',
                    onConfirm: () => {
                        this.$refs.revokeForm.submit();
                    }
                });
            }
         }">

        @if (session('status'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-xl shadow-xs flex items-center text-sm">
                <svg class="w-5 h-5 mr-2.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-xl shadow-xs text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card Tambah Penugasan Admin (Searchable Combobox + Tags) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-5"
             x-data="{
                selectedAdmin: '{{ old('user_id', '') }}',
                searchQuery: '',
                selectedRegions: [],
                isDropdownOpen: false,
                allRegions: @js($regions->map->only(['id', 'name', 'status'])),
                adminAssignments: @js($admins->mapWithKeys(fn($a) => [$a->id => $a->assignedRegions->pluck('id')])),
                
                get filteredRegions() {
                    if (!this.searchQuery.trim()) return this.allRegions;
                    const q = this.searchQuery.toLowerCase();
                    return this.allRegions.filter(r => r.name.toLowerCase().includes(q));
                },
                get selectedRegionObjects() {
                    return this.allRegions.filter(r => this.selectedRegions.includes(r.id));
                },
                toggleRegion(id) {
                    if (this.selectedRegions.includes(id)) {
                        this.selectedRegions = this.selectedRegions.filter(x => x !== id);
                    } else {
                        this.selectedRegions.push(id);
                    }
                },
                removeRegion(id) {
                    this.selectedRegions = this.selectedRegions.filter(x => x !== id);
                },
                selectAllFiltered() {
                    const selectableIds = this.filteredRegions
                        .filter(r => !this.isAlreadyAssigned(r.id))
                        .map(r => r.id);
                    this.selectedRegions = Array.from(new Set([...this.selectedRegions, ...selectableIds]));
                },
                clearSelected() {
                    this.selectedRegions = [];
                },
                isAlreadyAssigned(regionId) {
                    if (!this.selectedAdmin || !this.adminAssignments[this.selectedAdmin]) return false;
                    return this.adminAssignments[this.selectedAdmin].includes(regionId);
                }
             }">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                    <h3 class="text-base font-bold text-slate-800">Penugasan Wilayah Admin</h3>
                </div>
                <span class="text-xs text-slate-500">Cari dan pilih banyak kota sekaligus tanpa membuat tampilan penuh</span>
            </div>

            <form method="POST" action="{{ route('superadmin.admins.assign') }}" class="space-y-4">
                @csrf

                <!-- Template Hidden Inputs untuk seluruh region_ids terpilih -->
                <template x-for="rId in selectedRegions" :key="rId">
                    <input type="hidden" name="region_ids[]" :value="rId">
                </template>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Dropdown Pilih Admin -->
                    <div class="md:col-span-5">
                        <label for="user_id" class="block text-xs font-semibold text-slate-700 mb-1">1. Pilih Akun Admin</label>
                        <select id="user_id" name="user_id" x-model="selectedAdmin" required
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50">
                            <option value="">-- Pilih Akun Admin --</option>
                            @foreach ($admins as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Combobox Cari Kota dengan Floating Dropdown -->
                    <div class="md:col-span-7 relative" @click.outside="isDropdownOpen = false">
                        <label for="search_regions" class="block text-xs font-semibold text-slate-700 mb-1 flex justify-between items-center">
                            <span>2. Cari & Pilih Kota Tugas</span>
                            <span x-show="selectedRegions.length > 0" class="text-emerald-700 font-semibold text-[11px]" x-text="selectedRegions.length + ' kota dipilih'"></span>
                        </label>
                        <div class="relative">
                            <input type="text" id="search_regions" x-model="searchQuery" 
                                @focus="isDropdownOpen = true"
                                @click="isDropdownOpen = true"
                                @keydown.escape="isDropdownOpen = false"
                                placeholder="Ketik nama kota atau klik untuk membuka daftar..."
                                class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 placeholder-slate-400 pl-8 pr-8">
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <button type="button" @click="isDropdownOpen = !isDropdownOpen" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="isDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Floating Popover Dropdown (Tidak Mendorong Konten ke Bawah) -->
                        <div x-show="isDropdownOpen" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute z-30 left-0 right-0 mt-1.5 bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden"
                             style="display: none;">
                            
                            <!-- Header Dropdown -->
                            <div class="p-2.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[11px]">
                                <span class="text-slate-500">
                                    Menampilkan <b class="text-slate-700" x-text="filteredRegions.length"></b> kota
                                </span>
                                <div class="space-x-2">
                                    <button type="button" @click="selectAllFiltered()" class="text-emerald-700 hover:underline font-semibold">
                                        + Pilih Semua
                                    </button>
                                    <button type="button" @click="isDropdownOpen = false" class="text-slate-400 hover:text-slate-600">
                                        Tutup ✕
                                    </button>
                                </div>
                            </div>

                            <!-- List Hasil Kota -->
                            <div class="max-h-56 overflow-y-auto divide-y divide-slate-100">
                                <template x-for="r in filteredRegions" :key="r.id">
                                    <div @click="!isAlreadyAssigned(r.id) && toggleRegion(r.id)"
                                         class="px-3.5 py-2 flex items-center justify-between text-xs transition cursor-pointer select-none"
                                         :class="{
                                             'bg-emerald-50/70 text-emerald-900 font-semibold': selectedRegions.includes(r.id),
                                             'hover:bg-slate-50 text-slate-700': !selectedRegions.includes(r.id) && !isAlreadyAssigned(r.id),
                                             'bg-slate-50 text-slate-400 cursor-not-allowed': isAlreadyAssigned(r.id)
                                         }">
                                        <div class="flex items-center space-x-2.5">
                                            <input type="checkbox" 
                                                   :checked="selectedRegions.includes(r.id)" 
                                                   :disabled="isAlreadyAssigned(r.id)"
                                                   class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 pointer-events-none w-3.5 h-3.5">
                                            <span x-text="r.name"></span>
                                        </div>
                                        <div class="flex items-center space-x-1.5">
                                            <span x-show="isAlreadyAssigned(r.id)" class="text-[10px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded font-normal">
                                                Sudah ditugaskan
                                            </span>
                                            <span x-show="selectedRegions.includes(r.id)" class="text-emerald-700 text-xs font-bold">
                                                ✓
                                            </span>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="filteredRegions.length === 0" class="py-6 text-center text-xs text-slate-400">
                                    Tidak ada kota yang sesuai dengan kata kunci.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tag / Chip Kota Terpilih -->
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-3 min-h-[52px]">
                    <div class="flex items-center justify-between mb-2" x-show="selectedRegions.length > 0">
                        <span class="text-xs font-semibold text-slate-700 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Kota yang akan ditugaskan (<span x-text="selectedRegions.length"></span>):</span>
                        </span>
                        <button type="button" @click="clearSelected()" class="text-[11px] text-rose-600 hover:underline font-semibold">
                            Hapus Semua
                        </button>
                    </div>

                    <div x-show="selectedRegions.length > 0" class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto">
                        <template x-for="r in selectedRegionObjects" :key="r.id">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-emerald-300 text-emerald-800 rounded-lg text-xs font-medium shadow-2xs">
                                <span x-text="r.name"></span>
                                <button type="button" @click="removeRegion(r.id)" class="text-emerald-400 hover:text-rose-600 font-bold leading-none">
                                    ×
                                </button>
                            </span>
                        </template>
                    </div>

                    <div x-show="selectedRegions.length === 0" class="py-1 text-center text-xs text-slate-400">
                        Belum ada kota yang dipilih. Cari dan centang kota pada kolom pencarian di atas.
                    </div>
                </div>

                <!-- Footer Summary & Submit Button -->
                <div class="pt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-100">
                    <div class="text-xs text-slate-500">
                        <span x-show="selectedRegions.length === 0">Pilih admin dan sedikitnya satu kota untuk mulai menugaskan.</span>
                        <span x-show="selectedRegions.length > 0" class="text-emerald-800 font-semibold">
                            Siap menugaskan <span x-text="selectedRegions.length"></span> kota sekaligus.
                        </span>
                    </div>

                    <button type="submit" 
                        :disabled="!selectedAdmin || selectedRegions.length === 0"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold rounded-xl shadow-xs transition text-xs flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span x-text="selectedRegions.length > 0 ? 'Tetapkan ' + selectedRegions.length + ' Kota ke Admin Ini' : 'Pilih Admin & Kota Terlebih Dahulu'"></span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Admin & Penugasan -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-base font-bold text-slate-800">Daftar Admin & Wilayah Binaan</h3>
                <span class="text-xs text-slate-500">Total: {{ $admins->count() }} Admin</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-slate-700 uppercase font-semibold text-[11px] border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5">Nama Admin</th>
                            <th class="px-5 py-3.5">Email</th>
                            <th class="px-5 py-3.5">Wilayah Tugas</th>
                            <th class="px-5 py-3.5 text-center">Total Kota</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($admins as $admin)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 font-semibold text-slate-800">
                                    {{ $admin->name }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-500 font-mono text-[11px]">
                                    {{ $admin->email }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-wrap items-center gap-1 text-xs">
                                        @forelse ($admin->assignedRegions->take(3) as $assigned)
                                            <span class="text-slate-700 font-medium">{{ $assigned->name }}@if(!$loop->last && ($loop->index < 2 || $admin->assignedRegions->count() <= 3)),@endif</span>
                                        @empty
                                            <span class="text-slate-400 italic">Belum ada wilayah binaan</span>
                                        @endforelse

                                        @if ($admin->assignedRegions->count() > 3)
                                            <span class="text-slate-400 text-[11px]">
                                                +{{ $admin->assignedRegions->count() - 3 }} lainnya
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="font-bold text-slate-800 text-xs">{{ $admin->assignedRegions->count() }}</span>
                                    <span class="text-slate-400 text-xs">Kota</span>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <button type="button" 
                                        id="btn-kelola-wilayah-{{ $admin->id }}"
                                        @click="openManageModal({
                                            id: {{ $admin->id }},
                                            name: '{{ addslashes($admin->name) }}',
                                            email: '{{ addslashes($admin->email) }}',
                                            regions: @js($admin->assignedRegions->map->only(['id', 'name', 'status']))
                                        })"
                                        class="text-xs font-semibold text-slate-700 hover:text-emerald-700 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                        <span>Kelola Wilayah</span>
                                        <span>&rarr;</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada akun admin terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Kelola Wilayah Admin (Batch Revoke Checklist) -->
        <div x-show="manageModalOpen" 
             id="modal-kelola-wilayah"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="closeManageModal()"
                 x-show="manageModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200"
                     x-show="manageModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @keydown.escape.window="closeManageModal()">
                    
                    <!-- Modal Header -->
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                                <span>Kelola Wilayah Tugas Admin</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                <span class="font-semibold text-slate-700" x-text="activeAdmin?.name"></span>
                                (<span class="font-mono text-[11px]" x-text="activeAdmin?.email"></span>)
                            </p>
                        </div>
                        <button type="button" @click="closeManageModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form x-ref="revokeForm" method="POST" action="{{ route('superadmin.admins.revoke') }}">
                        @csrf
                        <input type="hidden" name="user_id" :value="activeAdmin ? activeAdmin.id : ''">
                        
                        <template x-for="rId in selectedRevokeIds" :key="rId">
                            <input type="hidden" name="region_ids[]" :value="rId">
                        </template>

                        <!-- Modal Body -->
                        <div class="p-5 space-y-4">
                            <!-- Search in Assigned Cities if more than 3 -->
                            <div x-show="activeAdmin && activeAdmin.regions && activeAdmin.regions.length > 3" class="relative">
                                <input type="text" x-model="modalSearchQuery" placeholder="Cari di antara wilayah tugas..."
                                       class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 placeholder-slate-400 pl-8">
                                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-700">
                                    Pilih kota yang ingin dicabut (klik untuk menandai):
                                </span>
                                <div class="space-x-2 text-[11px]" x-show="modalFilteredRegions.length > 0">
                                    <button type="button" @click="selectAllModalFiltered()" class="text-rose-600 hover:underline font-semibold">
                                        Pilih Semua
                                    </button>
                                    <span class="text-slate-300">|</span>
                                    <button type="button" @click="clearAllModalRevoke()" class="text-slate-500 hover:underline">
                                        Batal Pilih
                                    </button>
                                </div>
                            </div>

                            <!-- Daftar Kota Binaan -->
                            <div class="max-h-64 overflow-y-auto space-y-2 p-1">
                                <template x-for="r in modalFilteredRegions" :key="r.id">
                                    <div @click="toggleRevokeId(r.id)"
                                         class="p-3 rounded-xl border cursor-pointer select-none transition flex items-center justify-between text-xs"
                                         :class="{
                                             'bg-rose-50 border-rose-300 text-rose-900 shadow-2xs': selectedRevokeIds.includes(r.id),
                                             'bg-slate-50/70 border-slate-200/80 text-slate-700 hover:border-slate-300 hover:bg-slate-50': !selectedRevokeIds.includes(r.id)
                                         }">
                                        <div class="flex items-center space-x-2.5">
                                            <input type="checkbox"
                                                   :checked="selectedRevokeIds.includes(r.id)"
                                                   class="rounded text-rose-600 focus:ring-rose-500 border-slate-300 pointer-events-none w-4 h-4">
                                            <div>
                                                <span class="font-medium block" x-text="r.name"></span>
                                                <span x-show="r.status === 'aktif'" class="text-[9px] text-emerald-700 font-semibold">● Wilayah Aktif</span>
                                            </div>
                                        </div>
                                        <span x-show="selectedRevokeIds.includes(r.id)" class="text-[11px] font-semibold text-rose-600">
                                            Akan dicabut
                                        </span>
                                    </div>
                                </template>

                                <div x-show="!activeAdmin || !activeAdmin.regions || activeAdmin.regions.length === 0" class="py-8 text-center text-xs text-slate-400">
                                    Admin ini belum memiliki wilayah tugas yang dibina.
                                </div>
                                <div x-show="activeAdmin && activeAdmin.regions && activeAdmin.regions.length > 0 && modalFilteredRegions.length === 0" class="py-6 text-center text-xs text-slate-400">
                                    Tidak ada kota yang sesuai dengan pencarian.
                                </div>
                            </div>

                            <div x-show="selectedRevokeIds.length > 0" class="p-3 bg-rose-50/70 rounded-xl border border-rose-200 text-xs text-rose-800 flex items-center space-x-2">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span><b x-text="selectedRevokeIds.length"></b> kota ditandai untuk dicabut dari admin ini.</span>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                            <button type="button" @click="closeManageModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition">
                                Tutup
                            </button>

                            <button type="button" 
                                    id="btn-confirm-revoke"
                                    @click="submitBatchRevoke()"
                                    :disabled="selectedRevokeIds.length === 0"
                                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold rounded-xl text-xs transition shadow-xs flex items-center space-x-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span x-text="selectedRevokeIds.length > 0 ? 'Cabut ' + selectedRevokeIds.length + ' Kota Terpilih' : 'Pilih Kota untuk Dicabut'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>
