@php
    $isFromCheckout = request('from') === 'checkout' && request()->filled('package_id');
    $backUrl = $isFromCheckout 
        ? route('pelanggan.orders.create', ['package_id' => request('package_id')])
        : route('profile.edit');
    $createUrl = route('pelanggan.addresses.create', $isFromCheckout ? ['from' => 'checkout', 'package_id' => request('package_id')] : []);
@endphp

<x-pwa-layout 
    :title="$isFromCheckout ? 'Pilih Alamat - Pijat.In' : 'Daftar Alamat Tersimpan - Pijat.In'" 
    :headerTitle="$isFromCheckout ? 'Pilih Alamat' : 'Daftar Alamat Tersimpan'" 
    :backUrl="$backUrl" 
    :hideDock="$isFromCheckout">

    <div class="space-y-3 pt-2 pb-24">

        <!-- Status Alerts -->
        @if (session('status'))
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-medium flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-medium flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Addresses List -->
        @if ($addresses->isEmpty())
            <div class="text-center py-12 px-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs space-y-3 my-2">
                <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-700 mx-auto flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Belum Ada Alamat Tersimpan</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                        Simpan alamat penjemputan layanan pijat Anda.
                    </p>
                </div>
                <div class="pt-1">
                    <a href="{{ $createUrl }}"
                        class="inline-flex items-center min-h-[40px] px-5 py-2 rounded-xl bg-emerald-700 text-white text-xs font-bold hover:bg-emerald-800 active:scale-95 transition cursor-pointer">
                        <span>Tambah Alamat</span>
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($addresses as $addr)
                    <div class="bg-white rounded-2xl p-4 border transition {{ $addr->is_primary ? 'border-emerald-600 shadow-2xs ring-1 ring-emerald-600/30' : 'border-slate-200/90 shadow-2xs' }}">
                        
                        <!-- Header Baris Alamat: Label • Dot • Utama • Dot • Wilayah (Tanpa background box) -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                <span class="font-bold text-slate-900">{{ $addr->label }}</span>
                                @if ($addr->is_primary)
                                    <span class="text-emerald-500 font-bold">•</span>
                                    <span class="font-semibold text-emerald-700">Utama</span>
                                @endif
                                @if ($addr->region)
                                    <span class="text-slate-300">•</span>
                                    <span class="text-slate-500">{{ $addr->region->name }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Alamat Lengkap & Patokan -->
                        <div class="mt-2 text-xs text-slate-700 leading-relaxed">
                            {{ $addr->address_text }}
                        </div>

                        @if ($addr->benchmark)
                            <div class="mt-1 text-[11px] text-slate-400">
                                Patokan: {{ $addr->benchmark }}
                            </div>
                        @endif

                        <!-- Aksi Card -->
                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                            <div>
                                @if ($isFromCheckout)
                                    @if ($addr->is_primary)
                                        <a href="{{ route('pelanggan.orders.create', ['package_id' => request('package_id')]) }}"
                                           class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-800 transition cursor-pointer">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Gunakan Alamat Ini</span>
                                        </a>
                                    @else
                                        <form action="{{ route('pelanggan.addresses.set-primary', $addr) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="from" value="checkout">
                                            <input type="hidden" name="package_id" value="{{ request('package_id') }}">
                                            <button type="submit" 
                                                    class="text-xs font-bold text-emerald-700 hover:text-emerald-800 transition cursor-pointer">
                                                Pilih Alamat Ini
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    @if (! $addr->is_primary)
                                        <form id="set-primary-{{ $addr->id }}" action="{{ route('pelanggan.addresses.set-primary', $addr) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="button" 
                                                    onclick="confirmSetPrimary('set-primary-{{ $addr->id }}')"
                                                    class="text-xs font-bold text-emerald-700 hover:text-emerald-800 cursor-pointer">
                                                Jadikan Alamat Utama
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 font-medium">Alamat utama saat ini</span>
                                    @endif
                                @endif
                            </div>

                            <div class="flex items-center space-x-3">
                                <a href="{{ route('pelanggan.addresses.edit', $addr) }}" 
                                    class="text-xs font-medium text-slate-500 hover:text-slate-900 transition cursor-pointer">
                                    Ubah
                                </a>

                                <form id="delete-addr-{{ $addr->id }}" action="{{ route('pelanggan.addresses.destroy', $addr) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            onclick="confirmDeleteAddress('delete-addr-{{ $addr->id }}')"
                                            class="text-xs font-medium text-rose-600 hover:text-rose-800 transition cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                @endforeach

                <!-- Tombol Tambah Alamat Baru di Bawah Daftar Alamat -->
                <div class="pt-1">
                    <a href="{{ $createUrl }}"
                        class="w-full min-h-[46px] py-3 px-4 rounded-xl border border-dashed border-emerald-600/40 bg-emerald-50/50 hover:bg-emerald-50 text-emerald-800 text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                        <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah Alamat Baru</span>
                    </a>
                </div>
            </div>
        @endif

    </div>

    @push('scripts')
        <script>
            function confirmSetPrimary(formId) {
                const form = typeof formId === 'string' ? document.getElementById(formId) : formId;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        iconHtml: '✓',
                        title: 'Jadikan Alamat Utama?',
                        text: 'Alamat ini akan otomatis digunakan sebagai tujuan utama pemesanan.',
                        showCancelButton: true,
                        cancelButtonText: 'Batal',
                        confirmButtonText: 'Ya, Jadikan Utama',
                        reverseButtons: true,
                        buttonsStyling: false,
                        customClass: {
                            popup: 'swal-admin-theme',
                            confirmButton: 'swal2-confirm',
                            cancelButton: 'swal2-cancel',
                        }
                    }).then((result) => {
                        if (result.isConfirmed && form) {
                            form.submit();
                        }
                    });
                } else if (confirm('Alamat ini akan otomatis digunakan sebagai tujuan utama pemesanan. Lanjutkan?')) {
                    if (form) form.submit();
                }
            }

            function confirmDeleteAddress(formId) {
                const form = typeof formId === 'string' ? document.getElementById(formId) : formId;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        iconHtml: '✕',
                        title: 'Hapus Alamat?',
                        text: 'Apakah Anda yakin ingin menghapus alamat ini?',
                        showCancelButton: true,
                        cancelButtonText: 'Kembali',
                        confirmButtonText: 'Ya, Hapus',
                        reverseButtons: true,
                        buttonsStyling: false,
                        customClass: {
                            popup: 'swal-danger-theme',
                            confirmButton: 'swal2-confirm',
                            cancelButton: 'swal2-cancel',
                        }
                    }).then((result) => {
                        if (result.isConfirmed && form) {
                            form.submit();
                        }
                    });
                } else if (confirm('Apakah Anda yakin ingin menghapus alamat ini?')) {
                    if (form) form.submit();
                }
            }
        </script>
    @endpush
</x-pwa-layout>