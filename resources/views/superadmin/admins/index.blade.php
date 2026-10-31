<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Penugasan Admin Wilayah') }}
        </h2>
    </x-slot>

    <div class="py-8 px-6 sm:px-8 lg:px-10 space-y-6"
         x-data="{
            // State Modal Kelola / Cabut Wilayah
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
                setTimeout(() => {
                    if (!this.manageModalOpen) {
                        this.activeAdmin = null;
                        this.selectedRevokeIds = [];
                    }
                }, 200);
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
            },

            // State Modal Buat Akun Admin Baru
            createModalOpen: {{ $errors->any() && old('create_admin') ? 'true' : 'false' }},
            createSelectedRegions: [],
            createSearchQuery: '',
            createDropdownOpen: false,
            openCreateModal() {
                this.createSelectedRegions = [];
                this.createSearchQuery = '';
                this.createDropdownOpen = false;
                this.createModalOpen = true;
            },
            closeCreateModal() {
                this.createModalOpen = false;
            },
            get createFilteredRegions() {
                if (!this.createSearchQuery.trim()) return this.allRegions;
                const q = this.createSearchQuery.toLowerCase();
                return this.allRegions.filter(r => r.name.toLowerCase().includes(q));
            },
            get createSelectedRegionObjects() {
                return this.allRegions.filter(r => this.createSelectedRegions.includes(r.id));
            },
            toggleCreateRegion(id) {
                if (this.createSelectedRegions.includes(id)) {
                    this.createSelectedRegions = this.createSelectedRegions.filter(x => x !== id);
                } else {
                    this.createSelectedRegions.push(id);
                }
            },
            removeCreateRegion(id) {
                this.createSelectedRegions = this.createSelectedRegions.filter(x => x !== id);
            },
            selectAllCreateFiltered() {
                const ids = this.createFilteredRegions.map(r => r.id);
                this.createSelectedRegions = Array.from(new Set([...this.createSelectedRegions, ...ids]));
            },
            clearCreateSelected() {
                this.createSelectedRegions = [];
            },

            // State Modal Tambah Penugasan
            assignModalOpen: {{ $errors->any() && old('user_id') ? 'true' : 'false' }},
            selectedAdmin: '{{ old('user_id', '') }}',
            searchQuery: '',
            selectedRegions: [],
            isDropdownOpen: false,
            allRegions: @js($regions->map->only(['id', 'name', 'status'])),
            adminAssignments: @js($admins->mapWithKeys(fn($a) => [$a->id => $a->assignedRegions->pluck('id')])),
            
            openAssignModal(adminId = '') {
                this.selectedAdmin = adminId ? String(adminId) : '';
                this.searchQuery = '';
                this.selectedRegions = [];
                this.isDropdownOpen = false;
                this.assignModalOpen = true;
            },
            closeAssignModal() {
                this.assignModalOpen = false;
                this.searchQuery = '';
                this.selectedRegions = [];
                this.isDropdownOpen = false;
            },
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
            },

            // State Modal Detail Informasi Admin
            detailModalOpen: false,
            detailAdmin: null,
            openDetailModal(admin) {
                this.detailAdmin = admin;
                this.detailModalOpen = true;
            },
            closeDetailModal() {
                this.detailModalOpen = false;
                setTimeout(() => {
                    if (!this.detailModalOpen) {
                        this.detailAdmin = null;
                    }
                }, 200);
            },
            openManageFromDetail() {
                if (!this.detailAdmin) return;
                const adminData = {
                    id: this.detailAdmin.id,
                    name: this.detailAdmin.name,
                    email: this.detailAdmin.email,
                    regions: this.detailAdmin.regions
                };
                // Tutup modal detail terlebih dahulu agar transisi keluar selesai
                this.closeDetailModal();
                // Tunggu transisi keluar selesai sebelum membuka modal kelola wilayah
                setTimeout(() => {
                    this.openManageModal(adminData);
                }, 180);
            },
            confirmDeleteAdmin() {
                if (!this.detailAdmin) return;
                const adminName = this.detailAdmin.name;
                const form = this.$refs.deleteAdminForm;
                window.confirmAction({
                    title: 'Hapus Akun Admin',
                    targetName: adminName,
                    message: 'Akun admin ini akan dihapus secara permanen dari sistem beserta seluruh penugasan wilayahnya. Tindakan ini tidak dapat dibatalkan.',
                    type: 'danger',
                    confirmText: 'Ya, Hapus Akun',
                    cancelText: 'Batal',
                    onConfirm: () => {
                        form.submit();
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

        <!-- Tabel Daftar Admin & Penugasan -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Daftar Admin & Wilayah Binaan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Total: {{ $admins->count() }} Admin terdaftar</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <button type="button" 
                            @click="openAssignModal()"
                            class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-xl text-xs transition border border-slate-200 shadow-2xs inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span>Tambah Penugasan</span>
                    </button>
                    <button type="button" 
                            @click="openCreateModal()"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span>Buat Akun Admin</span>
                    </button>
                </div>
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
                            @php
                                $adminPayload = [
                                    'id' => $admin->id,
                                    'name' => $admin->name,
                                    'email' => $admin->email,
                                    'phone' => $admin->phone,
                                    'created_at' => $admin->created_at?->translatedFormat('d F Y, H:i') ?? '-',
                                    'has_catalog' => $admin->canAccessMenu('KATALOG'),
                                    'regions' => $admin->assignedRegions->map->only(['id', 'name', 'status'])->values(),
                                    'delete_url' => route('superadmin.admins.destroy', $admin),
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 font-semibold text-slate-800">
                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                                @click="openDetailModal(@js($adminPayload))"
                                                class="font-semibold text-slate-800 hover:text-emerald-700 hover:underline text-left cursor-pointer">
                                            {{ $admin->name }}
                                        </button>
                                        @if ($admin->canAccessMenu('KATALOG'))
                                            <span class="text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 px-1.5 py-0.5 rounded" title="Izin Akses Katalog Layanan">
                                                Katalog
                                            </span>
                                        @endif
                                    </div>
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
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button" 
                                            id="btn-detail-admin-{{ $admin->id }}"
                                            @click="openDetailModal(@js($adminPayload))"
                                            class="text-xs font-semibold text-slate-600 hover:text-emerald-700 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                            <span>Detail</span>
                                        </button>
                                        <span class="text-slate-200">|</span>
                                        <button type="button" 
                                            @click="openAssignModal({{ $admin->id }})"
                                            class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                            <span>+ Tugaskan</span>
                                        </button>
                                        <span class="text-slate-200">|</span>
                                        <button type="button" 
                                            id="btn-kelola-wilayah-{{ $admin->id }}"
                                            @click="openManageModal({
                                                id: {{ $admin->id }},
                                                name: '{{ addslashes($admin->name) }}',
                                                email: '{{ addslashes($admin->email) }}',
                                                regions: @js($admin->assignedRegions->map->only(['id', 'name', 'status']))
                                            })"
                                            class="text-xs font-semibold text-slate-600 hover:text-slate-800 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                            <span>Kelola Wilayah</span>
                                            <span>&rarr;</span>
                                        </button>
                                    </div>
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
                                       class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 placeholder:text-xs placeholder-slate-400 pl-8">
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

        <!-- Modal Tambah Penugasan Admin (Searchable Combobox + Tags) -->
        <div x-show="assignModalOpen" 
             id="modal-tambah-penugasan"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="closeAssignModal()"
                 x-show="assignModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-200"
                     x-show="assignModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @keydown.escape.window="closeAssignModal()">
                    
                    <!-- Modal Header -->
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                                <span>Tambah Penugasan Wilayah Admin</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Tetapkan satu atau banyak wilayah operasional sekaligus kepada admin.
                            </p>
                        </div>
                        <button type="button" @click="closeAssignModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('superadmin.admins.assign') }}" class="space-y-0">
                        @csrf

                        <!-- Template Hidden Inputs untuk region_ids -->
                        <template x-for="rId in selectedRegions" :key="rId">
                            <input type="hidden" name="region_ids[]" :value="rId">
                        </template>

                        <!-- Modal Body -->
                        <div class="p-5 space-y-4">
                            <!-- 1. Dropdown Pilih Admin -->
                            <div>
                                <label for="modal_user_id" class="block text-xs font-semibold text-slate-700 mb-1">1. Pilih Akun Admin *</label>
                                <select id="modal_user_id" name="user_id" x-model="selectedAdmin" required
                                    class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50/50">
                                    <option value="">-- Pilih Akun Admin --</option>
                                    @foreach ($admins as $admin)
                                        <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 2. Combobox Cari Kota dengan Floating Dropdown -->
                            <div class="relative" @click.outside="isDropdownOpen = false">
                                <label for="modal_search_regions" class="block text-xs font-semibold text-slate-700 mb-1 flex justify-between items-center">
                                    <span>2. Cari & Pilih Kota Tugas *</span>
                                    <span x-show="selectedRegions.length > 0" class="text-emerald-700 font-semibold text-[11px]" x-text="selectedRegions.length + ' kota dipilih'"></span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="modal_search_regions" x-model="searchQuery" 
                                        @focus="isDropdownOpen = true"
                                        @click="isDropdownOpen = true"
                                        @keydown.escape="isDropdownOpen = false"
                                        placeholder="Ketik nama kota atau klik untuk membuka daftar..."
                                        class="w-full text-xs placeholder:text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 pl-8 pr-8">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <button type="button" @click="isDropdownOpen = !isDropdownOpen" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 cursor-pointer">
                                        <svg class="w-4 h-4 transition-transform duration-200" :class="isDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Floating Popover Dropdown -->
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
                                            <button type="button" @click="selectAllFiltered()" class="text-emerald-700 hover:underline font-semibold cursor-pointer">
                                                + Pilih Semua
                                            </button>
                                            <button type="button" @click="isDropdownOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                                                Tutup ✕
                                            </button>
                                        </div>
                                    </div>

                                    <!-- List Hasil Kota -->
                                    <div class="max-h-48 overflow-y-auto divide-y divide-slate-100">
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

                            <!-- 3. Tag / Chip Kota Terpilih -->
                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-3 min-h-[52px]">
                                <div class="flex items-center justify-between mb-2" x-show="selectedRegions.length > 0">
                                    <span class="text-xs font-semibold text-slate-700 flex items-center space-x-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>Kota yang akan ditugaskan (<span x-text="selectedRegions.length"></span>):</span>
                                    </span>
                                    <button type="button" @click="clearSelected()" class="text-[11px] text-rose-600 hover:underline font-semibold cursor-pointer">
                                        Hapus Semua
                                    </button>
                                </div>

                                <div x-show="selectedRegions.length > 0" class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto">
                                    <template x-for="r in selectedRegionObjects" :key="r.id">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-emerald-300 text-emerald-800 rounded-lg text-xs font-medium shadow-2xs">
                                            <span x-text="r.name"></span>
                                            <button type="button" @click="removeRegion(r.id)" class="text-emerald-400 hover:text-rose-600 font-bold leading-none cursor-pointer">
                                                ×
                                            </button>
                                        </span>
                                    </template>
                                </div>

                                <div x-show="selectedRegions.length === 0" class="py-1 text-center text-xs text-slate-400">
                                    Belum ada kota yang dipilih. Cari dan centang kota pada kolom di atas.
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                            <button type="button" @click="closeAssignModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition cursor-pointer">
                                Batal
                            </button>

                            <button type="submit" 
                                    :disabled="!selectedAdmin || selectedRegions.length === 0"
                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold rounded-xl text-xs transition shadow-xs flex items-center space-x-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <span x-text="selectedRegions.length > 0 ? 'Tetapkan ' + selectedRegions.length + ' Kota' : 'Pilih Admin & Kota'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Modal Buat Akun Admin Baru -->
        <div x-show="createModalOpen" 
             id="modal-buat-admin"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="closeCreateModal()"
                 x-show="createModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-200"
                     x-show="createModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @keydown.escape.window="closeCreateModal()">
                    
                    <!-- Modal Header -->
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                                <span>Buat Akun Admin Wilayah Baru</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Daftarkan akun admin baru untuk mengelola operasional wilayah tugas.
                            </p>
                        </div>
                        <button type="button" @click="closeCreateModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('superadmin.admins.store') }}">
                        @csrf
                        <input type="hidden" name="create_admin" value="1">

                        <!-- Template Hidden Inputs untuk region_ids -->
                        <template x-for="rId in createSelectedRegions" :key="rId">
                            <input type="hidden" name="region_ids[]" :value="rId">
                        </template>

                        <!-- Modal Body -->
                        <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                            <!-- Nama Lengkap -->
                            <div>
                                <label for="create_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Admin *</label>
                                <input type="text" id="create_name" name="name" value="{{ old('name') }}" required
                                    placeholder="Contoh: Budi Santoso"
                                    class="w-full text-xs placeholder:text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Email -->
                                <div>
                                    <label for="create_email" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email *</label>
                                    <input type="email" id="create_email" name="email" value="{{ old('email') }}" required
                                        placeholder="admin@pijat.in"
                                        class="w-full text-xs placeholder:text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>

                                <!-- Nomor Telepon / WA -->
                                <div>
                                    <label for="create_phone" class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                                    <input type="text" id="create_phone" name="phone" value="{{ old('phone') }}"
                                        placeholder="08123456789"
                                        class="w-full text-xs placeholder:text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Password -->
                                <div>
                                    <label for="create_password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi (Min. 8 karakter) *</label>
                                    <input type="password" id="create_password" name="password" required
                                        placeholder="••••••••"
                                        class="w-full text-xs placeholder:text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>

                                <!-- Password Confirmation -->
                                <div>
                                    <label for="create_password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Kata Sandi *</label>
                                    <input type="password" id="create_password_confirmation" name="password_confirmation" required
                                        placeholder="••••••••"
                                        class="w-full text-xs placeholder:text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>

                            <!-- Penugasan Wilayah Pertama (Opsional) -->
                            <div class="pt-2 border-t border-slate-100 space-y-2 relative" @click.outside="createDropdownOpen = false">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-semibold text-slate-700">Tugaskan Wilayah Awal (Opsional)</label>
                                    <span x-show="createSelectedRegions.length > 0" class="text-emerald-700 font-semibold text-[11px]" x-text="createSelectedRegions.length + ' kota dipilih'"></span>
                                </div>

                                <div class="relative">
                                    <input type="text" x-model="createSearchQuery"
                                        @focus="createDropdownOpen = true"
                                        @click="createDropdownOpen = true"
                                        @keydown.escape="createDropdownOpen = false"
                                        placeholder="Pilih kota tugas awal admin ini..."
                                        class="w-full text-xs placeholder:text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 pl-8 pr-8">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <button type="button" @click="createDropdownOpen = !createDropdownOpen" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 cursor-pointer">
                                        <svg class="w-4 h-4 transition-transform duration-200" :class="createDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Floating Popover Dropdown -->
                                <div x-show="createDropdownOpen" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 translate-y-1"
                                     class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden"
                                     style="display: none;">
                                    <div class="p-2.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[11px]">
                                        <span class="text-slate-500">Menampilkan <b class="text-slate-700" x-text="createFilteredRegions.length"></b> kota</span>
                                        <button type="button" @click="selectAllCreateFiltered()" class="text-emerald-700 hover:underline font-semibold cursor-pointer">+ Pilih Semua</button>
                                    </div>
                                    <div class="max-h-40 overflow-y-auto divide-y divide-slate-100">
                                        <template x-for="r in createFilteredRegions" :key="r.id">
                                            <div @click="toggleCreateRegion(r.id)"
                                                 class="px-3.5 py-2 flex items-center justify-between text-xs transition cursor-pointer select-none"
                                                 :class="createSelectedRegions.includes(r.id) ? 'bg-emerald-50/70 text-emerald-900 font-semibold' : 'hover:bg-slate-50 text-slate-700'">
                                                <div class="flex items-center space-x-2.5">
                                                    <input type="checkbox" :checked="createSelectedRegions.includes(r.id)" class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 pointer-events-none w-3.5 h-3.5">
                                                    <span x-text="r.name"></span>
                                                </div>
                                                <span x-show="createSelectedRegions.includes(r.id)" class="text-emerald-700 text-xs font-bold">✓</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Chips Kota Terpilih -->
                                <div x-show="createSelectedRegions.length > 0" class="flex flex-wrap gap-1.5 pt-1">
                                    <template x-for="r in createSelectedRegionObjects" :key="r.id">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-emerald-300 text-emerald-800 rounded-lg text-xs font-medium shadow-2xs">
                                            <span x-text="r.name"></span>
                                            <button type="button" @click="removeCreateRegion(r.id)" class="text-emerald-400 hover:text-rose-600 font-bold leading-none cursor-pointer">×</button>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                            <button type="button" @click="closeCreateModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition cursor-pointer">
                                Batal
                            </button>

                            <button type="submit" 
                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <span>Buat Akun Admin Sekarang</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Modal Detail Informasi Admin -->
        <div x-show="detailModalOpen" 
             id="modal-detail-admin"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="closeDetailModal()"
                 x-show="detailModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200"
                     x-show="detailModalOpen"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @keydown.escape.window="closeDetailModal()">
                    
                    <!-- Hidden Delete Form -->
                    <form x-ref="deleteAdminForm" method="POST" :action="detailAdmin?.delete_url" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>

                    <!-- Modal Header -->
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Detail Informasi Admin</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Rincian profil, kontak, dan penugasan wilayah.</p>
                        </div>
                        <button type="button" @click="closeDetailModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div x-show="detailAdmin" class="p-6 space-y-5">
                        <!-- Top Summary Profile Card -->
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <div class="w-12 h-12 rounded-xl bg-emerald-700 text-white font-bold flex items-center justify-center text-lg shrink-0 shadow-xs"
                                 x-text="detailAdmin?.name ? detailAdmin.name.charAt(0).toUpperCase() : 'A'">
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-bold text-slate-800 truncate" x-text="detailAdmin?.name"></h4>
                                <p class="text-xs text-slate-500 font-mono truncate" x-text="detailAdmin?.email"></p>
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        Admin Wilayah
                                    </span>
                                    <span x-show="detailAdmin?.has_catalog" class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Akses Katalog
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Data Fields List -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-xl border border-slate-100 bg-white">
                                <span class="text-slate-400 text-[11px] block font-medium">Nomor Telepon / WA</span>
                                <span class="font-semibold text-slate-700 block mt-0.5" x-text="detailAdmin?.phone ? detailAdmin.phone : 'Belum ditambahkan'"></span>
                            </div>
                            <div class="p-3 rounded-xl border border-slate-100 bg-white">
                                <span class="text-slate-400 text-[11px] block font-medium">Terdaftar Sejak</span>
                                <span class="font-semibold text-slate-700 block mt-0.5" x-text="detailAdmin?.created_at"></span>
                            </div>
                            <div class="p-3 rounded-xl border border-slate-100 bg-white sm:col-span-2">
                                <span class="text-slate-400 text-[11px] block font-medium">Total Penugasan</span>
                                <span class="font-semibold text-slate-700 block mt-0.5">
                                    <span class="font-bold text-emerald-700" x-text="detailAdmin?.regions ? detailAdmin.regions.length : 0"></span> Kota / Kabupaten
                                </span>
                            </div>
                        </div>

                        <!-- Wilayah Tugas List -->
                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <h5 class="text-xs font-bold text-slate-700">Daftar Wilayah Binaan</h5>
                                <span class="text-[11px] text-slate-400" x-text="(detailAdmin?.regions ? detailAdmin.regions.length : 0) + ' wilayah'"></span>
                            </div>
                            
                            <div x-show="!detailAdmin?.regions || detailAdmin.regions.length === 0" class="p-3 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-xs text-slate-400">
                                Admin ini belum memiliki wilayah tugas binaan.
                            </div>

                            <div x-show="detailAdmin?.regions && detailAdmin.regions.length > 0" class="max-h-44 overflow-y-auto divide-y divide-slate-100 rounded-xl border border-slate-200">
                                <template x-for="r in (detailAdmin?.regions || [])" :key="r.id">
                                    <div class="px-3.5 py-2 flex items-center justify-between text-xs hover:bg-slate-50 transition">
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="r.status === 'aktif' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                            <span class="font-medium text-slate-700" x-text="r.name"></span>
                                        </div>
                                        <span class="text-[10px] uppercase font-semibold px-1.5 py-0.5 rounded"
                                              :class="r.status === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'"
                                              x-text="r.status">
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <!-- Tombol Hapus Akun Admin -->
                        <button type="button" 
                                id="btn-hapus-admin"
                                @click="confirmDeleteAdmin()"
                                class="px-3.5 py-2 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 border border-rose-200 rounded-xl transition inline-flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Hapus Akun</span>
                        </button>

                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="openManageFromDetail()"
                                    class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl transition cursor-pointer">
                                Kelola Wilayah
                            </button>
                            <button type="button" 
                                    @click="closeDetailModal()" 
                                    class="px-4 py-2 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-xl transition cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>
