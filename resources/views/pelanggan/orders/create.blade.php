<x-pwa-layout title="Konfirmasi Pesanan - Pijat.In" headerTitle="Pesan Layanan" :backUrl="route('pelanggan.catalog.show', $package->service->slug)">
    <div class="py-2 space-y-4"
         x-data="{
            selectedPackageId: {{ old('package_id', $package->id) }},
            packages: @js($siblingPackages),
            recipientType: '{{ old('recipient_type', 'myself') }}',
            recipientGender: '{{ old('recipient_gender', $user->gender ?? 'pria') }}',
            orderType: '{{ old('order_type', 'terjadwal') }}',
            scheduledDate: '{{ old('scheduled_date', $minScheduleDate->format('Y-m-d')) }}',
            scheduledTime: '{{ old('scheduled_time', $minScheduleDate->format('H:i')) }}',
            minDate: '{{ $minScheduleDate->format('Y-m-d') }}',
            maxDate: '{{ $maxScheduleDate->format('Y-m-d') }}',
            showRiskModal: false,
            
            get currentPackage() {
                return this.packages.find(p => p.id == this.selectedPackageId) || this.packages[0];
            },
            get estimatedEndTime() {
                if (this.orderType !== 'terjadwal' || !this.scheduledTime) return '';
                const parts = this.scheduledTime.split(':');
                if (parts.length < 2) return '';
                let h = parseInt(parts[0], 10);
                let m = parseInt(parts[1], 10);
                const duration = this.currentPackage ? this.currentPackage.duration_minutes : 60;
                m += duration;
                h += Math.floor(m / 60);
                m = m % 60;
                const hh = String(h).padStart(2, '0');
                const mm = String(m).padStart(2, '0');
                return hh + ':' + mm;
            },
            get isEndTimeOverLimit() {
                const end = this.estimatedEndTime;
                return end && end > '21:00';
            }
         }">

        <div>
            <h2 class="text-base font-extrabold text-slate-900 leading-tight">Konfirmasi Detail Pesanan</h2>
            <p class="text-xs text-slate-500">Lengkapi data penerima, waktu layanan, dan alamat kunjungan.</p>
        </div>

        <form action="{{ route('pelanggan.orders.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="package_id" :value="selectedPackageId">

            <!-- Card 1: Ringkasan Paket & Durasi (DEC-01 Flat Tariff) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Layanan Pilihan</span>
                        <h3 class="text-sm font-extrabold text-slate-900">{{ $package->service->name }}</h3>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        Tarif Bersih
                    </span>
                </div>

                <!-- Durasi Switcher -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Durasi Sesi:</label>
                    <div class="grid grid-cols-{{ count($siblingPackages) > 2 ? '3' : '2' }} gap-2">
                        @foreach ($siblingPackages as $pkg)
                            <button type="button" @click="selectedPackageId = {{ $pkg->id }}"
                                    class="min-h-[44px] p-2.5 rounded-xl border text-center transition cursor-pointer"
                                    :class="selectedPackageId == {{ $pkg->id }} ? 'bg-emerald-50 border-emerald-600 ring-1 ring-emerald-600 text-emerald-950 font-bold' : 'bg-slate-50 border-slate-200 text-slate-700 hover:border-slate-300'">
                                <div class="text-xs font-bold leading-tight">{{ $pkg->duration_minutes }} Menit</div>
                                <div class="text-[11px] text-emerald-700 font-mono font-extrabold mt-0.5">{{ $pkg->formatted_price }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between text-xs">
                    <span class="text-slate-600 font-medium">Total Tarif Layanan:</span>
                    <span class="text-sm font-black text-slate-900 font-mono" x-text="'Rp ' + (currentPackage ? Number(currentPackage.price).toLocaleString('id-ID') : '0')"></span>
                </div>
            </div>

            <!-- Card 2: Alamat Layanan (CUS-05) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-900">
                        Alamat Kunjungan Terapis <span class="text-rose-500">*</span>
                    </label>
                    <a href="{{ route('pelanggan.addresses.create') }}" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800">
                        + Tambah Alamat
                    </a>
                </div>

                <div class="space-y-2">
                    @foreach ($addresses as $addr)
                        <label class="block p-3 rounded-xl border cursor-pointer transition"
                               :class="'{{ old('customer_address_id', $defaultAddress->id) }}' == '{{ $addr->id }}' ? 'bg-emerald-50/70 border-emerald-600 ring-1 ring-emerald-600' : 'bg-slate-50/50 border-slate-200 hover:border-slate-300'">
                            <div class="flex items-start space-x-3">
                                <input type="radio" name="customer_address_id" value="{{ $addr->id }}"
                                    {{ old('customer_address_id', $defaultAddress->id) == $addr->id ? 'checked' : '' }}
                                    class="mt-1 text-emerald-700 focus:ring-emerald-600 border-slate-300">
                                <div class="flex-1 text-xs">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-extrabold text-slate-900">{{ $addr->label }}</span>
                                        @if ($addr->is_primary)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Utama</span>
                                        @endif
                                        <span class="text-[11px] text-slate-500 font-medium">({{ $addr->region->name ?? 'Wilayah' }})</span>
                                    </div>
                                    <p class="text-slate-600 mt-1 leading-relaxed">{{ $addr->address_text }}</p>
                                    @if ($addr->benchmark)
                                        <p class="text-slate-500 text-[11px] mt-0.5">Patokan: {{ $addr->benchmark }}</p>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('customer_address_id') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
            </div>

            <!-- Card 3: Penerima Layanan (CUS-04 Gender Lock) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3">
                <div>
                    <h3 class="text-xs font-bold text-slate-900">Penerima Layanan</h3>
                    <p class="text-xs text-slate-500">Tentukan siapa yang akan menerima sesi pijat ini.</p>
                </div>

                <!-- Toggle Segment: Diri Sendiri vs Orang Lain -->
                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                    <button type="button" @click="recipientType = 'myself'"
                            class="min-h-[44px] py-2 rounded-lg text-xs font-bold transition cursor-pointer flex items-center justify-center space-x-1.5"
                            :class="recipientType === 'myself' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Untuk Diri Sendiri</span>
                    </button>
                    <button type="button" @click="recipientType = 'other'"
                            class="min-h-[44px] py-2 rounded-lg text-xs font-bold transition cursor-pointer flex items-center justify-center space-x-1.5"
                            :class="recipientType === 'other' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Untuk Orang Lain</span>
                    </button>
                </div>
                <input type="hidden" name="recipient_type" :value="recipientType">

                <!-- Mode: Diri Sendiri -->
                <div x-show="recipientType === 'myself'" class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/90 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Nama Lengkap:</span>
                        <span class="font-bold text-slate-900">{{ $user->name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Nomor Telepon/WA:</span>
                        <span class="font-bold text-slate-900 font-mono">{{ $user->phone ?? 'Belum ada' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Jenis Kelamin:</span>
                        <span class="font-bold text-slate-900 capitalize">{{ $user->gender ?? 'Belum ditentukan' }}</span>
                    </div>

                    <!-- Gender Lock Notice -->
                    <div class="mt-2 p-2.5 bg-emerald-50 rounded-lg border border-emerald-200 text-emerald-900 text-[11px] leading-relaxed">
                        <strong>Aturan Sesama Gender:</strong> Mitra yang ditugaskan dijamin berjenis kelamin <strong>{{ $user->gender === 'wanita' ? 'Wanita' : 'Pria' }}</strong> sesuai profil akun Anda.
                    </div>
                </div>

                <!-- Mode: Orang Lain -->
                <div x-show="recipientType === 'other'" class="space-y-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Nama Lengkap Penerima <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name') }}"
                            placeholder="Contoh: Budi Santoso, Ibu Ratna..."
                            class="w-full min-h-[44px] text-xs rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 py-2.5">
                        @error('recipient_name') <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Nomor WhatsApp / HP Penerima <span class="text-rose-500">*</span>
                        </label>
                        <input type="tel" name="recipient_phone" value="{{ old('recipient_phone') }}"
                            placeholder="Contoh: 081234567890"
                            class="w-full min-h-[44px] text-xs rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 py-2.5 font-mono">
                        @error('recipient_phone') <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Jenis Kelamin Penerima <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="min-h-[44px] p-2.5 rounded-xl border flex items-center justify-center space-x-2 cursor-pointer transition"
                                   :class="recipientGender === 'pria' ? 'bg-emerald-50 border-emerald-600 text-emerald-950 font-bold' : 'bg-slate-50 border-slate-200 text-slate-700'">
                                <input type="radio" name="recipient_gender" value="pria" x-model="recipientGender" class="text-emerald-700 focus:ring-emerald-600">
                                <span class="text-xs">Laki-laki (Pria)</span>
                            </label>
                            <label class="min-h-[44px] p-2.5 rounded-xl border flex items-center justify-center space-x-2 cursor-pointer transition"
                                   :class="recipientGender === 'wanita' ? 'bg-emerald-50 border-emerald-600 text-emerald-950 font-bold' : 'bg-slate-50 border-slate-200 text-slate-700'">
                                <input type="radio" name="recipient_gender" value="wanita" x-model="recipientGender" class="text-emerald-700 focus:ring-emerald-600">
                                <span class="text-xs">Perempuan (Wanita)</span>
                            </label>
                        </div>
                        @error('recipient_gender') <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p> @enderror

                        <div class="mt-2 p-2.5 bg-amber-50 rounded-lg border border-amber-200 text-amber-950 text-[11px] leading-relaxed">
                            <strong>Kunci Penugasan Mitra:</strong> Pilihan jenis kelamin ini mengunci mitra terapis yang ditugaskan agar selalu sesama gender.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Waktu Layanan (CUS-06 Operational Hours 08:00 - 21:00) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3">
                <div>
                    <h3 class="text-xs font-bold text-slate-900">Jadwal Layanan</h3>
                    <p class="text-xs text-slate-500">Pilih pemesanan instan atau jadwalkan di muka.</p>
                </div>

                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                    <button type="button" @click="orderType = 'terjadwal'"
                            class="min-h-[44px] py-2 rounded-lg text-xs font-bold transition cursor-pointer flex items-center justify-center space-x-1.5"
                            :class="orderType === 'terjadwal' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Terjadwal</span>
                    </button>
                    <button type="button" @click="orderType = 'instan'"
                            class="min-h-[44px] py-2 rounded-lg text-xs font-bold transition cursor-pointer flex items-center justify-center space-x-1.5"
                            :class="orderType === 'instan' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>Sekarang (Instan)</span>
                    </button>
                </div>
                <input type="hidden" name="order_type" :value="orderType">

                <!-- Mode: Terjadwal -->
                <div x-show="orderType === 'terjadwal'" class="space-y-3 pt-1">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Tanggal Layanan <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="scheduled_date" x-model="scheduledDate"
                                :min="minDate" :max="maxDate"
                                class="w-full min-h-[44px] text-xs rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Jam Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="scheduled_time" x-model="scheduledTime"
                                min="08:00" max="20:00" step="900"
                                class="w-full min-h-[44px] text-xs rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 py-2">
                        </div>
                    </div>

                    <!-- Operational info & End time calculation -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/90 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Jam Operasional:</span>
                            <span class="font-bold text-slate-900">08:00 - 21:00 ({{ $regionTz === 'Asia/Jakarta' ? 'WIB' : 'WITA' }})</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Ketentuan Penjadwalan:</span>
                            <span class="font-medium text-slate-800">Min. 3 jam s/d 7 hari</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600 pt-1 border-t border-slate-200/60">
                            <span>Estimasi Selesai:</span>
                            <span class="font-bold font-mono" :class="isEndTimeOverLimit ? 'text-rose-600' : 'text-emerald-800'" x-text="estimatedEndTime ? estimatedEndTime + ' {{ $regionTz === 'Asia/Jakarta' ? 'WIB' : 'WITA' }}' : '—'"></span>
                        </div>
                    </div>

                    <div x-show="isEndTimeOverLimit" class="p-2.5 bg-rose-50 rounded-xl border border-rose-200 text-rose-700 text-xs font-semibold">
                        Peringatan: Estimasi waktu selesai melebihi batas jam operasional 21:00. Mohon pilih jam mulai yang lebih awal.
                    </div>

                    @error('scheduled_time') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Mode: Instan -->
                <div x-show="orderType === 'instan'" class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-950 space-y-1 leading-relaxed">
                    <p class="font-bold">Pencarian Mitra Instan:</p>
                    <p class="text-[11px] text-emerald-900">Sistem akan segera mencarikan mitra terapis terdekat yang tersedia di sekitar lokasi Anda setelah pembayaran diselesaikan.</p>
                </div>
            </div>

            <!-- Card 5: Catatan Medis Terenkripsi & Persetujuan Risiko (CUS-07 & SYS-08) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3.5">
                <div>
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-900">
                            Catatan Kondisi Medis
                        </label>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                            Opsional
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Informasikan keluhan khusus atau area tubuh yang memerlukan perhatian.</p>
                </div>

                <div>
                    <textarea name="medical_notes" rows="3"
                        placeholder="Contoh: Riwayat cedera bahu kanan, hindari tekanan kuat pada tulang leher, memiliki tekanan darah tinggi ringan..."
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 p-3">{{ old('medical_notes') }}</textarea>
                    
                    <div class="mt-2 flex items-center space-x-2 text-[11px] text-slate-500">
                        <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Tersimpan dengan enkripsi di database; hanya dapat dilihat oleh mitra setelah pesanan disetujui.</span>
                    </div>
                    @error('medical_notes') <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Checkbox Persetujuan Risiko (SYS-08 v1.0) -->
                <div class="pt-2 border-t border-slate-100">
                    <label class="flex items-start space-x-3 p-3 rounded-xl border border-slate-200 hover:border-emerald-300 transition cursor-pointer select-none bg-slate-50/60">
                        <input type="checkbox" name="risk_agreement" value="1" required {{ old('risk_agreement') ? 'checked' : '' }}
                            class="mt-0.5 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                        <div class="text-xs leading-relaxed text-slate-700">
                            <span>Saya telah membaca dan menyetujui</span>
                            <button type="button" @click.prevent="showRiskModal = true" class="text-emerald-700 font-bold underline cursor-pointer">
                                Pernyataan Risiko Layanan Pijat.In (v1.0)
                            </button>
                            <span class="text-rose-500">*</span>
                        </div>
                    </label>
                    @error('risk_agreement') <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Modal SOP / Persetujuan Risiko -->
            <div x-show="showRiskModal" style="display: none;"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div @click.away="showRiskModal = false" class="bg-white rounded-2xl max-w-sm w-full p-5 space-y-4 shadow-xl border border-slate-200">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h4 class="text-sm font-extrabold text-slate-900">Ketentuan Risiko Layanan (v1.0)</h4>
                        <button type="button" @click="showRiskModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                            ✕
                        </button>
                    </div>
                    <div class="text-xs text-slate-600 space-y-2.5 max-h-60 overflow-y-auto pr-1 leading-relaxed">
                        <p>1. <strong>Kondisi Kesehatan:</strong> Pelanggan menyatakan bahwa penerima layanan tidak sedang menderita demam tinggi, infeksi menular akut, patah tulang, atau pasca-operasi berat tanpa izin medis.</p>
                        <p>2. <strong>Kehamilan:</strong> Untuk penerima layanan wanita dalam kondisi hamil, wajib menginformasikan kepada terapis agar titik-titik kontraindikasi dapat dihindari sepenuhnya.</p>
                        <p>3. <strong>SOP Profesional:</strong> Layanan pijat Pijat.In berorientasi murni pada kebugaran dan relaksasi kesehatan. Segala bentuk pelanggaran etika dan asusila dilarang keras dan akan dilaporkan ke pihak berwajib.</p>
                        <p>4. <strong>Persetujuan Waktu:</strong> Waktu konfirmasi dan versi dokumen ini dicatat secara digital dalam log audit sistem demi kepastian hukum kedua belah pihak.</p>
                    </div>
                    <button type="button" @click="showRiskModal = false"
                        class="w-full min-h-[44px] py-2.5 rounded-xl bg-emerald-700 text-white font-bold text-xs hover:bg-emerald-800 transition">
                        Saya Paham & Setuju
                    </button>
                </div>
            </div>

            <!-- Submit CTA Button -->
            <div class="pt-2">
                <button type="submit" :disabled="isEndTimeOverLimit"
                    class="w-full min-h-[48px] py-3.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Lanjutkan Konfirmasi Pesanan</span>
                </button>
            </div>

        </form>
    </div>
</x-pwa-layout>

