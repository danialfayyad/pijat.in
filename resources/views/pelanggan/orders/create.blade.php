<x-pwa-layout title="Konfirmasi Pesanan - Pijat.In" headerTitle="Pesan Layanan" :backUrl="route('pelanggan.catalog.show', $package->service->slug)">
    <div class="py-2 space-y-4"
         x-data="{
            selectedPackageId: {{ old('package_id', $package->id) }},
            selectedAddressId: {{ old('customer_address_id', $defaultAddress->id) }},
            packages: @js($siblingPackages),
            recipientType: '{{ old('recipient_type', 'myself') }}',
            recipientGender: '{{ old('recipient_gender', $user->gender ?? 'pria') }}',
            preferredGender: '{{ old('preferred_gender', 'bebas') }}',
            orderType: '{{ old('order_type', 'terjadwal') }}',
            todayDate: '{{ $todayDate }}',
            minDate: '{{ $minScheduleDate->format('Y-m-d') }}',
            maxDate: '{{ $maxScheduleDate->format('Y-m-d') }}',
            minTimeToday: '{{ $minTimeToday }}',
            isTodayBookable: {{ $isTodayBookable ? 'true' : 'false' }},
            scheduledDate: '{{ old('scheduled_date', $defaultScheduleDate) }}',
            scheduledTime: '{{ old('scheduled_time', $defaultScheduleTime) }}',
            showRiskModal: false,
            showInstantUnavailableModal: false,
            checkingInstant: false,
            instantChecked: false,
            instantAvailable: true,

            selectPackage(id) {
                this.selectedPackageId = id;
                this.adjustTimeIfNeeded();
            },

            async selectOrderType(type) {
                this.orderType = type;
                if (type === 'instan') {
                    await this.checkInstantAvailability();
                }
            },

            async checkInstantAvailability() {
                this.checkingInstant = true;
                try {
                    const gender = this.recipientType === 'myself' ? '{{ $user->gender ?? 'pria' }}' : this.recipientGender;
                    const url = '{{ route('pelanggan.orders.check-instant') }}?address_id=' + this.selectedAddressId + '&preferred_gender=' + encodeURIComponent(this.preferredGender) + '&gender=' + encodeURIComponent(gender) + '&package_id=' + this.selectedPackageId;
                    const res = await fetch(url);
                    const data = await res.json();
                    this.instantAvailable = !!data.available;
                    this.instantChecked = true;
                    if (!this.instantAvailable) {
                        this.showInstantUnavailableModal = true;
                    }
                } catch (e) {
                    console.error('Gagal mengecek ketersediaan mitra instan:', e);
                } finally {
                    this.checkingInstant = false;
                }
            },
            
            get currentPackage() {
                return this.packages.find(p => p.id == this.selectedPackageId) || this.packages[0];
            },

            get minTimeAllowed() {
                if (this.scheduledDate === this.todayDate) {
                    return this.minTimeToday;
                }
                return '08:00';
            },

            get maxTimeAllowed() {
                const duration = this.currentPackage ? this.currentPackage.duration_minutes : 60;
                const totalMinutes = (21 * 60) - duration;
                const h = Math.floor(totalMinutes / 60);
                const m = totalMinutes % 60;
                return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
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

            get isStartTimeTooEarly() {
                if (this.orderType !== 'terjadwal' || !this.scheduledTime) return false;
                return this.scheduledTime < this.minTimeAllowed;
            },

            get isStartTimeTooLate() {
                if (this.orderType !== 'terjadwal' || !this.scheduledTime) return false;
                return this.scheduledTime > this.maxTimeAllowed;
            },

            get isEndTimeOverLimit() {
                const end = this.estimatedEndTime;
                return end && end > '21:00';
            },

            get isTimeInvalid() {
                return this.isStartTimeTooEarly || this.isStartTimeTooLate || this.isEndTimeOverLimit;
            },

            handleDateChange() {
                if (this.scheduledDate === this.todayDate && !this.isTodayBookable) {
                    this.scheduledDate = this.minDate;
                }
                this.adjustTimeIfNeeded();
            },

            adjustTimeIfNeeded() {
                if (this.scheduledTime < this.minTimeAllowed) {
                    this.scheduledTime = this.minTimeAllowed;
                } else if (this.scheduledTime > this.maxTimeAllowed) {
                    this.scheduledTime = this.maxTimeAllowed;
                }
            }
         }">

        <div>
            <h2 class="text-base font-extrabold text-slate-900 leading-tight">Konfirmasi Detail Pesanan</h2>
            <p class="text-xs text-slate-500">Lengkapi data penerima, waktu layanan, dan alamat kunjungan.</p>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs shadow-xs space-y-1.5">
                <div class="flex items-center space-x-2 font-bold text-rose-800">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Mohon periksa kembali formulir Anda:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-700 pl-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
                            <button type="button" @click="selectPackage({{ $pkg->id }})"
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
                    @error('recipient_gender')
                        <p class="text-xs text-rose-600 font-semibold pt-1">{{ $message }}</p>
                    @enderror
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
                    </div>
                </div>

                <!-- Preferensi Gender Terapis (Opsi A) -->
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-900">
                            Preferensi Gender Terapis <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-[11px] text-slate-500">Pilih jenis kelamin mitra terapis yang Anda inginkan.</p>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <!-- Bebas / Siapa Saja -->
                        <label class="min-h-[44px] p-2.5 rounded-xl border flex flex-col items-center justify-center text-center cursor-pointer transition"
                               :class="preferredGender === 'bebas' ? 'bg-emerald-50 border-emerald-600 ring-1 ring-emerald-600 text-emerald-950 font-bold' : 'bg-slate-50 border-slate-200 text-slate-700 hover:border-slate-300'">
                            <input type="radio" name="preferred_gender" value="bebas" x-model="preferredGender" @change="if(orderType === 'instan') checkInstantAvailability()" class="sr-only">
                            <span class="text-xs font-bold">Bebas</span>
                            <span class="text-[10px] text-emerald-700 font-semibold mt-0.5">Siapa Saja</span>
                        </label>
                        <!-- Pria -->
                        <label class="min-h-[44px] p-2.5 rounded-xl border flex flex-col items-center justify-center text-center cursor-pointer transition"
                               :class="preferredGender === 'pria' ? 'bg-emerald-50 border-emerald-600 ring-1 ring-emerald-600 text-emerald-950 font-bold' : 'bg-slate-50 border-slate-200 text-slate-700 hover:border-slate-300'">
                            <input type="radio" name="preferred_gender" value="pria" x-model="preferredGender" @change="if(orderType === 'instan') checkInstantAvailability()" class="sr-only">
                            <span class="text-xs font-bold">Pria</span>
                            <span class="text-[10px] text-slate-500 mt-0.5">Terapis Pria</span>
                        </label>
                        <!-- Wanita -->
                        <label class="min-h-[44px] p-2.5 rounded-xl border flex flex-col items-center justify-center text-center cursor-pointer transition"
                               :class="preferredGender === 'wanita' ? 'bg-emerald-50 border-emerald-600 ring-1 ring-emerald-600 text-emerald-950 font-bold' : 'bg-slate-50 border-slate-200 text-slate-700 hover:border-slate-300'">
                            <input type="radio" name="preferred_gender" value="wanita" x-model="preferredGender" @change="if(orderType === 'instan') checkInstantAvailability()" class="sr-only">
                            <span class="text-xs font-bold">Wanita</span>
                            <span class="text-[10px] text-slate-500 mt-0.5">Terapis Wanita</span>
                        </label>
                    </div>
                    @error('preferred_gender') <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Card 4: Waktu Layanan (CUS-06 Operational Hours 08:00 - 21:00) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3">
                <div>
                    <h3 class="text-xs font-bold text-slate-900">Jadwal Layanan</h3>
                    <p class="text-xs text-slate-500">Pilih pemesanan instan atau jadwalkan di muka.</p>
                </div>

                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                    <button type="button" @click="selectOrderType('terjadwal')"
                            class="min-h-[44px] py-2 rounded-lg text-xs font-bold transition cursor-pointer flex items-center justify-center space-x-1.5"
                            :class="orderType === 'terjadwal' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Terjadwal</span>
                    </button>
                    <button type="button" @click="selectOrderType('instan')"
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
                                @change="handleDateChange()"
                                :min="minDate" :max="maxDate"
                                class="w-full min-h-[44px] text-xs rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Jam Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="time" name="scheduled_time" x-model="scheduledTime"
                                @change="adjustTimeIfNeeded()"
                                :min="minTimeAllowed" :max="maxTimeAllowed" step="900"
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
                            <span class="font-medium text-slate-800">Min. 3 jam dari sekarang (Maks. 7 hari)</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Rentang Jam Mulai:</span>
                            <span class="font-bold font-mono text-emerald-800" x-text="minTimeAllowed + ' - ' + maxTimeAllowed + ' ({{ $regionTz === 'Asia/Jakarta' ? 'WIB' : 'WITA' }})'"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600 pt-1 border-t border-slate-200/60">
                            <span>Estimasi Selesai:</span>
                            <span class="font-bold font-mono" :class="isEndTimeOverLimit ? 'text-rose-600' : 'text-emerald-800'" x-text="estimatedEndTime ? estimatedEndTime + ' {{ $regionTz === 'Asia/Jakarta' ? 'WIB' : 'WITA' }}' : '—'"></span>
                        </div>
                    </div>

                    <div x-show="isStartTimeTooEarly" class="p-2.5 bg-rose-50 rounded-xl border border-rose-200 text-rose-700 text-xs font-semibold">
                        Peringatan: Pemesanan terjadwal minimal 3 jam dari sekarang. Jam mulai paling awal untuk tanggal ini adalah pk. <span x-text="minTimeAllowed"></span>.
                    </div>

                    <div x-show="isStartTimeTooLate || isEndTimeOverLimit" class="p-2.5 bg-rose-50 rounded-xl border border-rose-200 text-rose-700 text-xs font-semibold">
                        Peringatan: Estimasi waktu selesai melebihi batas jam operasional 21:00. Jam mulai paling lambat untuk paket <span x-text="currentPackage ? currentPackage.duration_minutes : 60"></span> menit adalah pk. <span x-text="maxTimeAllowed"></span>.
                    </div>

                    @error('scheduled_time') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Mode: Instan (CUS-08) -->
                <div x-show="orderType === 'instan'" class="space-y-2 pt-1">
                    <!-- Loading check -->
                    <div x-show="checkingInstant" class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center space-x-2.5 text-xs text-slate-600">
                        <svg class="w-4 h-4 text-emerald-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Mengecek ketersediaan mitra terapis online terdekat...</span>
                    </div>

                    <!-- Available Notice -->
                    <div x-show="!checkingInstant && instantChecked && instantAvailable" class="p-3.5 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-950 space-y-1">
                        <div class="flex items-center space-x-1.5 font-bold text-emerald-900">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Mitra Terapis Tersedia</span>
                        </div>
                        <p class="text-[11px] text-emerald-800 leading-relaxed">
                            Mitra terapis online yang sesuai preferensi Anda (radius 5 km) siap menerima pesanan. Sistem segera menghubungkan pesanan setelah pembayaran terkonfirmasi.
                        </p>
                    </div>

                    <!-- Unavailable Warning Box -->
                    <div x-show="!checkingInstant && instantChecked && !instantAvailable" class="p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-950 space-y-2">
                        <div class="flex items-center space-x-1.5 font-bold text-amber-900">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Mitra Online Tidak Ditemukan</span>
                        </div>
                        <p class="text-[11px] text-amber-800 leading-relaxed">
                            Saat ini belum ada mitra terapis online dalam radius 5 km yang sesuai. Anda disarankan beralih ke jadwal Terjadwal.
                        </p>
                        <button type="button" @click="selectOrderType('terjadwal')" class="min-h-[40px] px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-bold text-xs transition">
                            Beralih ke Terjadwal
                        </button>
                    </div>

                    <!-- Fallback if not checked yet -->
                    <div x-show="!checkingInstant && !instantChecked" class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-950 space-y-1 leading-relaxed">
                        <p class="font-bold">Pencarian Mitra Instan:</p>
                        <p class="text-[11px] text-emerald-900">Sistem akan segera mencarikan mitra terapis terdekat yang tersedia di sekitar lokasi Anda setelah pembayaran diselesaikan.</p>
                    </div>

                    @error('order_type') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
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

            <!-- Modal Informasi Mitra Instan Belum Tersedia (CUS-08) -->
            <div x-show="showInstantUnavailableModal" style="display: none;"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div @click.away="showInstantUnavailableModal = false" class="bg-white rounded-2xl max-w-sm w-full p-5 space-y-4 shadow-xl border border-slate-200 text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-900">Mitra Online Belum Tersedia</h4>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Saat ini belum ada mitra terapis online yang sesuai preferensi Anda (radius 5 km) di sekitar wilayah ini. Anda dapat beralih ke jadwal Terjadwal.
                        </p>
                    </div>
                    <div class="space-y-2 pt-1">
                        <button type="button" @click="selectOrderType('terjadwal'); showInstantUnavailableModal = false"
                            class="w-full min-h-[44px] py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition">
                            Beralih ke Terjadwal
                        </button>
                        <button type="button" @click="showInstantUnavailableModal = false"
                            class="w-full min-h-[44px] py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submit CTA Button -->
            <div class="pt-2">
                <button type="submit" :disabled="orderType === 'terjadwal' && isTimeInvalid"
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

