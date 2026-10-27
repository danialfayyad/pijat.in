<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                    Langkah 2: Kelengkapan Berkas Mitra
                </span>
                <h2 class="text-xl font-bold text-slate-800 leading-tight mt-1.5">
                    Formulir Pendaftaran & Verifikasi Kemitraan
                </h2>
            </div>
            <div class="text-right text-xs text-slate-400">
                SOP Kemitraan Versi 2.2
            </div>
        </div>
    </x-slot>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if ($user->isDitolak())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs flex items-start space-x-3">
                <svg class="w-6 h-6 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h3 class="font-bold text-sm">Pendaftaran Sebelumnya Perlu Perbaikan</h3>
                    <p class="text-xs mt-0.5 leading-relaxed">
                        Alasan penolakan: <strong>{{ $user->rejection_reason ?? 'Berkas tidak memenuhi kriteria.' }}</strong>.
                        Silakan perbarui dokumen dan informasi di bawah ini untuk mengajukan verifikasi ulang.
                    </p>
                </div>
            </div>
        @endif

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('mitra.onboarding.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Card 1: Identitas & Dokumen KTP -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-5">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm">
                        1
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Identitas Kependudukan & Foto Wajah</h3>
                        <p class="text-xs text-slate-500">Wajib sesuai dengan e-KTP fisik asli Anda.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Induk Kependudukan (NIK) <span class="text-rose-500">*</span></label>
                        <input type="text" name="nik" maxlength="16" value="{{ old('nik', $user->nik) }}" required placeholder="16 digit nomor NIK di KTP"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                        @error('nik') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Kontak WhatsApp / HP <span class="text-rose-500">*</span></label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required placeholder="Contoh: 081234567890"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                        @error('phone') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div class="border border-dashed border-slate-200 rounded-xl p-4 bg-slate-50/50 hover:bg-emerald-50/20 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1">Foto e-KTP Asli <span class="text-rose-500">*</span></label>
                        <p class="text-[11px] text-slate-500 mb-2.5">Foto jelas, tidak buram, seluruh teks dan NIK terbaca.</p>
                        <input type="file" name="ktp_file" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200">
                        @error('ktp_file') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        @if ($partnerProfile?->ktp_path)
                            <p class="text-[10px] text-emerald-600 mt-1.5 font-medium">✓ File KTP sudah tersimpan (unggah baru jika ingin mengganti).</p>
                        @endif
                    </div>

                    <div class="border border-dashed border-slate-200 rounded-xl p-4 bg-slate-50/50 hover:bg-emerald-50/20 transition">
                        <label class="block text-xs font-bold text-slate-800 mb-1">Foto Selfie Memegang KTP <span class="text-rose-500">*</span></label>
                        <p class="text-[11px] text-slate-500 mb-2.5">Wajah dan kartu KTP harus terlihat jelas dan tidak terpotong.</p>
                        <input type="file" name="selfie_file" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200">
                        @error('selfie_file') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        @if ($partnerProfile?->selfie_path)
                            <p class="text-[10px] text-emerald-600 mt-1.5 font-medium">✓ Foto selfie sudah tersimpan.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dokumen Pendukung SKCK (Opsional)</label>
                    <input type="file" name="skck_file" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>
            </div>

            <!-- Card 2: Lokasi Domisili & Pin Peta Spasial -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-5">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm">
                        2
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Alamat Domisili & Wilayah Layanan</h3>
                        <p class="text-xs text-slate-500">Klik peta untuk menetapkan titik lokasi domisili Anda (wajib di wilayah layanan aktif).</p>
                    </div>
                </div>

                <div>
                    <div id="map" class="w-full h-72 rounded-xl border border-slate-200 overflow-hidden shadow-inner z-0"></div>
                    <p class="text-[11px] text-slate-400 mt-1.5 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        Klik pada peta di atas untuk memindahkan pin lokasi domisili Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Latitude</label>
                        <input type="text" id="lat" name="latitude" value="{{ old('latitude', '-6.9175') }}" readonly
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-mono text-xs text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Longitude</label>
                        <input type="text" id="lng" name="longitude" value="{{ old('longitude', '107.6191') }}" readonly
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-mono text-xs text-slate-700">
                    </div>
                </div>
                @error('latitude') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap Domisili <span class="text-rose-500">*</span></label>
                    <textarea name="address_text" rows="3" required placeholder="Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan, Kota..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">{{ old('address_text', $partnerProfile?->address_text) }}</textarea>
                    @error('address_text') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Card 3: Rekening Bank Pencairan -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-5">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm">
                        3
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Rekening Bank Pencairan Dana</h3>
                        <p class="text-xs text-slate-500">Nama pemilik rekening <strong>harus sama</strong> dengan nama di KTP (ADM-03).</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Bank <span class="text-rose-500">*</span></label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $bankAccount?->bank_name ?? 'BCA') }}" required placeholder="BCA / Mandiri / BRI / BNI"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                        @error('bank_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Rekening <span class="text-rose-500">*</span></label>
                        <input type="text" name="account_number" value="{{ old('account_number', $bankAccount?->account_number) }}" required placeholder="Contoh: 1234567890"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                        @error('account_number') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pemilik Rekening <span class="text-rose-500">*</span></label>
                        <input type="text" name="account_holder_name" value="{{ old('account_holder_name', $bankAccount?->account_holder_name ?? $user->name) }}" required placeholder="Wajib sesuai e-KTP"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                        @error('account_holder_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Buku Tabungan / Mutasi Mobile Banking (Opsional tapi disarankan)</label>
                    <input type="file" name="bank_proof_file" accept="image/*,.pdf" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>
            </div>

            <!-- Card 4: Kode Etik & Persetujuan SOP -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-4">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm">
                        4
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Persetujuan SOP & Kode Etik Kemitraan</h3>
                        <p class="text-xs text-slate-500">Standar operasional higienitas, etika, dan keselamatan pijat.in.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 text-xs text-slate-600 leading-relaxed max-h-40 overflow-y-auto border border-slate-200/60">
                    <p class="font-bold text-slate-800 mb-1">Poin Utama SOP Kemitraan Pijat.In (Versi 2.2):</p>
                    <ul class="list-disc pl-4 space-y-1 text-slate-600">
                        <li>Layanan pijat profesional hanya diberikan kepada pelanggan <strong>dengan gender yang sama</strong> (strict sesama jenis).</li>
                        <li>Dilarang keras menawarkan, menyetujui, atau melakukan segala bentuk tindakan asusila. Pelanggaran berakibat pemutusan kemitraan permanen dan pelaporan hukum.</li>
                        <li>Mematuhi protokol kebersihan, mengenakan seragam/pakaian rapi dan higienis, serta membawa perlengkapan standar.</li>
                        <li>Tidak meminta biaya tambahan (tips atau add-on) di luar harga resmi yang tercatat di aplikasi.</li>
                    </ul>
                </div>

                <label class="flex items-start space-x-3 cursor-pointer pt-1">
                    <input type="checkbox" name="sop_agreement" value="1" required class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-xs text-slate-700 leading-relaxed">
                        Saya menyatakan bahwa seluruh data di atas adalah benar dan sah, serta saya telah membaca, memahami, dan menyetujui sepenuhnya <strong>SOP & Kode Etik Kemitraan Pijat.In Versi 2.2</strong>.
                    </span>
                </label>
                @error('sop_agreement') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Submit Button -->
            <div class="text-right">
                <button type="submit" class="px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition inline-flex items-center space-x-2">
                    <span>Kirim Berkas Pendaftaran</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </div>
        </form>

    </div>

    <!-- Script Leaflet Interactive Marker Pin -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const initialLat = parseFloat(document.getElementById('lat').value) || -6.9175;
            const initialLng = parseFloat(document.getElementById('lng').value) || 107.6191;

            const map = L.map('map').setView([initialLat, initialLng], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            let marker = L.marker([initialLat, initialLng], {
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
        });
    </script>
</x-app-layout>
