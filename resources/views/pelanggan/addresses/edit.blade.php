<x-pwa-layout title="Ubah Alamat - Pijat.In" headerTitle="Ubah Alamat" :backUrl="route('pelanggan.addresses.index')">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="py-2 space-y-4">
        <div>
            <h2 class="text-base font-extrabold text-slate-900 leading-tight">Ubah Alamat</h2>
            <p class="text-xs text-slate-500">Sesuaikan rincian atau pindahkan titik pin alamat Anda.</p>
        </div>

        <form action="{{ route('pelanggan.addresses.update', $address) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Card: Pilihan Wilayah & Peta Spasial -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 space-y-4"
                 x-data="{
                    searchQuery: '',
                    selectedRegionId: '{{ $address->region_id }}',
                    regions: @js($activeRegions),
                    label: '{{ old('label', $address->label) }}',
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
                                    class="px-3 py-1 rounded-lg text-xs font-bold border transition"
                                    :class="label === item ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-emerald-300'">
                                <span x-text="item"></span>
                            </button>
                        </template>
                    </div>
                    <input type="text" name="label" x-model="label" required placeholder="Contoh: Rumah, Kantor..."
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5">
                    @error('label') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- 1. Cari / Pilih Kota Layanan Aktif -->
                <div>
                    <label for="search_city" class="block text-xs font-semibold text-slate-700 mb-1">
                        Pilih Wilayah Operasional Kota
                    </label>
                    <div class="relative">
                        <input type="text" id="search_city" x-model="searchQuery" 
                            placeholder="Cari kota layanan..."
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 placeholder-slate-400 pl-8 py-2">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- City Chips -->
                    <div class="mt-2 flex flex-wrap gap-1.5 max-h-32 overflow-y-auto p-1">
                        <template x-for="r in filteredRegions" :key="r.id">
                            <button type="button" @click="selectRegion(r)"
                                    class="px-2.5 py-1 rounded-lg border text-xs font-medium transition flex items-center space-x-1"
                                    :class="selectedRegionId == r.id ? 'bg-emerald-600 text-white border-emerald-600 font-bold' : 'bg-white text-slate-700 border-slate-200 hover:border-emerald-300'">
                                <span x-text="r.name"></span>
                                <span x-show="selectedRegionId == r.id" class="text-[10px]">✓</span>
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
                        <span class="text-[11px] text-emerald-600 font-medium">
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
                        <label class="block text-[11px] font-medium text-slate-500 mb-0.5">Latitude</label>
                        <input type="text" id="lat" name="latitude" value="{{ old('latitude', $address->latitude) }}" readonly
                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 font-mono text-[11px] text-slate-600">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-0.5">Longitude</label>
                        <input type="text" id="lng" name="longitude" value="{{ old('longitude', $address->longitude) }}" readonly
                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 font-mono text-[11px] text-slate-600">
                    </div>
                </div>

                <!-- Alamat Lengkap & Patokan -->
                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Alamat Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="address_text" rows="2" required placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">{{ old('address_text', $address->address_text) }}</textarea>
                        @error('address_text') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Patokan Lokasi (Opsional)
                        </label>
                        <input type="text" name="benchmark" value="{{ old('benchmark', $address->benchmark) }}" placeholder="Contoh: Sebelah minimarket, pagar hitam..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                        @error('benchmark') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if ($address->is_primary)
                        <div class="p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center space-x-1.5">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Ini adalah Alamat Utama akun Anda saat ini.</span>
                        </div>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                        class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Perbarui Alamat</span>
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
            const initialLat = parseFloat(document.getElementById('lat').value) || {{ $address->latitude }};
            const initialLng = parseFloat(document.getElementById('lng').value) || {{ $address->longitude }};

            map = L.map('map').setView([initialLat, initialLng], 13);

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
            const currentRegionId = {{ $address->region_id }};
            const currentRegion = activeRegions.find(r => r.id === currentRegionId);
            if (currentRegion && currentRegion.geojson) {
                regionPolygonLayer = L.geoJSON(currentRegion.geojson, {
                    style: {
                        color: '#059669',
                        fillColor: '#10b981',
                        fillOpacity: 0.2,
                        weight: 2
                    }
                }).addTo(map);
            }
        });
    </script>
</x-pwa-layout>

