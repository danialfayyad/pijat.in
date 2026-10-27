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

    <div class="py-8 px-6 sm:px-8 lg:px-10 space-y-6">

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

        <!-- Card Tambah Penugasan Admin (Multi-City Batch Assign) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-5"
             x-data="{
                selectedAdmin: '{{ old('user_id', '') }}',
                searchQuery: '',
                selectedRegions: [],
                allRegions: @js($regions->map->only(['id', 'name', 'status'])),
                adminAssignments: @js($admins->mapWithKeys(fn($a) => [$a->id => $a->assignedRegions->pluck('id')])),
                
                get filteredRegions() {
                    if (!this.searchQuery.trim()) return this.allRegions;
                    const q = this.searchQuery.toLowerCase();
                    return this.allRegions.filter(r => r.name.toLowerCase().includes(q));
                },
                toggleRegion(id) {
                    if (this.selectedRegions.includes(id)) {
                        this.selectedRegions = this.selectedRegions.filter(x => x !== id);
                    } else {
                        this.selectedRegions.push(id);
                    }
                },
                selectAllFiltered() {
                    const ids = this.filteredRegions.map(r => r.id);
                    this.selectedRegions = Array.from(new Set([...this.selectedRegions, ...ids]));
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
                    <h3 class="text-base font-bold text-slate-800">Penugasan Wilayah Admin (Bisa Pilih Banyak Kota)</h3>
                </div>
                <span class="text-xs text-slate-500">Pilih admin dan centang kota-kota yang ingin ditugaskan</span>
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
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih Akun Admin --</option>
                            @foreach ($admins as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Input Filter Cari Kota -->
                    <div class="md:col-span-7">
                        <label for="search_regions" class="block text-xs font-semibold text-slate-700 mb-1">2. Cari Kota / Wilayah Layanan</label>
                        <div class="relative">
                            <input type="text" id="search_regions" x-model="searchQuery" placeholder="Ketik nama kota (cth: Surabaya, Malang, Bandung)..."
                                class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 placeholder-slate-400 pl-8">
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Pilihan Kota Bertingkat / Multi-Chip Selection -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-700">3. Klik Kota untuk Memilih Sekaligus:</span>
                        <div class="space-x-3">
                            <button type="button" @click="selectAllFiltered()" class="text-emerald-700 hover:underline font-semibold">
                                + Pilih Semua yang Tampil
                            </button>
                            <button type="button" @click="clearSelected()" class="text-slate-400 hover:text-rose-600">
                                Batal Pilih
                            </button>
                        </div>
                    </div>

                    <!-- Grid Kota -->
                    <div class="max-h-56 overflow-y-auto p-3 bg-slate-50/80 rounded-xl border border-slate-200/80 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                        <template x-for="r in filteredRegions" :key="r.id">
                            <div @click="toggleRegion(r.id)"
                                 class="p-2.5 rounded-xl border cursor-pointer select-none transition flex items-center justify-between text-xs"
                                 :class="{
                                     'bg-emerald-600 text-white font-bold border-emerald-600 shadow-xs': selectedRegions.includes(r.id),
                                     'bg-white text-slate-700 border-slate-200 hover:border-emerald-300': !selectedRegions.includes(r.id) && !isAlreadyAssigned(r.id),
                                     'bg-slate-100 text-slate-400 border-slate-200/60 cursor-default': isAlreadyAssigned(r.id) && !selectedRegions.includes(r.id)
                                 }">
                                <div class="truncate mr-1">
                                    <span x-text="r.name" class="block truncate"></span>
                                    <span x-show="isAlreadyAssigned(r.id)" class="text-[9px] block text-emerald-600 font-normal">Sudah ditugaskan</span>
                                </div>
                                <span class="text-sm font-bold shrink-0" 
                                      :class="selectedRegions.includes(r.id) ? 'text-white' : 'text-slate-300'" 
                                      x-text="selectedRegions.includes(r.id) ? '✓' : '+'"></span>
                            </div>
                        </template>

                        <div x-show="filteredRegions.length === 0" class="col-span-full py-6 text-center text-xs text-slate-400">
                            Tidak ada kota yang sesuai pencarian.
                        </div>
                    </div>
                </div>

                <!-- Footer Summary & Submit Button -->
                <div class="pt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-100">
                    <div class="text-xs text-slate-600">
                        <span x-show="selectedRegions.length === 0" class="text-slate-400 italic">Belum ada kota yang dipilih.</span>
                        <span x-show="selectedRegions.length > 0" class="text-emerald-800 font-semibold">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
                            <span x-text="selectedRegions.length"></span> kota siap ditugaskan
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
                            <th class="px-5 py-3.5 text-right">Total Kota</th>
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
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($admin->assignedRegions as $assigned)
                                            <span class="inline-flex items-center space-x-1 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 text-slate-700 hover:border-slate-300 transition">
                                                <span>{{ $assigned->name }}</span>
                                                <form method="POST" action="{{ route('superadmin.admins.revoke') }}" class="inline-block ml-1">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $admin->id }}">
                                                    <input type="hidden" name="region_id" value="{{ $assigned->id }}">
                                                    <button type="submit" onclick="return confirm('Cabut penugasan wilayah ini?')" title="Cabut kota ini" class="text-slate-400 hover:text-rose-600 font-bold leading-none ml-1">×</button>
                                                </form>
                                            </span>
                                        @empty
                                            <span class="text-rose-500 italic">Belum ada wilayah binaan</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-right font-medium text-slate-700">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $admin->assignedRegions->count() > 0 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $admin->assignedRegions->count() }} Kota
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada akun admin terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
