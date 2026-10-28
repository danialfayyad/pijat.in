<x-pwa-layout title="Kelengkapan Berkas - Pijat.In" headerTitle="Pendaftaran Mitra">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="py-2 space-y-4">
        <div class="mb-1">
            <span class="inline-block text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200">
                Langkah 2: Kelengkapan Berkas
            </span>
            <h2 class="text-base font-extrabold text-slate-900 leading-tight mt-1.5">
                Verifikasi Dokumen Kemitraan
            </h2>
            <p class="text-xs text-slate-500">Isi data dan lampirkan dokumen sesuai identitas KTP asli.</p>
        </div>

        @if ($user->isDitolak())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 shadow-xs flex items-start space-x-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h3 class="font-bold text-sm">Pendaftaran Sebelumnya Perlu Perbaikan</h3>
                    <p class="text-xs mt-1 leading-relaxed text-rose-800">
                        Alasan penolakan: <strong>{{ $user->rejection_reason ?? 'Berkas tidak memenuhi kriteria.' }}</strong>.
                        Silakan perbarui dokumen dan informasi di bawah ini untuk mengajukan verifikasi ulang.
                    </p>
                </div>
            </div>
        @endif

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form action="{{ route('mitra.onboarding.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Card 1: Identitas & Dokumen KTP -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 space-y-4">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-xs">
                        1
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Identitas Kependudukan dan Foto Wajah</h3>
                        <p class="text-xs text-slate-500">Wajib sesuai dengan e-KTP fisik asli Anda.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Induk Kependudukan (NIK) <span class="text-rose-500">*</span></label>
                        <input type="text" name="nik" maxlength="16" value="{{ old('nik', $user->nik) }}" required placeholder="16 digit nomor NIK di KTP"
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 font-mono transition">
                        @error('nik') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Kontak WhatsApp / HP <span class="text-rose-500">*</span></label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required placeholder="Contoh: 081234567890"
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 font-mono transition">
                        @error('phone') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/60 hover:bg-emerald-50/30 transition">
                        <label class="block text-xs font-bold text-slate-900 mb-0.5">Foto e-KTP Asli <span class="text-rose-500">*</span></label>
                        <p class="text-[11px] text-slate-500 mb-3">Foto jelas, tidak buram, seluruh teks dan NIK terbaca.</p>
                        <input type="file" name="ktp_file" accept="image/*,.pdf" class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-700 file:text-white hover:file:bg-emerald-800 cursor-pointer">
                        @error('ktp_file') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        @if ($partnerProfile?->ktp_path)
                            <p class="text-xs text-emerald-700 mt-2 font-medium">✓ File KTP sudah tersimpan (unggah baru jika ingin mengganti).</p>
                        @endif
                    </div>

                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/60 hover:bg-emerald-50/30 transition">
                        <label class="block text-xs font-bold text-slate-900 mb-0.5">Foto Selfie Memegang KTP <span class="text-rose-500">*</span></label>
                        <p class="text-[11px] text-slate-500 mb-3">Wajah dan kartu KTP harus terlihat jelas dan tidak terpotong.</p>
                        <input type="file" name="selfie_file" accept="image/*,.pdf" class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-700 file:text-white hover:file:bg-emerald-800 cursor-pointer">
                        @error('selfie_file') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                        @if ($partnerProfile?->selfie_path)
                            <p class="text-xs text-emerald-700 mt-2 font-medium">✓ Foto selfie sudah tersimpan.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dokumen Pendukung SKCK (Opsional)</label>
                    <input type="file" name="skck_file" accept="image/*,.pdf" class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300 cursor-pointer">
                </div>
            </div>

            <!-- Card 2: Lokasi Domisili & Wilayah Layanan (Pilih Kota yang Tersedia) -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 space-y-4"
                 x-data="{
                    searchQuery: '',
                    selectedRegionId: '{{ old('region_id', $partnerProfile?->region_id ?? '') }}',
                    regions: @js($activeRegions),
                    get filteredRegions() {
                        if (!this.searchQuery.trim()) return this.regions;
                        const q = this.searchQuery.toLowerCase();
                        return this.regions.filter(r => r.name.toLowerCase().includes(q));
                    },
                    selectRegion(region) {
                        this.selectedRegionId = region.id;
                        if (window.onMitraRegionSelected) {
                            window.onMitraRegionSelected(region);
                        }
                    },
                    clearRegion() {
                        this.selectedRegionId = '';
                        if (window.onMitraRegionCleared) {
                            window.onMitraRegionCleared();
                        }
                    },
                    getSelectedRegionName() {
                        const r = this.regions.find(x => x.id == this.selectedRegionId);
                        return r ? r.name : '';
                    }
                 }">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-xs">
                        2
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Wilayah Layanan dan Alamat Domisili</h3>
                        <p class="text-xs text-slate-500">Pilih kota layanan yang tersedia, lalu tentukan titik domisili Anda.</p>
                    </div>
                </div>

                <!-- Input Hidden region_id -->
                <input type="hidden" name="region_id" id="region_id"
                    x-effect="$el.value = selectedRegionId">

                <!-- 1. Cari Kota / Wilayah Layanan -->
                <div>
                    <label for="search_city" class="block text-xs font-semibold text-slate-700 mb-1">
                        1. Cari Kota / Wilayah Layanan
                    </label>
                    <div class="relative">
                        <input type="text" id="search_city" x-model="searchQuery" 
                            placeholder="Ketik nama kota (cth: Surabaya, Malang, Bandung)..."
                            class="w-full min-h-[44px] text-xs rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 placeholder-slate-400 pl-8 py-2.5">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="min-h-[44px] min-w-[44px] absolute right-0 top-0 flex items-center justify-center text-slate-400 hover:text-slate-600 text-xs cursor-pointer">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- 2. Klik Kota untuk Memilih Sekaligus -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-700">2. Klik Kota untuk Memilih Wilayah Operasional:</span>
                        <button type="button" x-show="selectedRegionId" @click="clearRegion()" class="min-h-[44px] text-rose-600 hover:text-rose-800 text-xs font-semibold cursor-pointer">
                            Batal Pilih
                        </button>
                    </div>

                    <!-- Grid Chips Kota -->
                    <div class="max-h-52 overflow-y-auto p-2.5 bg-slate-50/80 rounded-xl border border-slate-200/90 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <template x-for="r in filteredRegions" :key="r.id">
                            <div @click="selectRegion(r)"
                                 class="min-h-[44px] p-3 rounded-xl border cursor-pointer select-none transition flex items-center justify-between text-xs"
                                 :class="{
                                     'bg-emerald-700 text-white font-bold border-emerald-700 shadow-xs': selectedRegionId == r.id,
                                     'bg-white text-slate-800 border-slate-200 hover:border-emerald-300': selectedRegionId != r.id
                                 }">
                                <div class="truncate mr-1">
                                    <span x-text="r.name" class="block truncate"></span>
                                    <span class="text-[11px] block font-normal" 
                                          :class="selectedRegionId == r.id ? 'text-emerald-100' : 'text-slate-500'"
                                          x-text="r.timezone || 'WIB'"></span>
                                </div>
                                <span class="text-sm font-bold shrink-0" 
                                      :class="selectedRegionId == r.id ? 'text-white' : 'text-slate-400'" 
                                      x-text="selectedRegionId == r.id ? '✓' : '+'"></span>
                            </div>
                        </template>

                        <div x-show="filteredRegions.length === 0 && regions.length > 0" class="col-span-full py-4 text-center text-xs text-slate-500">
                            Tidak ada kota layanan yang sesuai pencarian "<span x-text="searchQuery"></span>".
                        </div>

                        <div x-show="regions.length === 0" class="col-span-full py-4 text-center text-xs text-amber-900 bg-amber-50 rounded-xl border border-amber-200">
                            Saat ini belum ada kota layanan yang berstatus aktif. Silakan hubungi admin.
                        </div>
                    </div>

                    <!-- Footer Status Pemilihan Kota -->
                    <div class="text-xs pt-1 flex items-center justify-between">
                        <div>
                            <span x-show="!selectedRegionId" class="text-slate-500">Belum ada kota yang dipilih.</span>
                            <span x-show="selectedRegionId" class="text-emerald-800 font-semibold flex items-center">
                                <span class="inline-block w-2 h-2 rounded-full bg-emerald-600 mr-1.5"></span>
                                <span>Kota Terpilih: <strong x-text="getSelectedRegionName()"></strong></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 3. Peta Interaktif & Penempatan Pin Domisili -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-slate-700">
                            3. Titik Lokasi Domisili (Peta Spasial)
                        </label>
                        <span class="text-xs text-emerald-700 font-medium" x-show="selectedRegionId">
                            Peta otomatis diarahkan ke kota terpilih
                        </span>
                    </div>

                    <div id="map" class="w-full h-64 rounded-xl border border-slate-200 overflow-hidden shadow-inner z-0"></div>
                    
                    <p class="text-xs text-slate-500 flex items-center">
                        <svg class="w-4 h-4 mr-1 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        Klik peta atau seret pin untuk menentukan titik alamat tempat tinggal Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Latitude</label>
                        <input type="text" id="lat" name="latitude" value="{{ old('latitude', '-6.9175') }}" readonly
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-mono text-xs text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Longitude</label>
                        <input type="text" id="lng" name="longitude" value="{{ old('longitude', '107.6191') }}" readonly
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-mono text-xs text-slate-800">
                    </div>
                </div>
                @error('latitude') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap Domisili <span class="text-rose-500">*</span></label>
                    <textarea name="address_text" rows="3" required placeholder="Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan, Kota..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">{{ old('address_text', $partnerProfile?->address_text) }}</textarea>
                    @error('address_text') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Card 3: Rekening Bank Pencairan -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 space-y-4">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-xs">
                        3
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Rekening Bank Pencairan Dana</h3>
                        <p class="text-xs text-slate-500">Nama pemilik rekening <strong>harus sama</strong> dengan nama di KTP (ADM-03).</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Bank <span class="text-rose-500">*</span></label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $bankAccount?->bank_name ?? 'BCA') }}" required placeholder="BCA / Mandiri / BRI / BNI"
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">
                        @error('bank_name') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Rekening <span class="text-rose-500">*</span></label>
                        <input type="text" name="account_number" value="{{ old('account_number', $bankAccount?->account_number) }}" required placeholder="Contoh: 1234567890"
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 font-mono transition">
                        @error('account_number') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pemilik Rekening <span class="text-rose-500">*</span></label>
                        <input type="text" name="account_holder_name" value="{{ old('account_holder_name', $bankAccount?->account_holder_name ?? $user->name) }}" required placeholder="Wajib sesuai e-KTP"
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">
                        @error('account_holder_name') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Buku Tabungan / Mutasi Mobile Banking (Opsional)</label>
                    <input type="file" name="bank_proof_file" accept="image/*,.pdf" class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300 cursor-pointer">
                </div>
            </div>

            <!-- Card 4: Kode Etik & Persetujuan SOP -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/90 p-5 space-y-4">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-xs">
                        4
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Persetujuan SOP dan Kode Etik Kemitraan</h3>
                        <p class="text-xs text-slate-500">Standar operasional higienitas, etika, dan keselamatan pijat.in.</p>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 text-xs text-slate-700 leading-relaxed max-h-44 overflow-y-auto border border-slate-200/80">
                    <p class="font-bold text-slate-900 mb-1.5">Poin Utama SOP Kemitraan Pijat.In (Versi 2.2):</p>
                    <ul class="list-disc pl-4 space-y-1.5 text-slate-700">
                        <li>Layanan pijat profesional hanya diberikan kepada pelanggan <strong>dengan gender yang sama</strong> (strict sesama jenis).</li>
                        <li>Dilarang keras menawarkan, menyetujui, atau melakukan segala bentuk tindakan asusila. Pelanggaran berakibat pemutusan kemitraan permanen dan pelaporan hukum.</li>
                        <li>Mematuhi protokol kebersihan, mengenakan seragam atau pakaian rapi dan higienis, serta membawa perlengkapan standar.</li>
                        <li>Tidak meminta biaya tambahan (tips atau add-on) di luar harga resmi yang tercatat di aplikasi.</li>
                    </ul>
                </div>

                <label class="flex items-start space-x-3 cursor-pointer pt-1 min-h-[44px]">
                    <input type="checkbox" name="sop_agreement" value="1" required class="mt-1 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600 cursor-pointer">
                    <span class="text-xs text-slate-800 leading-relaxed select-none">
                        Saya menyatakan bahwa seluruh data di atas adalah benar dan sah, serta saya telah membaca, memahami, dan menyetujui sepenuhnya <strong>SOP & Kode Etik Kemitraan Pijat.In Versi 2.2</strong>.
                    </span>
                </label>
                @error('sop_agreement') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>

            <!-- Submit Button -->
            <div class="pt-1">
                <button type="submit" class="w-full min-h-[48px] py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow-sm transition inline-flex items-center justify-center space-x-2 cursor-pointer">
                    <span>Kirim Berkas Pendaftaran</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </div>
        </form>

    </div>

    <!-- Script Leaflet Interactive Marker Pin & Region Auto-Centering -->
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

            // When a region is selected from the chips
            window.onMitraRegionSelected = function(region) {
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
                map.fitBounds(bounds, { padding: [20, 20] });

                const center = bounds.getCenter();
                marker.setLatLng(center);
                updateInputs(center.lat, center.lng);
            };

            window.onMitraRegionCleared = function() {
                if (regionPolygonLayer) {
                    map.removeLayer(regionPolygonLayer);
                    regionPolygonLayer = null;
                }
            };

            // Check if a region is pre-selected on load (e.g. after validation error / edit)
            const activeRegions = @js($activeRegions);
            const preselectedId = @js(old('region_id', $partnerProfile?->region_id ?? ''));

            if (preselectedId && activeRegions && activeRegions.length > 0) {
                const preselected = activeRegions.find(r => r.id == preselectedId);
                if (preselected) {
                    window.onMitraRegionSelected(preselected);
                }
            } else if (activeRegions && activeRegions.length === 1) {
                // If there's only 1 active region, auto select it for convenience
                const auto = activeRegions[0];
                document.getElementById('region_id').value = auto.id;
                window.onMitraRegionSelected(auto);
            }
        });
    </script>
</x-pwa-layout>
