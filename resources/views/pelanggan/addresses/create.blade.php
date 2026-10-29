<x-pwa-layout title="Tambah Alamat - Pijat.In" headerTitle="Tambah Alamat" :backUrl="route('pelanggan.addresses.index')">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="py-2 space-y-4">
        <div>
            <h2 class="text-base font-extrabold text-slate-900 leading-tight">Tambah Alamat Baru</h2>
            <p class="text-xs text-slate-500">Pilih kota wilayah layanan, lalu tempatkan pin lokasi Anda.</p>
        </div>

        <form action="{{ route('pelanggan.addresses.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Card: Pilihan Wilayah & Peta Spasial -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 space-y-4"
                 x-data="{
                    searchQuery: '',
                    selectedRegionId: '',
                    regions: @js($activeRegions),
                    label: '{{ old('label', 'Rumah') }}',
                    get filteredRegions() {
                        if (!this.searchQuery.trim()) return this.regions;
                        const q = this.searchQuery.toLowerCase();
                        return this.regions.filter(r => r.name.toLowerCase().includes(q));
                    },
                    selectRegion(region) {
                        this.selectedRegionId = region.id;
                        if (window.onCustomerRegionSelected) {
                            window.onCustomerRegionSelected(region);
                        }
                    }
                 }">

                <!-- Label Alamat -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Label Alamat <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center space-x-2 mb-2">
                        <template x-for="item in ['Rumah', 'Kantor', 'Apartemen', 'Kos']" :key="item">
                            <button type="button" @click="label = item"
                                    class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs font-bold border transition cursor-pointer flex items-center justify-center"
                                    :class="label === item ? 'bg-emerald-700 text-white border-emerald-700 shadow-xs' : 'bg-slate-50 text-slate-800 border-slate-200 hover:border-emerald-300'">
                                <span x-text="item"></span>
                            </button>
                        </template>
                    </div>
                    <input type="text" name="label" x-model="label" required placeholder="Contoh: Rumah, Kantor..."
                        class="w-full min-h-[44px] text-xs rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                    @error('label') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- 1. Cari / Pilih Kota Layanan Aktif -->
                <div>
                    <label for="search_city" class="block text-xs font-semibold text-slate-700 mb-1">
                        Pilih Wilayah Operasional Kota
                    </label>
                    <div class="relative">
                        <input type="text" id="search_city" x-model="searchQuery" 
                            placeholder="Cari kota layanan..."
                            class="w-full min-h-[44px] text-xs rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 placeholder-slate-400 pl-8 py-2">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- City Chips -->
                    <div class="mt-2 flex flex-wrap gap-2 max-h-36 overflow-y-auto p-1">
                        <template x-for="r in filteredRegions" :key="r.id">
                            <button type="button" @click="selectRegion(r)"
                                    class="min-h-[44px] px-3 py-2 rounded-xl border text-xs font-medium transition flex items-center space-x-1.5 cursor-pointer"
                                    :class="selectedRegionId == r.id ? 'bg-emerald-700 text-white border-emerald-700 font-bold shadow-xs' : 'bg-white text-slate-800 border-slate-200 hover:border-emerald-300'">
                                <span x-text="r.name"></span>
                                <span x-show="selectedRegionId == r.id" class="text-xs">✓</span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- 2. Interactive Map -->
                <div class="space-y-1.5 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-slate-700">
                            Titik Lokasi Pin Peta <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-xs text-emerald-700 font-medium">
                            Ketuk peta atau seret pin
                        </span>
                    </div>

                    <div id="map" class="w-full h-56 rounded-xl border border-slate-200 overflow-hidden shadow-inner z-0"></div>

                    @error('latitude')
                        <p class="text-xs text-rose-600 font-bold bg-rose-50 p-2 rounded-lg border border-rose-200 mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Coordinates Hidden/Readonly -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-0.5">Latitude</label>
                        <input type="text" id="lat" name="latitude" value="{{ old('latitude', '-6.9175') }}" readonly
                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 font-mono text-xs text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-0.5">Longitude</label>
                        <input type="text" id="lng" name="longitude" value="{{ old('longitude', '107.6191') }}" readonly
                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 font-mono text-xs text-slate-700">
                    </div>
                </div>

                <!-- Alamat Lengkap & Patokan -->
                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Alamat Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="address_text" rows="2" required placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600">{{ old('address_text') }}</textarea>
                        @error('address_text') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Patokan Lokasi (Opsional)
                        </label>
                        <input type="text" name="benchmark" value="{{ old('benchmark') }}" placeholder="Contoh: Sebelah minimarket, pagar hitam..."
                            class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600">
                        @error('benchmark') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    @if ($user->customerAddresses()->exists())
                        <div class="flex items-center space-x-2 pt-1 min-h-[44px]">
                            <input type="checkbox" id="is_primary" name="is_primary" value="1" {{ old('is_primary') ? 'checked' : '' }}
                                class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600 cursor-pointer">
                            <label for="is_primary" class="text-xs text-slate-800 select-none cursor-pointer">
                                Jadikan sebagai Alamat Utama
                            </label>
                        </div>
                    @else
                        <p class="text-xs text-emerald-900 bg-emerald-50 p-2.5 rounded-xl border border-emerald-200">
                            Alamat pertama Anda akan otomatis dijadikan sebagai <strong>Alamat Utama</strong>.
                        </p>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                        class="w-full min-h-[48px] py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-sm transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Simpan Alamat</span>
                    </button>
                </div>

            </div>
        </form>
    </div>

    <!-- Leaflet Script -->
    <script>
        let map = null;
        let marker = null;
        let regionPolygonLayer = null;

        document.addEventListener('DOMContentLoaded', function () {
            const initialLat = parseFloat(document.getElementById('lat').value) || -6.9175;
            const initialLng = parseFloat(document.getElementById('lng').value) || 107.6191;

            map = L.map('map').setView([initialLat, initialLng], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            marker = L.marker([initialLat, initialLng], {
                draggable: true
            }).addTo(map);

            function updateInputs(lat, lng) {
                document.getElementById('lat').value = lat.toFixed(6);
                document.getElementById('lng').value = lng.toFixed(6);
            }

            marker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateInputs(pos.lat, pos.lng);
            });

            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                updateInputs(e.latlng.lat, e.latlng.lng);
            });

            window.onCustomerRegionSelected = function(region) {
                if (!region || !region.geojson) return;

                if (regionPolygonLayer) {
                    map.removeLayer(regionPolygonLayer);
                }

                regionPolygonLayer = L.geoJSON(region.geojson, {
                    style: {
                        color: '#059669',
                        fillColor: '#10b981',
                        fillOpacity: 0.25,
                        weight: 2
                    }
                }).addTo(map);

                const bounds = regionPolygonLayer.getBounds();
                map.fitBounds(bounds, { padding: [15, 15] });

                const center = bounds.getCenter();
                marker.setLatLng(center);
                updateInputs(center.lat, center.lng);
            };

            const activeRegions = @js($activeRegions);
            if (activeRegions && activeRegions.length === 1) {
                window.onCustomerRegionSelected(activeRegions[0]);
            }
        });
    </script>
</x-pwa-layout>

