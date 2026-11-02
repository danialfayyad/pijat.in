<x-app-layout>
    <x-slot name="header">
        <h1 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Manajemen Parameter Operasional') }}
        </h1>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{ 
            showReasonModal: false, 
            reason: '',
            reasonError: '',
            activeGroup: 'financial',
            openModal() {
                this.reasonError = '';
                this.showReasonModal = true;
            },
            submitForm() {
                if (this.reason.trim().length < 5) {
                    this.reasonError = 'Harap isi alasan perubahan minimal 5 karakter demi kepatuhan audit log.';
                    return;
                }
                document.getElementById('parameter-reason-input').value = this.reason.trim();
                document.getElementById('parameters-form').submit();
            }
         }">

        @if (session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold rounded-2xl flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('info'))
            <div class="p-4 bg-slate-100 border border-slate-200 text-slate-700 text-sm font-semibold rounded-2xl flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold rounded-2xl shadow-xs">
                <div class="font-bold mb-1">Terjadi kesalahan validasi:</div>
                <ul class="list-disc pl-5 space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Banner Info Non-Retroactive -->
        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 text-amber-900 text-xs flex items-start gap-3 shadow-2xs">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="space-y-0.5">
                <span class="font-bold block">Prinsip Parameter Non-Retroaktif</span>
                <p class="text-amber-800/90 leading-relaxed">
                    Setiap perubahan nilai parameter hanya berlaku untuk transaksi dan pesanan baru yang dibuat setelah waktu perubahan disimpan. Seluruh riwayat perubahan wajib mencantumkan alasan dan otomatis terekam pada Log Audit Sistem.
                </p>
            </div>
        </div>

        <form id="parameters-form" method="POST" action="{{ route('superadmin.parameters.update') }}">
            @csrf
            <input type="hidden" name="reason" id="parameter-reason-input">

            <!-- Category Navigation Pill Tabs (Layout Sebelumnya) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                @foreach ($groups as $groupKey => $groupInfo)
                    <button type="button" 
                            @click="activeGroup = '{{ $groupKey }}'"
                            :class="activeGroup === '{{ $groupKey }}' 
                                ? 'bg-emerald-700 text-white shadow-xs font-bold' 
                                : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-800 border border-slate-200/80 font-medium'"
                            class="px-4 py-2 rounded-xl text-xs whitespace-nowrap transition cursor-pointer">
                        {{ $groupInfo['label'] }}
                    </button>
                @endforeach
            </div>

            <!-- Parameters Grid for Each Group (Layout Sebelumnya yang Rapi) -->
            <div class="mt-4 space-y-6">
                @foreach ($groups as $groupKey => $groupInfo)
                    <div x-show="activeGroup === '{{ $groupKey }}'" class="space-y-4">
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                                <div>
                                    <h2 class="font-bold text-slate-800 text-sm">{{ $groupInfo['label'] }}</h2>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $groupInfo['desc'] }}</p>
                                </div>
                                <span class="text-xs font-semibold text-slate-400 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                                    {{ count($parameters[$groupKey] ?? []) }} Parameter
                                </span>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @forelse ($parameters[$groupKey] ?? [] as $param)
                                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/40 transition">
                                        <div class="space-y-1 max-w-xl">
                                            <h3 class="font-bold text-slate-800 text-xs">{{ $param->label }}</h3>
                                            @if ($param->description)
                                                <p class="text-xs text-slate-500 leading-relaxed">{{ $param->description }}</p>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0 sm:w-64">
                                            <div class="relative w-full">
                                                <input type="{{ $param->type === 'integer' || $param->type === 'decimal' ? 'number' : 'text' }}"
                                                       @if ($param->type === 'decimal') step="0.01" @endif
                                                       name="parameters[{{ $param->key }}]"
                                                       value="{{ old('parameters.'.$param->key, $param->value) }}"
                                                       required
                                                       class="w-full text-xs font-semibold rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3 {{ $param->unit ? 'pr-20' : 'pr-3' }} text-slate-800 shadow-2xs">
                                                @if ($param->unit)
                                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-semibold text-slate-400 pointer-events-none">
                                                        {{ $param->unit }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-xs text-slate-400">
                                        Tidak ada parameter pada kategori ini.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Sticky Save Bar -->
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs text-slate-500">
                    Pastikan seluruh nilai telah sesuai sebelum menyimpan perubahan ke database.
                </div>
                <button type="button" 
                        @click="openModal()"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Perubahan Parameter</span>
                </button>
            </div>
        </form>

        <!-- Reason Modal Dialog (Audit Trail Note - Bersih Tanpa Kode Internal SA-01 & Tanpa alert()) -->
        <div x-show="showReasonModal" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak
             style="display: none;"
             @keydown.escape.window="showReasonModal = false">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4" @click.stop>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-base text-slate-800">
                        Catatan Alasan Perubahan
                    </h3>
                    <button type="button" @click="showReasonModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Kepatuhan audit mewajibkan setiap perubahan parameter operasional disertai keterangan resmi tertulis:
                    </p>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Alasan Perubahan <span class="text-rose-500">*</span>
                        </label>
                        <textarea x-model="reason" 
                                  rows="3" 
                                  maxlength="500"
                                  placeholder="Contoh: Penyesuaian persentase denda pembatalan sesuai hasil evaluasi operasional Q3..."
                                  class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 p-2.5 text-slate-800"></textarea>
                        
                        <div class="flex items-center justify-between text-[11px] mt-1">
                            <span x-show="reasonError" x-text="reasonError" class="text-rose-600 font-semibold"></span>
                            <span x-show="!reasonError" class="text-slate-400">Minimal 5 karakter</span>
                            <span :class="reason.trim().length >= 5 ? 'text-emerald-600 font-semibold' : 'text-slate-400'"
                                  x-text="reason.trim().length + '/500 karakter'"></span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" 
                            @click="showReasonModal = false" 
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" 
                            @click="submitForm()" 
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
                        Konfirmasi & Simpan
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
