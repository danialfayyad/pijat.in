<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Master Rekening & Saluran Pembayaran') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{
             openCreateModal: false,
             openEditModal: false,
             editBank: { id: null, name: '', code: '', category: 'bank', account_number: '', account_holder_name: '', notes: '', sort_order: 0, is_active: true },
             editUrl: '',
             openEdit(bank) {
                 this.editBank = { 
                     ...bank,
                     category: bank.category || 'bank'
                 };
                 this.editUrl = '{{ url('/superadmin/banks') }}/' + bank.id;
                 this.openEditModal = true;
             }
         }">

        @if (session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-2xl flex items-center gap-2.5 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded-2xl shadow-2xs space-y-1">
                @foreach ($errors->all() as $error)
                    <p>&bull; {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- STAT METRICS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Total Saluran Terdaftar</span>
                    <span class="text-xl font-bold text-slate-900">{{ number_format($totalCount) }}</span>
                </div>
                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Saluran Aktif (Bisa Digunakan)</span>
                    <span class="text-xl font-bold text-emerald-700">{{ number_format($activeCount) }}</span>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Saluran Nonaktif</span>
                    <span class="text-xl font-bold text-slate-500">{{ number_format($inactiveCount) }}</span>
                </div>
                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- FILTER & ACTION BAR -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
            <form method="GET" action="{{ route('superadmin.banks.index') }}" class="flex flex-1 flex-wrap items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:max-w-xs min-w-[180px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama atau kode saluran..."
                           class="w-full text-xs rounded-xl border border-slate-200 pl-8 pr-3 py-2 focus:border-emerald-500 focus:ring-emerald-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <select name="category" onchange="this.form.submit()"
                        class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 text-slate-700 cursor-pointer">
                    <option value="">Semua Kategori</option>
                    <option value="bank" {{ request('category') === 'bank' ? 'selected' : '' }}>Bank Virtual Account</option>
                    <option value="qris" {{ request('category') === 'qris' ? 'selected' : '' }}>QRIS Nasional</option>
                    <option value="ewallet" {{ request('category') === 'ewallet' ? 'selected' : '' }}>E-Wallet (GoPay, DANA)</option>
                </select>

                <select name="status" onchange="this.form.submit()"
                        class="text-xs font-semibold border-slate-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-xs py-2 pl-3.5 pr-8 text-slate-700 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                @if (request()->hasAny(['search', 'status', 'category']))
                    <a href="{{ route('superadmin.banks.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline px-1">
                        Reset
                    </a>
                @endif
            </form>

            <button type="button" @click="openCreateModal = true"
                    class="w-full sm:w-auto py-2.5 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Saluran Baru</span>
            </button>
        </div>

        <!-- TABLE LIST -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                        <tr>
                            <th scope="col" class="py-3 px-4">Nama Saluran</th>
                            <th scope="col" class="py-3 px-4">Kode</th>
                            <th scope="col" class="py-3 px-4">Kategori</th>
                            <th scope="col" class="py-3 px-4">Rekening Platform (Opsional)</th>
                            <th scope="col" class="py-3 px-4">Keterangan</th>
                            <th scope="col" class="py-3 px-4 text-center">Status</th>
                            <th scope="col" class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($banks as $bank)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $bank->name }}
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-800">
                                    {{ $bank->code }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($bank->category === 'qris')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                            QRIS
                                        </span>
                                    @elseif ($bank->category === 'ewallet')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200/60">
                                            E-Wallet
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/60">
                                            Bank VA
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    @if ($bank->account_number)
                                        <div class="font-mono text-slate-900 font-semibold">{{ $bank->account_number }}</div>
                                        @if ($bank->account_holder_name)
                                            <div class="text-[11px] text-slate-400">a.n. {{ $bank->account_holder_name }}</div>
                                        @endif
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                    {{ $bank->notes ?: '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form action="{{ route('superadmin.banks.toggle', $bank) }}" method="POST" class="inline-flex items-center justify-center">
                                        @csrf
                                        <button type="submit" 
                                                class="group inline-flex items-center gap-2 cursor-pointer focus:outline-none"
                                                title="Klik untuk {{ $bank->is_active ? 'menonaktifkan' : 'mengaktifkan' }} saluran {{ $bank->name }}">
                                            <!-- Saklar On/Off (Toggle Switch) -->
                                            <div class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors duration-200 ease-in-out {{ $bank->is_active ? 'bg-emerald-600' : 'bg-slate-300' }}">
                                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out {{ $bank->is_active ? 'translate-x-4.5' : 'translate-x-0.5' }}"></span>
                                            </div>
                                            <!-- Status Text Label -->
                                            <span class="text-[11px] font-bold transition-colors {{ $bank->is_active ? 'text-emerald-700' : 'text-slate-400' }}">
                                                {{ $bank->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit Button -->
                                        <button type="button" @click="openEdit(@js($bank))"
                                                title="Edit Data Saluran"
                                                class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Delete Button -->
                                        <form action="{{ route('superadmin.banks.destroy', $bank) }}" method="POST"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus saluran {{ $bank->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Saluran"
                                                    class="p-1.5 rounded-lg border border-slate-200 hover:bg-rose-50 text-rose-600 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <svg class="w-8 h-8 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        <p class="font-semibold text-slate-600">Belum ada saluran pembayaran terdaftar</p>
                                        <p class="text-[11px] text-slate-400">Klik tombol di atas untuk menambahkan saluran pembayaran baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($banks->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $banks->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL TAMBAH BANK / SALURAN BARU -->
        <div x-show="openCreateModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             @click.self="openCreateModal = false">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Tambah Saluran Pembayaran Baru</h3>
                    <button type="button" @click="openCreateModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('superadmin.banks.store') }}" class="space-y-4 text-xs">
                    @csrf
                    <div class="space-y-1">
                        <label class="block font-semibold text-slate-700">Nama Saluran / Bank <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Bank Central Asia (BCA) atau QRIS"
                               class="w-full rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block font-semibold text-slate-700">Kode Unik <span class="text-rose-500">*</span></label>
                            <input type="text" name="code" required placeholder="Contoh: BCA, QRIS, GOPAY"
                                   class="w-full rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3 uppercase">
                        </div>

                        <div class="space-y-1">
                            <label class="block font-semibold text-slate-700">Kategori <span class="text-rose-500">*</span></label>
                            <select name="category" required class="w-full rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3 bg-white">
                                <option value="bank">Bank VA / Transfer</option>
                                <option value="qris">QRIS Nasional</option>
                                <option value="ewallet">E-Wallet (GoPay, DANA)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div class="space-y-1">
                            <label class="block font-semibold text-slate-700">No. Rekening Platform</label>
                            <input type="text" name="account_number" placeholder="Opsional"
                                   class="w-full rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3 font-mono">
                        </div>

                        <div class="space-y-1">
                            <label class="block font-semibold text-slate-700">Atas Nama Pemilik</label>
                            <input type="text" name="account_holder_name" placeholder="Opsional"
                                   class="w-full rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block font-semibold text-slate-700">Catatan / Petunjuk Pembayaran</label>
                        <textarea name="notes" rows="2" placeholder="Catatan operasional atau waktu verifikasi..."
                                  class="w-full rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></textarea>
                    </div>

                    <div class="pt-1">
                        <label class="inline-flex items-center gap-2 cursor-pointer font-medium text-slate-700">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span>Aktifkan saluran ini untuk pelanggan & mitra</span>
                        </label>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="openCreateModal = false"
                                class="py-2.5 px-4 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="py-2.5 px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold shadow-xs transition">
                            Simpan Saluran
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL EDIT SALURAN -->
        <div x-show="openEditModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             @click.self="openEditModal = false">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Edit Data Saluran</h3>
                    <button type="button" @click="openEditModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" :action="editUrl" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <div class="space-y-1">
                        <label class="block font-semibold text-slate-700">Nama Saluran / Bank <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="editBank.name" required
                               class="w-full rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block font-semibold text-slate-700">Kode Unik <span class="text-rose-500">*</span></label>
                            <input type="text" name="code" x-model="editBank.code" required
                                   class="w-full rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3 uppercase">
                        </div>

                        <div class="space-y-1">
                            <label class="block font-semibold text-slate-700">Kategori <span class="text-rose-500">*</span></label>
                            <select name="category" x-model="editBank.category" required class="w-full rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3 bg-white">
                                <option value="bank">Bank VA / Transfer</option>
                                <option value="qris">QRIS Nasional</option>
                                <option value="ewallet">E-Wallet (GoPay, DANA)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div class="space-y-1">
                            <label class="block font-semibold text-slate-700">No. Rekening Platform</label>
                            <input type="text" name="account_number" x-model="editBank.account_number"
                                   class="w-full rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3 font-mono">
                        </div>

                        <div class="space-y-1">
                            <label class="block font-semibold text-slate-700">Atas Nama Pemilik</label>
                            <input type="text" name="account_holder_name" x-model="editBank.account_holder_name"
                                   class="w-full rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block font-semibold text-slate-700">Catatan / Petunjuk Pembayaran</label>
                        <textarea name="notes" rows="2" x-model="editBank.notes"
                                  class="w-full rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3"></textarea>
                    </div>

                    <div class="pt-1">
                        <label class="inline-flex items-center gap-2 cursor-pointer font-medium text-slate-700">
                            <input type="checkbox" name="is_active" value="1" x-model="editBank.is_active"
                                   class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span>Aktifkan saluran ini untuk pelanggan & mitra</span>
                        </label>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="openEditModal = false"
                                class="py-2.5 px-4 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="py-2.5 px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold shadow-xs transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
