<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Master Wilayah Layanan') }}
        </h2>
    </x-slot>

    <!-- Leaflet Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <div class="py-8 px-6 sm:px-8 lg:px-10 space-y-6">

        @if (session('status'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-xl shadow-xs flex items-center justify-between text-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
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

        <!-- Card Tambah Wilayah Baru (Clean 2 Kolom) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                    <h3 class="text-base font-bold text-slate-800">Tambah Wilayah Baru</h3>
                </div>
                <div class="flex items-center space-x-1 text-xs">
                    <button type="button" id="btn-mode-preset" onclick="setMode('preset')"
                        class="px-2.5 py-1 rounded-lg font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 transition">
                        Pilih Kota Indonesia (514)
                    </button>
                    <button type="button" id="btn-mode-manual" onclick="setMode('manual')"
                        class="px-2.5 py-1 rounded-lg font-medium text-slate-500 hover:text-slate-800 transition">
                        Gambar Manual
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Kolom Form (5 Kolom) -->
                <form method="POST" action="{{ route('superadmin.regions.store') }}" class="lg:col-span-5 space-y-3.5">
                    @csrf
                    <input type="hidden" id="geojson" name="geojson" value="{{ old('geojson') }}">
                    <input type="hidden" id="coordinates" name="coordinates" value="{{ old('coordinates') }}">

                    <!-- Dropdown Provinsi & Kota (Preset Mode) -->
                    <div id="wrapper-preset-select" class="space-y-3">
                        <div>
                            <label for="select-province" class="block text-xs font-semibold text-slate-700 mb-1">Pilih Provinsi</label>
                            <select id="select-province" onchange="onProvinceChange()" class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">-- Memuat 34 Provinsi... --</option>
                            </select>
                        </div>
                        <div>
                            <label for="select-city" class="block text-xs font-semibold text-slate-700 mb-1">Pilih Kota / Kabupaten</label>
                            <select id="select-city" onchange="onCityChange()" disabled class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 bg-slate-50">
                                <option value="">-- Pilih Provinsi Dahulu --</option>
                            </select>
                        </div>
                        <div>
                            <label for="search-city" class="block text-xs font-semibold text-slate-700 mb-1">Cari Cepat</label>
                            <input type="text" id="search-city" oninput="onSearchCity(this.value)" placeholder="Ketik nama kota (cth: Surabaya, Bandung)..." 
                                class="w-full text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 placeholder:text-xs placeholder-slate-400">
                            <div id="search-results" class="hidden mt-1 max-h-36 overflow-y-auto bg-white border border-emerald-200 rounded-xl shadow-lg text-xs divide-y divide-slate-100 z-20"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Wilayah</label>
                            <input type="text" id="name" name="name" required placeholder="Nama Wilayah..."
                                class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"
                                value="{{ old('name') }}">
                        </div>
                        <div>
                            <label for="timezone" class="block text-xs font-semibold text-slate-700 mb-1">Zona Waktu</label>
                            <select id="timezone" name="timezone" class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="Asia/Jakarta">WIB (Jakarta)</option>
                                <option value="Asia/Makassar">WITA (Makassar)</option>
                                <option value="Asia/Jayapura">WIT (Jayapura)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Status Poligon -->
                    <div class="py-2 px-3 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center justify-between text-xs text-slate-600">
                        <div class="flex items-center overflow-hidden">
                            <span class="w-2 h-2 rounded-full bg-slate-300 mr-2 shrink-0" id="boundary-indicator"></span>
                            <span id="boundary-status-label" class="truncate">Belum ada batas terpilih.</span>
                        </div>
                        <button type="button" onclick="clearCurrentBoundary()" class="text-[11px] text-rose-600 hover:underline shrink-0 ml-2">Reset</button>
                    </div>

                    <div class="pt-2">
                        <button type="submit" id="btn-submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-xs transition text-xs">
                            Simpan Wilayah
                        </button>
                    </div>
                </form>

                <!-- Kolom Peta Leaflet (7 Kolom) -->
                <div class="lg:col-span-7 flex flex-col space-y-2">
                    <div id="map" class="w-full h-80 rounded-xl border border-slate-200 z-0"></div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <span id="map-hint">Pilih kota di sebelah kiri atau klik peta untuk gambar manual.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Wilayah -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-base font-bold text-slate-800">Daftar Wilayah Layanan</h3>
                <span class="text-xs text-slate-500">Total: {{ $regions->count() }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-slate-700 uppercase font-semibold text-[11px] border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5">Nama Wilayah</th>
                            <th class="px-5 py-3.5">Zona Waktu</th>
                            <th class="px-5 py-3.5">Admin Bertugas</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($regions as $region)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 font-semibold text-slate-800">
                                    {{ $region->name }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-500 font-mono text-[11px]">
                                    {{ $region->timezone }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-slate-800 text-xs">{{ $region->admin_assignments_count }}</span>
                                    <span class="text-slate-400 text-xs">Admin</span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $region->status === 'aktif' ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                        {{ $region->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    @if ($region->status === 'nonaktif')
                                        <form method="POST" action="{{ route('superadmin.regions.activate', $region) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" 
                                                class="text-xs font-semibold {{ ($region->admin_assignments_count >= 1) ? 'text-emerald-600 hover:text-emerald-700 hover:underline cursor-pointer' : 'text-slate-300 cursor-not-allowed' }}"
                                                {{ ($region->admin_assignments_count >= 1) ? '' : 'disabled title=Belum memiliki minimal 1 admin bertugas' }}>
                                                <span>Aktifkan</span>
                                                <span>&rarr;</span>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('superadmin.regions.deactivate', $region) }}" class="inline-block">
                                            @csrf
                                            <button type="button" 
                                                onclick="confirmAction({
                                                    title: 'Nonaktifkan Wilayah?',
                                                    message: 'Customer di wilayah ini tidak dapat membuat pesanan baru.',
                                                    type: 'warning',
                                                    confirmText: 'Nonaktifkan',
                                                    form: this.closest('form')
                                                })"
                                                class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline cursor-pointer">
                                                <span>Nonaktifkan</span>
                                                <span>&rarr;</span>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada wilayah layanan yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Script Leaflet & Preset Interaktif -->
    <script>
        let currentMode = 'preset';
        let allProvinces = [];
        let allCities = [];
        let currentGeoJsonLayer = null;
        let manualMarkers = [];
        let manualPolygon = null;
        let manualPoints = [];
        let map = null;

        document.addEventListener('DOMContentLoaded', async function () {
            map = L.map('map').setView([-2.5489, 118.0149], 5);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            map.on('click', function (e) {
                if (currentMode !== 'manual') return;

                const lat = parseFloat(e.latlng.lat.toFixed(6));
                const lng = parseFloat(e.latlng.lng.toFixed(6));

                manualPoints.push([lng, lat]);

                const marker = L.circleMarker([lat, lng], {
                    radius: 5,
                    color: '#059669',
                    fillColor: '#10b981',
                    fillOpacity: 1
                }).addTo(map);

                manualMarkers.push(marker);

                if (manualPoints.length >= 3) {
                    if (manualPolygon) {
                        map.removeLayer(manualPolygon);
                    }
                    const latLngs = manualPoints.map(p => [p[1], p[0]]);
                    manualPolygon = L.polygon(latLngs, {
                        color: '#059669',
                        fillColor: '#34d399',
                        fillOpacity: 0.35
                    }).addTo(map);
                }

                updateManualOutput();
            });

            try {
                const res = await fetch("{{ route('superadmin.regions.presets') }}");
                if (res.ok) {
                    const data = await res.json();
                    allProvinces = data.provinces || [];
                    allCities = data.cities || [];
                    populateProvinces();
                }
            } catch (err) {
                console.error('Gagal memuat preset:', err);
            }
        });

        function setMode(mode) {
            currentMode = mode;
            const btnPreset = document.getElementById('btn-mode-preset');
            const btnManual = document.getElementById('btn-mode-manual');
            const wrapperPreset = document.getElementById('wrapper-preset-select');
            const mapHint = document.getElementById('map-hint');

            clearCurrentBoundary();

            if (mode === 'preset') {
                btnPreset.className = 'px-2.5 py-1 rounded-lg font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 transition';
                btnManual.className = 'px-2.5 py-1 rounded-lg font-medium text-slate-500 hover:text-slate-800 transition';
                wrapperPreset.classList.remove('hidden');
                mapHint.textContent = 'Pilih kota di sebelah kiri.';
            } else {
                btnManual.className = 'px-2.5 py-1 rounded-lg font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 transition';
                btnPreset.className = 'px-2.5 py-1 rounded-lg font-medium text-slate-500 hover:text-slate-800 transition';
                wrapperPreset.classList.add('hidden');
                mapHint.textContent = 'Klik pada peta minimal 3 titik untuk menggambar batas poligon.';
            }
        }

        function populateProvinces() {
            const select = document.getElementById('select-province');
            select.innerHTML = '<option value="">-- Pilih Provinsi (34 Provinsi) --</option>';
            allProvinces.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.name;
                opt.textContent = p.name;
                select.appendChild(opt);
            });
        }

        function onProvinceChange() {
            const provName = document.getElementById('select-province').value;
            const citySelect = document.getElementById('select-city');
            citySelect.innerHTML = '<option value="">-- Pilih Kota / Kabupaten --</option>';

            if (!provName) {
                citySelect.disabled = true;
                citySelect.classList.add('bg-slate-50');
                return;
            }

            const filtered = allCities.filter(c => c.province === provName);
            filtered.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = `${c.alt_name || c.name}`;
                citySelect.appendChild(opt);
            });

            citySelect.disabled = false;
            citySelect.classList.remove('bg-slate-50');
        }

        async function onCityChange() {
            const cityId = document.getElementById('select-city').value;
            if (!cityId) return;
            await loadCityBoundary(cityId);
        }

        function onSearchCity(query) {
            const resultsBox = document.getElementById('search-results');
            query = (query || '').trim().toLowerCase();

            if (query.length < 2) {
                resultsBox.classList.add('hidden');
                resultsBox.innerHTML = '';
                return;
            }

            const matches = allCities.filter(c => 
                (c.alt_name && c.alt_name.toLowerCase().includes(query)) ||
                (c.name && c.name.toLowerCase().includes(query)) ||
                (c.province && c.province.toLowerCase().includes(query))
            ).slice(0, 8);

            if (matches.length === 0) {
                resultsBox.innerHTML = '<div class="p-2 text-slate-400">Tidak ada kota yang cocok</div>';
                resultsBox.classList.remove('hidden');
                return;
            }

            resultsBox.innerHTML = matches.map(c => `
                <div onclick="selectFromSearch('${c.id}')" class="p-2 hover:bg-emerald-50 cursor-pointer flex justify-between items-center">
                    <span class="font-semibold text-slate-800">${c.alt_name || c.name}</span>
                    <span class="text-[10px] text-emerald-600 font-mono">${c.province}</span>
                </div>
            `).join('');

            resultsBox.classList.remove('hidden');
        }

        async function selectFromSearch(cityId) {
            document.getElementById('search-results').classList.add('hidden');
            const city = allCities.find(c => c.id === cityId);
            if (city) {
                document.getElementById('search-city').value = `${city.alt_name || city.name}`;
                document.getElementById('select-province').value = city.province;
                onProvinceChange();
                document.getElementById('select-city').value = cityId;
            }
            await loadCityBoundary(cityId);
        }

        async function loadCityBoundary(cityId) {
            try {
                const url = "{{ route('superadmin.regions.cityBoundary', ':id') }}".replace(':id', cityId);
                const res = await fetch(url);
                if (!res.ok) throw new Error('Gagal mengambil batas wilayah');
                const data = await res.json();

                clearCurrentBoundary(false);

                currentGeoJsonLayer = L.geoJSON(data.geometry, {
                    style: {
                        color: '#059669',
                        fillColor: '#10b981',
                        fillOpacity: 0.35,
                        weight: 2
                    }
                }).addTo(map);

                map.fitBounds(currentGeoJsonLayer.getBounds(), { padding: [20, 20] });

                document.getElementById('name').value = data.alt_name || data.name;
                document.getElementById('timezone').value = data.timezone;
                document.getElementById('geojson').value = JSON.stringify(data.geometry);
                document.getElementById('coordinates').value = '';

                document.getElementById('boundary-indicator').className = 'w-2 h-2 rounded-full bg-emerald-500 mr-2 shrink-0';
                document.getElementById('boundary-status-label').innerHTML = `<strong class="text-emerald-800">${data.alt_name || data.name}</strong> terpilih`;
            } catch (err) {
                console.error(err);
            }
        }

        function clearCurrentBoundary(resetForm = true) {
            if (currentGeoJsonLayer) {
                map.removeLayer(currentGeoJsonLayer);
                currentGeoJsonLayer = null;
            }
            if (manualPolygon) {
                map.removeLayer(manualPolygon);
                manualPolygon = null;
            }
            manualMarkers.forEach(m => map.removeLayer(m));
            manualMarkers = [];
            manualPoints = [];

            document.getElementById('geojson').value = '';
            document.getElementById('coordinates').value = '';

            document.getElementById('boundary-indicator').className = 'w-2 h-2 rounded-full bg-slate-300 mr-2 shrink-0';
            document.getElementById('boundary-status-label').textContent = 'Belum ada batas terpilih.';

            if (resetForm) {
                document.getElementById('name').value = '';
                document.getElementById('search-city').value = '';
                document.getElementById('select-province').value = '';
                document.getElementById('select-city').innerHTML = '<option value="">-- Pilih Provinsi Dahulu --</option>';
                document.getElementById('select-city').disabled = true;
            }
        }

        function updateManualOutput() {
            const hiddenCoords = document.getElementById('coordinates');
            const hiddenGeoJson = document.getElementById('geojson');
            const indicator = document.getElementById('boundary-indicator');
            const label = document.getElementById('boundary-status-label');

            hiddenGeoJson.value = '';

            if (manualPoints.length === 0) {
                hiddenCoords.value = '';
                indicator.className = 'w-2 h-2 rounded-full bg-slate-300 mr-2 shrink-0';
                label.textContent = '0 titik manual.';
            } else {
                hiddenCoords.value = JSON.stringify(manualPoints);
                if (manualPoints.length >= 3) {
                    indicator.className = 'w-2 h-2 rounded-full bg-emerald-500 mr-2 shrink-0';
                    label.innerHTML = `<strong>${manualPoints.length} titik</strong> manual (Sah)`;
                } else {
                    indicator.className = 'w-2 h-2 rounded-full bg-amber-500 mr-2 shrink-0';
                    label.innerHTML = `${manualPoints.length} titik (min. 3 titik)`;
                }
            }
        }
    </script>
</x-app-layout>
