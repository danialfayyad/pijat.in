<x-pwa-layout title="Laporkan Kendala / Insiden - Pijat.In" headerTitle="Lapor Kendala" :backUrl="route('mitra.orders.show', $order)">
    <div class="py-2 space-y-4">
        <!-- Order Header Card -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor Pesanan</span>
                <span class="font-mono font-bold text-xs text-slate-800">{{ $order->order_code }}</span>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <div>
                    <span class="text-slate-500 block text-[11px]">Pelanggan / Penerima:</span>
                    <span class="font-bold text-slate-900">{{ $order->recipient_name ?? $order->customer?->name }}</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-500 block text-[11px]">Layanan:</span>
                    <span class="font-bold text-slate-900">{{ $order->service?->name }}</span>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('mitra.orders.incident.store', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-4"
              x-data="{ isEmergency: false }">
            @csrf

            <!-- Emergency Checkbox Banner -->
            <div class="p-4 rounded-2xl border transition"
                 :class="isEmergency ? 'bg-rose-50 border-rose-300' : 'bg-slate-50 border-slate-200'">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_emergency" value="1" x-model="isEmergency"
                           class="w-4 h-4 mt-0.5 rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                    <div class="text-xs">
                        <span class="font-bold block" :class="isEmergency ? 'text-rose-900' : 'text-slate-900'">
                            Tandai sebagai Keadaan Darurat / Insiden Kritis (MIT-19)
                        </span>
                        <p class="text-[11px] mt-0.5 leading-relaxed" :class="isEmergency ? 'text-rose-700' : 'text-slate-500'">
                            Centang opsi ini jika Anda mengalami ancaman keselamatan, pelecehan, intimidasi fisik, atau kondisi darurat medis di lokasi pesanan. Laporan akan dinaikkan ke prioritas darurat untuk intervensi langsung Admin Wilayah.
                        </p>
                    </div>
                </label>
            </div>

            <!-- Form Content -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-slate-900 border-b border-slate-100 pb-2">Detail Laporan Kendala</h3>

                <!-- Kategori Kendala -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Kendala</label>
                    <select name="complaint_category_id"
                        class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">
                        <option value="">-- Pilih Kategori (Opsional) --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('complaint_category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Judul Ringkasan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Judul Ringkasan Kendala <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required minlength="5" maxlength="100"
                        placeholder="Contoh: Pelanggan tidak dapat dihubungi atau minta tarif tambahan"
                        class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">
                </div>

                <!-- Deskripsi Kronologi -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Kronologi / Deskripsi Kejadian <span class="text-rose-600">*</span>
                    </label>
                    <textarea name="description" rows="4" required minlength="10" maxlength="1000"
                        placeholder="Jelaskan secara rinci apa yang terjadi di lokasi atau selama pelayanan..."
                        class="w-full p-3.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition">{{ old('description') }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Minimal 10 karakter, maksimal 1000 karakter.</p>
                </div>

                <!-- Bukti Foto dengan Kompresi Otomatis -->
                <x-image-upload-compressor 
                    name="evidence_photos[]" 
                    label="Foto Bukti Lapangan (Opsional)" 
                    :multiple="true" 
                    :maxFiles="3" 
                    helpText="Maksimal 3 foto (format JPG/PNG/WEBP). Foto otomatis dikompresi agar ukuran di bawah 2MB." />
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit"
                    class="w-full min-h-[48px] py-3.5 font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer text-white"
                    :class="isEmergency ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-700 hover:bg-emerald-800'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span x-text="isEmergency ? 'Kirim Peringatan Darurat ke Admin Wilayah' : 'Kirim Laporan Kendala'"></span>
                </button>
            </div>
        </form>
    </div>
</x-pwa-layout>

