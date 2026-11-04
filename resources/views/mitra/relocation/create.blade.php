<x-pwa-layout title="Pindah Wilayah Domisili - Pijat.In" headerTitle="Pindah Wilayah" :backUrl="route('mitra.profile')">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="py-2 space-y-4">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @if ($pendingRequest)
            {{-- CASE A: Pending Relocation Request exists --}}
            <div class="bg-white rounded-2xl p-5 border border-amber-200 shadow-xs space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider">
                            Menunggu Verifikasi Admin
                        </span>
                        <h2 class="text-sm font-bold text-slate-900 mt-1">Permohonan Relokasi Sedang Diproses</h2>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/80 text-xs text-amber-950 leading-relaxed">
                    Permohonan perpindahan domisili Anda sedang ditinjau oleh Admin Wilayah <strong>{{ $pendingRequest->toRegion->name }}</strong>. Selama verifikasi berlangsung, akun kemitraan dinonaktifkan sementara dan penarikan saldo ditangguhkan demi integritas akun.
                </div>

                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-2.5 text-xs">
                    <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                        <span class="text-slate-500 font-medium">Wilayah Asal:</span>
                        <span class="font-bold text-slate-900">{{ $pendingRequest->fromRegion->name }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                        <span class="text-slate-500 font-medium">Wilayah Tujuan:</span>
                        <span class="font-bold text-emerald-700">{{ $pendingRequest->toRegion->name }}</span>
                    </div>
                    <div class="flex justify-between items-start py-1 border-b border-slate-200/60">
                        <span class="text-slate-500 font-medium shrink-0 mr-2">Alamat Baru:</span>
                        <span class="font-medium text-slate-800 text-right">{{ $pendingRequest->address_text }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-slate-200/60">
                        <span class="text-slate-500 font-medium">Koordinat:</span>
                        <span class="font-mono text-slate-700">{{ number_format($pendingRequest->latitude, 5) }}, {{ number_format($pendingRequest->longitude, 5) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-slate-500 font-medium">Tanggal Pengajuan:</span>
                        <span class="text-slate-700">{{ $pendingRequest->created_at->translatedFormat('d M Y, H:i') }}</span>
                    </div>
                </div>

                <form action="{{ route('mitra.relocation.cancel', $pendingRequest) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan perpindahan wilayah ini? Akun Anda akan kembali terhubung dengan wilayah asal.');">
                    @csrf
                    <button type="submit" class="w-full min-h-[44px] py-2.5 rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-50 font-bold text-xs transition cursor-pointer flex items-center justify-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Batalkan Permohonan Relokasi</span>
                    </button>
                </form>
            </div>

        @elseif ($hasActiveOrders)
            {{-- CASE B: Has Active or Scheduled Orders (Prerequisite Guard) --}}
            <div class="bg-white rounded-2xl p-5 border border-rose-200 shadow-xs space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 uppercase tracking-wider">
                            Pindah Wilayah Terkunci
                        </span>
                        <h2 class="text-sm font-bold text-slate-900 mt-1">Selesaikan Pesanan Terlebih Dahulu</h2>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-rose-50/70 border border-rose-200/80 text-xs text-rose-950 leading-relaxed">
                    Anda saat ini masih memiliki <strong>{{ $activeOrdersCount }} pesanan</strong> yang berstatus aktif atau telah dijadwalkan (FCFS/Booking).
                    Sistem melarang perpindahan wilayah selama masih ada tanggung jawab layanan kepada pelanggan di wilayah saat ini.
                </div>

                <div class="space-y-2 pt-1">
                    <a href="{{ route('mitra.orders.my-schedule') }}" class="w-full min-h-[44px] py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs transition cursor-pointer flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Lihat Jadwal & Pesanan Saya</span>
                    </a>
                    <a href="{{ route('mitra.profile') }}" class="w-full min-h-[44px] py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold rounded-xl text-xs transition cursor-pointer flex items-center justify-center">
                        Kembali ke Profil
                    </a>
                </div>
            </div>

        @else
            {{-- CASE C: Eligible to Apply (MIT-22 Form) --}}
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-xs text-blue-950 flex items-start space-x-3">
                <svg class="w-5 h-5 text-blue-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="leading-relaxed">
                    <strong>Ketentuan Relokasi Antarwilayah:</strong>
                    Tentukan titik domisili baru Anda pada peta di wilayah operasional yang aktif. Riwayat pesanan, rating bintang, dan riwayat sanksi Anda akan tetap terjaga setelah disetujui.
                </div>
            </div>

            <form action="{{ route('mitra.relocation.store') }}" method="POST" id="relocation-form" class="space-y-4">
                @csrf

                <!-- Wilayah Saat Ini -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Wilayah Asal Saat Ini</span>
                        <span class="text-sm font-black text-slate-900 mt-0.5 block">{{ $currentRegion?->name ?? 'Belum Ditentukan' }}</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                        Aktif
                    </span>
                </div>

                <!-- Pemilihan Titik Peta & Leaflet -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <div>
                            <h3 class="text-xs font-bold text-slate-900">Pilih Titik Domisili Baru</h3>
                            <p class="text-[11px] text-slate-500">Geser pin marker pada peta ke lokasi tempat tinggal / pangkalan baru Anda.</p>
                        </div>
                    </div>

                    <!-- Quick Pan Buttons -->
                    @if ($activeRegions->isNotEmpty())
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Pintasan Kota Tujuan:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($activeRegions as $r)
                                    <button type="button"
                                            onclick="searchCityCenter('{{ $r->name }}')"
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition cursor-pointer">
                                        {{ $r->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Leaflet Container -->
                    <div class="relative rounded-xl overflow-hidden border border-slate-200">
                        <div id="relocation-map" class="h-64 w-full z-0"></div>
                        <div class="absolute bottom-2 left-2 right-2 bg-white/90 backdrop-blur-xs px-3 py-1.5 rounded-lg border border-slate-200 text-[10px] text-slate-600 flex items-center justify-between pointer-events-none z-10">
                            <span id="geo-status">Geser pin peta untuk memilih koordinat</span>
                            <span id="coord-display" class="font-mono text-slate-700 font-bold">-</span>
                        </div>
                    </div>

                    <!-- Hidden Coordinate Inputs -->
                    <input type="hidden" name="latitude" id="lat" value="{{ old('latitude', '-6.9175') }}">
                    <input type="hidden" name="longitude" id="lng" value="{{ old('longitude', '107.6191') }}">

                    <!-- Detail Alamat Teks -->
                    <div>
                        <label for="address_text" class="block text-xs font-semibold text-slate-700 mb-1">
                            Alamat Lengkap Domisili Baru <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="address_text" id="address_text" rows="3" required
                                  placeholder="Contoh: Jl. Diponegoro No. 12, RT 02/RW 04, Kel. Citarum, Kec. Bandung Wetan"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">{{ old('address_text') }}</textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Alamat dapat terisi otomatis saat pin digeser atau dapat Anda edit secara manual agar lebih akurat.</p>
                    </div>

                    <!-- Catatan Tambahan (Opsional) -->
                    <div>
                        <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1">
                            Alasan / Catatan ke Admin Wilayah (Opsional)
                        </label>
                        <textarea name="notes" id="notes" rows="2"
                                  placeholder="Contoh: Pindah tempat tinggal keluarga ke Bandung per bulan ini."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="w-full min-h-[48px] py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow-sm transition cursor-pointer flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Ajukan Permohonan Pindah Wilayah</span>
                    </button>
                </div>
            </form>

            <script>
                let map, marker, geocodeTimeout;

                function updateMarkerPosition(lat, lng) {
                    document.getElementById('lat').value = lat.toFixed(7);
                    document.getElementById('lng').value = lng.toFixed(7);
                    document.getElementById('coord-display').textContent = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                    reverseGeocode(lat, lng);
                }

                function reverseGeocode(lat, lng) {
                    const addressInput = document.getElementById('address_text');
                    const geoStatus = document.getElementById('geo-status');

                    if (geoStatus) {
                        geoStatus.textContent = 'Mendeteksi alamat...';
                    }

                    clearTimeout(geocodeTimeout);
                    geocodeTimeout = setTimeout(() => {
                        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.display_name) {
                                    if (!addressInput.value || addressInput.value.trim() === '') {
                                        addressInput.value = data.display_name;
                                    }
                                    if (geoStatus) {
                                        geoStatus.textContent = '✓ Koordinat siap disematkan';
                                    }
                                } else {
                                    if (geoStatus) geoStatus.textContent = 'Pin peta berhasil disematkan';
                                }
                            })
                            .catch(() => {
                                if (geoStatus) geoStatus.textContent = 'Pin peta berhasil disematkan';
                            });
                    }, 500);
                }

                function searchCityCenter(cityName) {
                    const geoStatus = document.getElementById('geo-status');
                    if (geoStatus) geoStatus.textContent = `Mencari wilayah ${cityName}...`;

                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(cityName + ', Indonesia')}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                const lat = parseFloat(data[0].lat);
                                const lng = parseFloat(data[0].lon);
                                map.setView([lat, lng], 13);
                                marker.setLatLng([lat, lng]);
                                updateMarkerPosition(lat, lng);
                            }
                        })
                        .catch(() => {
                            if (geoStatus) geoStatus.textContent = 'Gagal memusatkan peta';
                        });
                }

                document.addEventListener('DOMContentLoaded', function () {
                    const latInput = document.getElementById('lat');
                    const lngInput = document.getElementById('lng');

                    const initLat = parseFloat(latInput?.value) || -6.9175;
                    const initLng = parseFloat(lngInput?.value) || 107.6191;

                    map = L.map('relocation-map').setView([initLat, initLng], 12);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 18,
                        attribution: '© OpenStreetMap'
                    }).addTo(map);

                    marker = L.marker([initLat, initLng], {
                        draggable: true
                    }).addTo(map);

                    updateMarkerPosition(initLat, initLng);

                    marker.on('dragend', function (e) {
                        const pos = marker.getLatLng();
                        updateMarkerPosition(pos.lat, pos.lng);
                    });

                    map.on('click', function (e) {
                        marker.setLatLng(e.latlng);
                        updateMarkerPosition(e.latlng.lat, e.latlng.lng);
                    });
                });
            </script>
        @endif

    </div>
</x-pwa-layout>

