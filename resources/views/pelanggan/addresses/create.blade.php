<x-pwa-layout title="Tambah Alamat - Pijat.In" headerTitle="Tambah Alamat" :backUrl="request('from') === 'checkout' && request()->filled('package_id') ? route('pelanggan.addresses.index', ['from' => 'checkout', 'package_id' => request('package_id')]) : route('pelanggan.addresses.index')">
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
            @if (request('from') === 'checkout')
                <input type="hidden" name="from" value="checkout">
                <input type="hidden" name="package_id" value="{{ request('package_id') }}">
            @endif

            <!-- Card: Pilihan Wilayah & Peta Spasial -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 space-y-4"
                 x-data="{
                    selectedRegionId: '{{ count($activeRegions) === 1 ? $activeRegions[0]['id'] : '' }}',
                    regions: @js($activeRegions),
                    label: '{{ old('label', 'Rumah') }}',
                    onSelectDropdown(id) {
                        this.selectedRegionId = id;
                        const region = this.regions.find(r => r.id == id);
                        if (region && window.onCustomerRegionSelected) {
                            window.onCustomerRegionSelected(region);
                        }
                    }
                 }">

                <!-- Label Alamat (Pilihan Rapi Tanpa Kolom Input) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-2">
                        Label Alamat <span class="text-rose-500">*</span>
                    </label>
                    <input type="hidden" name="label" :value="label">
                    <div class="grid grid-cols-4 gap-2">
                        <template x-for="item in ['Rumah', 'Kantor', 'Kos', 'Apartemen']" :key="item">
                            <button type="button" 
                                    @click="label = item"
                                    class="py-2.5 px-1.5 rounded-xl text-xs text-center border transition-all cursor-pointer flex items-center justify-center select-none active:scale-95"
                                    :class="label === item 
                                        ? 'border-emerald-600 bg-emerald-50/70 text-emerald-800 font-bold ring-1 ring-emerald-600/30 shadow-2xs' 
                                        : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 font-medium'">
                                <span x-text="item"></span>
                            </button>
                        </template>
                    </div>
                    @error('label') <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- 1. Pilih Wilayah Operasional Kota (Dropdown Rapi & Tidak Overwhelming) -->
                <div>
                    <label for="region_select" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Pilih Wilayah Operasional Kota <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="region_select" 
                                x-model="selectedRegionId" 
                                @change="onSelectDropdown($event.target.value)"
                                class="w-full min-h-[42px] text-xs rounded-xl border border-slate-200 bg-white text-slate-800 focus:border-emerald-600 focus:ring-emerald-600 py-2.5 px-3.5 pr-8 appearance-none cursor-pointer">
                            <option value="" disabled {{ count($activeRegions) !== 1 ? 'selected' : '' }}>-- Pilih Kota Wilayah Layanan --</option>
                            @foreach ($activeRegions as $r)
                                <option value="{{ $r['id'] }}" {{ count($activeRegions) === 1 ? 'selected' : '' }}>{{ $r['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Peta di bawah akan otomatis menampilkan area jangkauan kota yang dipilih.</p>
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

                <!-- Coordinates Hidden -->
                <input type="hidden" id="lat" name="latitude" value="{{ old('latitude', '-6.9175') }}">
                <input type="hidden" id="lng" name="longitude" value="{{ old('longitude', '107.6191') }}">

                <!-- Alamat Lengkap & Patokan -->
                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">
                                Alamat Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <span id="geo-status" class="text-[11px] text-emerald-700 font-medium">Otomatis terisi saat pin peta dipindahkan</span>
                        </div>
                        <textarea id="address_text" name="address_text" rows="2" required placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..."
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
                            <input type="checkbox" id="is_primary" name="is_primary" value="1" {{ old('is_primary', request('from') === 'checkout') ? 'checked' : '' }}
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

            let geocodeTimeout = null;
            function reverseGeocode(lat, lng) {
                const addressInput = document.getElementById('address_text');
                const geoStatus = document.getElementById('geo-status');
                if (!addressInput) return;

                if (geoStatus) {
                    geoStatus.textContent = 'Mendeteksi alamat...';
                }

                clearTimeout(geocodeTimeout);
                geocodeTimeout = setTimeout(() => {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.display_name) {
                                addressInput.value = data.display_name;
                                if (geoStatus) {
                                    geoStatus.textContent = '✓ Alamat diperbarui dari peta';
                                }
                            } else {
                                if (geoStatus) geoStatus.textContent = 'Otomatis terisi saat pin peta dipindahkan';
                            }
                        })
                        .catch(() => {
                            if (geoStatus) geoStatus.textContent = 'Otomatis terisi saat pin peta dipindahkan';
                        });
                }, 400);
            }

            function updateInputs(lat, lng) {
                document.getElementById('lat').value = lat.toFixed(6);
                document.getElementById('lng').value = lng.toFixed(6);
                reverseGeocode(lat, lng);
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

