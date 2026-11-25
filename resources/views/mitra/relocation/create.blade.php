<x-pwa-layout title="Pindah Wilayah - Pijat.In" headerTitle="Pindah Wilayah" :backUrl="route('mitra.profile')">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="py-2 space-y-3">

        @if (session('status'))
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        @if ($pendingRequest)
            {{-- CASE A: Permohonan Sedang Diproses --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800">Pengajuan Pindah Wilayah</h3>
                    <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/60">
                        Menunggu Verifikasi
                    </span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between items-center py-1 border-b border-slate-100">
                        <span class="text-slate-400">Rute</span>
                        <span class="font-semibold text-slate-800">{{ $pendingRequest->fromRegion->name }} &rarr; <span class="text-emerald-700">{{ $pendingRequest->toRegion->name }}</span></span>
                    </div>
                    <div class="flex justify-between items-start py-1 border-b border-slate-100">
                        <span class="text-slate-400 shrink-0 mr-3">Alamat Tujuan</span>
                        <span class="text-slate-700 text-right">{{ $pendingRequest->address_text }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1 text-[11px]">
                        <span class="text-slate-400">Waktu Pengajuan</span>
                        <span class="text-slate-600">{{ $pendingRequest->created_at->translatedFormat('d M Y, H:i') }}</span>
                    </div>
                </div>

                <form action="{{ route('mitra.relocation.cancel', $pendingRequest) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan pindah wilayah?');">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl border border-slate-200 hover:border-rose-300 text-slate-600 hover:text-rose-700 text-xs font-medium transition cursor-pointer">
                        Batalkan Pengajuan
                    </button>
                </form>
            </div>

        @elseif ($hasActiveOrders)
            {{-- CASE B: Masih Ada Pesanan Aktif --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 space-y-3">
                <h3 class="text-sm font-bold text-slate-800">Pindah Wilayah Terkunci</h3>
                <p class="text-xs text-slate-500">Selesaikan Pesanan Terlebih Dahulu</p>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Anda masih memiliki {{ $activeOrdersCount }} pesanan aktif atau terjadwal. Harap selesaikan seluruh pesanan sebelum mengajukan pindah wilayah.
                </p>

                <div class="pt-2">
                    <a href="{{ route('mitra.orders.my-schedule') }}" class="w-full py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-xl text-xs transition flex items-center justify-center">
                        Lihat Jadwal Saya
                    </a>
                </div>
            </div>

        @else
            {{-- CASE C: Formulir Ringkas --}}
            <form action="{{ route('mitra.relocation.store') }}" method="POST" id="relocation-form" class="space-y-3">
                @csrf

                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 space-y-3.5">
                    <!-- Rute: Asal & Tujuan -->
                    <div class="grid grid-cols-2 gap-3 items-center">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] text-slate-400 block font-medium">Wilayah Asal</span>
                            <span class="text-xs font-bold text-slate-800 truncate block mt-0.5">Wilayah {{ $currentRegion?->name ?? '-' }}</span>
                        </div>
                        <div>
                            <label for="city_select" class="text-[10px] text-slate-400 block font-medium mb-1">Kota Tujuan</label>
                            <select id="city_select" onchange="onCityChanged(this)"
                                class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 bg-white py-2 font-medium">
                                <option value="">Pilih Kota</option>
                                @foreach ($activeRegions as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Peta -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Pilih Titik Domisili Baru
                        </label>
                        <div class="w-full h-48 rounded-xl border border-slate-200 overflow-hidden" id="relocation-map"></div>
                        @error('latitude')
                            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <input type="hidden" name="latitude" id="lat" value="{{ old('latitude', $defaultLat ?? '') }}">
                    <input type="hidden" name="longitude" id="lng" value="{{ old('longitude', $defaultLng ?? '') }}">

                    <!-- Alamat Lengkap & Rekomendasi Alamat (Shopee / Grab Autocomplete) -->
                    <div class="relative" id="address-container">
                        <div class="flex items-center justify-between mb-1">
                            <label for="address_text" class="block text-xs font-semibold text-slate-700">
                                Alamat Lengkap
                            </label>
                            <span id="geocode-status" class="text-[10px] text-emerald-600 font-medium hidden">
                                Menyesuaikan pin...
                            </span>
                        </div>

                        <div class="relative">
                            <textarea id="address_text" name="address_text" rows="2" required 
                                placeholder="Ketik nama jalan, desa, atau kecamatan (cth: Trimulyo, Jetis)..."
                                autocomplete="off"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 pr-8">{{ old('address_text') }}</textarea>

                            <button type="button" id="clear-address-btn" onclick="clearAddressInput()" 
                                title="Hapus teks"
                                class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 hidden">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                            <!-- Dropdown Rekomendasi Alamat -->
                            <div id="address-suggestions" 
                                class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-2xl border border-slate-200 z-[1000] overflow-hidden hidden max-h-60 overflow-y-auto divide-y divide-slate-100">
                            </div>
                        </div>

                        <p class="text-[10px] text-slate-400 mt-1">
                            Ketik alamat untuk memilih dari rekomendasi, atau geser pin langsung pada peta.
                        </p>
                        @error('address_text') <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Alasan (Opsional) -->
                    <div>
                        <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1">
                            Alasan Pindah <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input type="text" id="notes" name="notes" value="{{ old('notes') }}" placeholder="Alasan perpindahan..."
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600">
                    </div>

                    <button type="submit"
                        class="w-full py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs transition cursor-pointer">
                        Ajukan Pindah Wilayah
                    </button>
                </div>
            </form>

            <script>
                let map, marker, reverseGeocodeTimeout, searchTimeout;
                let isProgrammaticChange = false;
                let currentSuggestions = [];

                function setCoordinates(lat, lng, syncAddress = true) {
                    document.getElementById('lat').value = lat.toFixed(6);
                    document.getElementById('lng').value = lng.toFixed(6);
                    if (syncAddress) {
                        reverseGeocode(lat, lng);
                    }
                }

                function reverseGeocode(lat, lng) {
                    const addressInput = document.getElementById('address_text');
                    if (!addressInput) return;

                    clearTimeout(reverseGeocodeTimeout);
                    reverseGeocodeTimeout = setTimeout(() => {
                        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.display_name) {
                                    isProgrammaticChange = true;
                                    addressInput.value = data.display_name;
                                    isProgrammaticChange = false;
                                    updateClearButton();
                                    hideSuggestions();
                                }
                            })
                            .catch(() => {});
                    }, 300);
                }

                function updateClearButton() {
                    const addressInput = document.getElementById('address_text');
                    const clearBtn = document.getElementById('clear-address-btn');
                    if (clearBtn && addressInput) {
                        if (addressInput.value.trim().length > 0) {
                            clearBtn.classList.remove('hidden');
                        } else {
                            clearBtn.classList.add('hidden');
                        }
                    }
                }

                function clearAddressInput() {
                    const addressInput = document.getElementById('address_text');
                    if (addressInput) {
                        addressInput.value = '';
                        addressInput.focus();
                        updateClearButton();
                        hideSuggestions();
                    }
                }

                function hideSuggestions() {
                    const list = document.getElementById('address-suggestions');
                    if (list) {
                        list.classList.add('hidden');
                        list.innerHTML = '';
                    }
                    currentSuggestions = [];
                }

                function generateQueryCandidates(rawText, cityName) {
                    const candidates = [];
                    const cleanCity = cityName
                        ? cityName.replace(/\b(kabupaten|kab\.|kota|kotamadya)\s+/gi, '').trim()
                        : '';

                    const cleaned = rawText
                        .replace(/\b(rt|rw)\s*\/?\s*\d+/gi, ' ')
                        .replace(/\bno(mor|\.)?\s*\d+[a-z]?\b/gi, ' ')
                        .replace(/\b(desa|kelurahan|kel\.|dusun|dukuh|kp\.|kampung)\s+/gi, ' ')
                        .replace(/\b(kecamatan|kec\.)\s+/gi, ' ')
                        .replace(/\b(kabupaten|kab\.|kotamadya|kota)\s+/gi, ' ')
                        .replace(/[,;]+/g, ', ')
                        .replace(/\s+/g, ' ')
                        .trim();

                    if (!cleaned) return candidates;

                    if (cleanCity && !cleaned.toLowerCase().includes(cleanCity.toLowerCase())) {
                        candidates.push(`${cleaned}, ${cleanCity}`);
                    }
                    candidates.push(cleaned);

                    const commaParts = cleaned.split(',').map(p => p.trim()).filter(Boolean);
                    if (commaParts.length > 1) {
                        for (let i = 1; i < commaParts.length; i++) {
                            const sub = commaParts.slice(i).join(', ');
                            if (cleanCity && !sub.toLowerCase().includes(cleanCity.toLowerCase())) {
                                candidates.push(`${sub}, ${cleanCity}`);
                            }
                            candidates.push(sub);
                        }
                    }

                    const words = cleaned.replace(/[,.]+/g, ' ').split(/\s+/).filter(w => w.length > 2);
                    if (words.length > 1) {
                        for (let i = words.length - 1; i >= 0; i--) {
                            const w = words[i];
                            if (cleanCity && w.toLowerCase() !== cleanCity.toLowerCase()) {
                                candidates.push(`${w}, ${cleanCity}`);
                            }
                        }
                    }

                    if (cleanCity && !rawText.toLowerCase().includes(cleanCity.toLowerCase())) {
                        candidates.push(`${rawText.trim()}, ${cleanCity}`);
                    }

                    return [...new Set(candidates)];
                }

                function fetchAddressSuggestions(rawText) {
                    if (isProgrammaticChange) return;

                    updateClearButton();

                    if (!rawText || rawText.trim().length < 3) {
                        hideSuggestions();
                        return;
                    }

                    const list = document.getElementById('address-suggestions');
                    const statusEl = document.getElementById('geocode-status');

                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(async () => {
                        const citySelect = document.getElementById('city_select');
                        const cityName = citySelect && citySelect.selectedIndex > 0 ? citySelect.options[citySelect.selectedIndex].text : '';

                        const candidates = generateQueryCandidates(rawText, cityName);
                        if (candidates.length === 0) {
                            hideSuggestions();
                            return;
                        }

                        if (statusEl) {
                            statusEl.textContent = 'Mencari rekomendasi...';
                            statusEl.classList.remove('hidden');
                        }

                        list.innerHTML = `
                            <div class="px-3.5 py-3 text-xs text-slate-500 flex items-center space-x-2">
                                <svg class="animate-spin w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Mencari rekomendasi alamat...</span>
                            </div>
                        `;
                        list.classList.remove('hidden');

                        let results = [];
                        for (const query of candidates) {
                            try {
                                const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(query)}&addressdetails=1&limit=5&countrycodes=id`;
                                const res = await fetch(url);
                                const data = await res.json();
                                if (data && data.length > 0) {
                                    results = data;
                                    break;
                                }
                            } catch (err) {}
                        }

                        if (statusEl) {
                            statusEl.classList.add('hidden');
                        }

                        if (results.length === 0) {
                            list.innerHTML = `
                                <div class="px-3.5 py-3 text-xs text-slate-400 text-center">
                                    Tidak ada rekomendasi lokasi. Coba tulis nama desa/kecamatan atau geser pin langsung pada peta.
                                </div>
                            `;
                            return;
                        }

                        currentSuggestions = results;
                        list.innerHTML = results.map((item, index) => {
                            const title = item.name || (item.display_name ? item.display_name.split(',')[0] : 'Lokasi');
                            const subtitle = item.display_name || '';
                            return `
                                <button type="button" onclick="selectSuggestion(${index})"
                                    class="w-full text-left px-3.5 py-2.5 hover:bg-emerald-50/60 focus:bg-emerald-50/60 flex items-start space-x-2.5 transition group cursor-pointer">
                                    <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5 group-hover:bg-emerald-600 group-hover:text-white transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-semibold text-slate-800 truncate group-hover:text-emerald-900 transition">
                                            ${title}
                                        </div>
                                        <div class="text-[10px] text-slate-500 line-clamp-1 mt-0.5">
                                            ${subtitle}
                                        </div>
                                    </div>
                                </button>
                            `;
                        }).join('');
                    }, 350);
                }

                function selectSuggestion(index) {
                    const item = currentSuggestions[index];
                    if (!item) return;

                    const addressInput = document.getElementById('address_text');
                    const lat = parseFloat(item.lat);
                    const lng = parseFloat(item.lon);

                    isProgrammaticChange = true;
                    addressInput.value = item.display_name;
                    isProgrammaticChange = false;

                    updateClearButton();
                    hideSuggestions();

                    document.getElementById('lat').value = lat.toFixed(6);
                    document.getElementById('lng').value = lng.toFixed(6);

                    marker.setLatLng([lat, lng]);

                    if (item.boundingbox && item.boundingbox.length === 4) {
                        const south = parseFloat(item.boundingbox[0]);
                        const north = parseFloat(item.boundingbox[1]);
                        const west = parseFloat(item.boundingbox[2]);
                        const east = parseFloat(item.boundingbox[3]);
                        map.fitBounds([[south, west], [north, east]], { maxZoom: 16 });
                    } else {
                        map.setView([lat, lng], 15);
                    }

                    const statusEl = document.getElementById('geocode-status');
                    if (statusEl) {
                        statusEl.textContent = 'Pin disesuaikan';
                        statusEl.classList.remove('hidden');
                        setTimeout(() => {
                            statusEl.classList.add('hidden');
                            statusEl.textContent = 'Menyesuaikan pin...';
                        }, 1500);
                    }
                }

                function onCityChanged(selectEl) {
                    const addressInput = document.getElementById('address_text');
                    if (addressInput && addressInput.value.trim().length >= 3) {
                        fetchAddressSuggestions(addressInput.value);
                    } else if (selectEl.selectedIndex > 0) {
                        searchCityCenter(selectEl.options[selectEl.selectedIndex].text);
                    }
                }

                function searchCityCenter(cityName) {
                    if (!cityName || cityName.includes('Pilih Kota')) return;
                    const cleanCity = cityName.replace(/\b(kabupaten|kab\.|kota|kotamadya)\s+/gi, '').trim();

                    fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(cleanCity)}&limit=1&countrycodes=id`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                const lat = parseFloat(data[0].lat);
                                const lng = parseFloat(data[0].lon);
                                if (data[0].boundingbox && data[0].boundingbox.length === 4) {
                                    map.fitBounds([
                                        [parseFloat(data[0].boundingbox[0]), parseFloat(data[0].boundingbox[2])],
                                        [parseFloat(data[0].boundingbox[1]), parseFloat(data[0].boundingbox[3])]
                                    ], { maxZoom: 13 });
                                } else {
                                    map.setView([lat, lng], 13);
                                }
                                marker.setLatLng([lat, lng]);
                                setCoordinates(lat, lng, true);
                            }
                        })
                        .catch(() => {});
                }

                document.addEventListener('DOMContentLoaded', function () {
                    const latInput = document.getElementById('lat');
                    const lngInput = document.getElementById('lng');
                    const addressInput = document.getElementById('address_text');

                    const initLat = parseFloat(latInput?.value) || {{ $defaultLat ?? '-7.7956' }};
                    const initLng = parseFloat(lngInput?.value) || {{ $defaultLng ?? '110.3695' }};

                    map = L.map('relocation-map').setView([initLat, initLng], 12);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 18,
                        attribution: '© OpenStreetMap'
                    }).addTo(map);

                    marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);

                    marker.on('dragend', function (e) {
                        const pos = marker.getLatLng();
                        setCoordinates(pos.lat, pos.lng, true);
                    });

                    map.on('click', function (e) {
                        marker.setLatLng(e.latlng);
                        setCoordinates(e.latlng.lat, e.latlng.lng, true);
                    });

                    if (addressInput) {
                        addressInput.addEventListener('input', function (e) {
                            fetchAddressSuggestions(e.target.value);
                        });

                        addressInput.addEventListener('focus', function (e) {
                            if (e.target.value.trim().length >= 3 && currentSuggestions.length > 0) {
                                document.getElementById('address-suggestions')?.classList.remove('hidden');
                            }
                        });

                        updateClearButton();
                    }

                    document.addEventListener('click', function (e) {
                        const container = document.getElementById('address-container');
                        if (container && !container.contains(e.target)) {
                            hideSuggestions();
                        }
                    });

                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape') {
                            hideSuggestions();
                        }
                    });
                });
            </script>
        @endif

    </div>
</x-pwa-layout>
