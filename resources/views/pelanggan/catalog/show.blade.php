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
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3.5">
            <div class="flex items-center space-x-3.5">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0">
                    @if (str_contains($service->slug, 'hot-stone'))
                        <svg class="w-7 h-7 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343a7.975 7.975 0 012.344 5.657 7.975 7.975 0 01-2.343 5.657z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                        </svg>
                    @elseif (str_contains($service->slug, 'thai') || str_contains($service->slug, 'refleksi'))
                        <svg class="w-7 h-7 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @elseif (str_contains($service->slug, 'swedish') || str_contains($service->slug, 'traditional') || str_contains($service->slug, 'herbal'))
                        <svg class="w-7 h-7 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    @else
                        <svg class="w-7 h-7 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/>
                        </svg>
                    @endif
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 leading-tight">{{ $service->name }}</h2>
                    <span class="inline-block mt-1 text-xs font-semibold text-emerald-700">
                        Standar Layanan Resmi Pijat.In
                    </span>
                </div>
            </div>

            <div class="text-xs text-slate-600 leading-relaxed pt-2 border-t border-slate-100">
                <p>{{ $service->description }}</p>
            </div>
        </div>

        <!-- Duration & Package Selection Card (CUS-03) -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-3.5">
            <div>
                <h3 class="text-xs font-bold text-slate-900">Pilih Durasi Paket</h3>
                <p class="text-xs text-slate-500">Pilih durasi pijat yang sesuai dengan kebutuhan Anda</p>
            </div>

            <!-- Packages Options Radio Chips -->
            <div class="grid grid-cols-1 gap-2.5">
                @foreach ($service->activePackages as $pkg)
                    <div @click="selectedPackageId = {{ $pkg->id }}"
                         class="p-4 rounded-xl border cursor-pointer select-none transition flex items-center justify-between min-h-[48px]"
                         :class="selectedPackageId === {{ $pkg->id }} ? 'bg-emerald-50/80 border-emerald-600 ring-1 ring-emerald-600 shadow-xs' : 'bg-slate-50/60 border-slate-200 hover:border-slate-300'">
                        <div class="flex items-center space-x-3">
                            <div class="w-5 h-5 rounded-full border flex items-center justify-center transition"
                                 :class="selectedPackageId === {{ $pkg->id }} ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 bg-white'">
                                <svg x-show="selectedPackageId === {{ $pkg->id }}" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-900 block">{{ $pkg->duration_minutes }} Menit</span>
                                <span class="text-[11px] text-slate-500">Sesi layanan penuh</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-black text-emerald-800 font-mono">{{ $pkg->formatted_price }}</span>
                            <span class="text-[11px] text-slate-500 block">Tarif Tetap</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Summary Price Display -->
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/90 flex items-center justify-between text-xs">
                <span class="text-slate-700 font-medium">Total Tarif Layanan:</span>
                <span class="text-sm font-black text-slate-900 font-mono" x-text="'Rp ' + (getSelectedPackage() ? Number(getSelectedPackage().price).toLocaleString('id-ID') : '0')"></span>
            </div>
        </div>

        <!-- Strict Standard Policy Badges -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3 text-xs text-slate-700">
            <div class="flex items-center space-x-3 text-emerald-900">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span><strong>Bebas Ongkir:</strong> Tidak ada biaya transportasi terapis tambahan.</span>
            </div>
            <div class="flex items-center space-x-3 text-emerald-900">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <span><strong>Protokol Gender:</strong> Terapis dipastikan bergender sama dengan Anda.</span>
            </div>
            <div class="flex items-center space-x-3 text-emerald-900">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span><strong>Tanpa Add-on:</strong> Paket bersih tanpa biaya tambahan tersembunyi.</span>
            </div>
        </div>

        <!-- Order Action Button (Slice 4 Order Flow) -->
        <div class="pt-2">
            <button type="button" 
                @click="window.location.href = '{{ route('pelanggan.orders.create') }}?package_id=' + selectedPackageId"
                class="w-full min-h-[48px] py-3.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center space-x-2 cursor-pointer">
                <span>Pesan Layanan Sekarang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </div>

    </div>
</x-pwa-layout>

