<x-pwa-layout title="{{ $service->name }} - Pijat.In" headerTitle="Detail Layanan" :backUrl="route('pelanggan.catalog.index')">
    <div class="py-2 space-y-4"
         x-data="{
            packages: @js($service->activePackages),
            selectedPackageId: {{ $service->activePackages->first()?->id ?? 'null' }},
            getSelectedPackage() {
                return this.packages.find(p => p.id === this.selectedPackageId) || this.packages[0];
            }
         }">

        <!-- Service Main Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-4">
            @php
                $icons = [
                    'full-body-massage' => '💆',
                    'hot-stone-massage' => '🔥',
                    'thai-massage' => '🧘',
                    'deep-tissue-massage' => '💪',
                    'swedish-massage' => '🌿',
                    'traditional-massage' => '🍵',
                ];
                $icon = $icons[$service->slug] ?? '💆';
            @endphp

            <div class="flex items-center space-x-3.5">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-3xl shrink-0 shadow-inner">
                    {{ $icon }}
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 leading-tight">{{ $service->name }}</h2>
                    <span class="inline-block mt-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                        Layanan Resmi Pijat.In
                    </span>
                </div>
            </div>

            <div class="text-xs text-slate-600 leading-relaxed pt-2 border-t border-slate-100">
                <p>{{ $service->description }}</p>
            </div>
        </div>

        <!-- Duration & Package Selection Card (CUS-03) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3.5">
            <div>
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Pilih Durasi Paket</h3>
                <p class="text-[11px] text-slate-400">Pilih durasi pijat yang sesuai dengan kebutuhan Anda</p>
            </div>

            <!-- Packages Options Radio Chips -->
            <div class="grid grid-cols-1 gap-2.5">
                @foreach ($service->activePackages as $pkg)
                    <div @click="selectedPackageId = {{ $pkg->id }}"
                         class="p-3.5 rounded-2xl border cursor-pointer select-none transition flex items-center justify-between"
                         :class="selectedPackageId === {{ $pkg->id }} ? 'bg-emerald-50/70 border-emerald-500 ring-1 ring-emerald-500 shadow-xs' : 'bg-slate-50/70 border-slate-200 hover:border-slate-300'">
                        <div class="flex items-center space-x-3">
                            <div class="w-5 h-5 rounded-full border flex items-center justify-center transition"
                                 :class="selectedPackageId === {{ $pkg->id }} ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 bg-white'">
                                <svg x-show="selectedPackageId === {{ $pkg->id }}" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">{{ $pkg->duration_minutes }} Menit</span>
                                <span class="text-[10px] text-slate-500">Durasi sesi penuh</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-extrabold text-emerald-800 font-mono">{{ $pkg->formatted_price }}</span>
                            <span class="text-[10px] text-emerald-600 block">Tarif Tetap</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Summary Price Display -->
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between text-xs">
                <span class="text-slate-600 font-medium">Total Tarif Layanan:</span>
                <span class="text-sm font-black text-slate-900 font-mono" x-text="'Rp ' + (getSelectedPackage() ? Number(getSelectedPackage().price).toLocaleString('id-ID') : '0')"></span>
            </div>
        </div>

        <!-- Strict Standard Policy Badges -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-2.5 text-[11px] text-slate-600">
            <div class="flex items-center space-x-2 text-emerald-800">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Bebas Ongkir:</strong> Tidak ada biaya transportasi terapis tambahan.</span>
            </div>
            <div class="flex items-center space-x-2 text-emerald-800">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Protokol Gender:</strong> Terapis dijamin bergender sama dengan penerima layanan.</span>
            </div>
            <div class="flex items-center space-x-2 text-emerald-800">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><strong>Tanpa Add-on:</strong> Paket bersih tanpa biaya tambahan di luar durasi resmi.</span>
            </div>
        </div>

        <!-- Order Action Button (Prepares for Slice 4 Order Flow) -->
        <div class="pt-2">
            <button type="button" 
                class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center space-x-2">
                <span>Pesan Layanan Sekarang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </div>

    </div>
</x-pwa-layout>

