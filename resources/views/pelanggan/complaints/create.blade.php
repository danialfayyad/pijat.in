<x-pwa-layout title="Ajukan Aduan Layanan - Pijat.In" headerTitle="Aduan Layanan" :backUrl="route('pelanggan.orders.show', $order)">
    <div class="py-2 space-y-4">
        <!-- Order Context Card -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">Kode Pesanan</span>
                <span class="text-xs font-extrabold text-slate-900 font-mono">{{ $order->order_code }}</span>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-600 font-medium">{{ $order->service_name }} ({{ $order->duration_minutes }} Menit)</span>
                <span class="font-bold text-slate-900 font-mono">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
            @if ($order->mitra)
                <div class="flex items-center space-x-2 text-[11px] text-slate-500 pt-1">
                    <span>Terapis:</span>
                    <strong class="text-slate-800">{{ $order->mitra->name }}</strong>
                </div>
            @endif
        </div>

        <!-- Warning / Info Box -->
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-3.5 space-y-1.5 text-xs text-rose-950">
            <div class="flex items-center space-x-1.5 font-bold text-rose-900">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Kebijakan Pengajuan Aduan (Maks. 24 Jam)</span>
            </div>
            <p class="text-[11px] text-rose-800 leading-relaxed">
                Aduan hanya dapat diproses bila diajukan dalam jendela 24 jam sejak layanan selesai. Laporan Anda akan diperiksa langsung oleh tim investigasi Admin Wilayah.
            </p>
        </div>

        <!-- Complaint Form -->
        <form method="POST" action="{{ route('pelanggan.complaints.store', $order) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Category -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-2">
                <label for="complaint_category_id" class="block text-xs font-bold text-slate-800">
                    Kategori Masalah <span class="text-rose-600">*</span>
                </label>
                <select name="complaint_category_id" id="complaint_category_id" required
                    class="w-full rounded-xl border-slate-200 text-xs px-3 py-2.5 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">-- Pilih Kategori Kendala --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('complaint_category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('complaint_category_id')
                    <p class="text-[11px] text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Title & Description -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-3">
                <div>
                    <label for="title" class="block text-xs font-bold text-slate-800 mb-1">
                        Judul Ringkasan <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" name="title" id="title" required maxlength="100"
                        value="{{ old('title') }}"
                        placeholder="Contoh: Terapis datang terlambat dan tidak menyelesaikan durasi"
                        class="w-full rounded-xl border-slate-200 text-xs px-3 py-2.5 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('title')
                        <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-xs font-bold text-slate-800 mb-1">
                        Penjelasan Lengkap Kejadian <span class="text-rose-600">*</span>
                    </label>
                    <textarea name="description" id="description" rows="4" required minlength="10" maxlength="1000"
                        placeholder="Jelaskan secara kronologis ketidaksesuaian atau kendala yang Anda alami selama sesi pemijatan..."
                        class="w-full rounded-xl border-slate-200 text-xs p-3 focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
                    <span class="text-[10px] text-slate-400">Minimal 10 karakter, maksimal 1.000 karakter.</span>
                    @error('description')
                        <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Evidence Photos Upload dengan Kompresi Otomatis -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs space-y-2">
                <x-image-upload-compressor 
                    name="evidence_photos[]" 
                    label="Unggah Bukti Foto Pendukung (Opsional)" 
                    :multiple="true" 
                    :maxFiles="3" 
                    helpText="Maksimal 3 foto (format JPG/PNG/WEBP). Foto otomatis dikompresi agar ukuran di bawah 2MB." />
                @error('evidence_photos')
                    <p class="text-[11px] text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
                @error('evidence_photos.*')
                    <p class="text-[11px] text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full min-h-[46px] py-3 px-4 bg-rose-600 hover:bg-rose-700 active:scale-[0.99] text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                <span>Kirimkan Aduan ke Admin Wilayah</span>
            </button>
        </form>
    </div>
</x-pwa-layout>

