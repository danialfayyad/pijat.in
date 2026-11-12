@php
    $blockers = $blockers ?? $user->accountDeletionBlockers(ignorePendingRequest: true);
    $latestDeletionRequest = $latestDeletionRequest ?? $user->accountDeletionRequests()->latest()->first();
    $hasPendingRequest = $latestDeletionRequest?->status === \App\Models\AccountDeletionRequest::STATUS_PENDING;
@endphp

<section class="space-y-4">
    @if ($errors->userDeletion->has('eligibility'))
        <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            {{ $errors->userDeletion->first('eligibility') }}
        </div>
    @endif

    @if ($hasPendingRequest)
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 space-y-2">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xs font-bold text-amber-900">Pengajuan Sedang Ditinjau Admin</h2>
                <span class="text-[11px] font-semibold text-amber-700 shrink-0">Menunggu</span>
            </div>
            <p class="text-xs text-amber-900/80 leading-relaxed">
                Pengajuan penghapusan akun Anda dikirim pada {{ $latestDeletionRequest->created_at?->translatedFormat('d M Y, H:i') }}. Akun akan dihapus setelah disetujui admin.
            </p>
            @if ($latestDeletionRequest->reason)
                <p class="text-[11px] text-amber-900/70 pt-1 border-t border-amber-200/60">
                    Alasan: {{ $latestDeletionRequest->reason }}
                </p>
            @endif
        </div>
    @else
        @if ($latestDeletionRequest?->status === \App\Models\AccountDeletionRequest::STATUS_REJECTED)
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                <p class="text-xs font-semibold text-slate-900">Pengajuan sebelumnya ditolak</p>
                @if ($latestDeletionRequest->rejection_reason)
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Catatan admin: {{ $latestDeletionRequest->rejection_reason }}
                    </p>
                @endif
            </div>
        @endif

        @if ($blockers !== [])
            <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200/80 space-y-2.5">
                <h2 class="text-xs font-bold text-amber-900">
                    Belum Dapat Mengajukan Penghapusan Akun
                </h2>
                <p class="text-xs text-amber-900/80 leading-relaxed">
                    Selesaikan aktivitas berikut terlebih dahulu sebelum mengajukan penghapusan akun:
                </p>
                <ul class="space-y-1.5 text-xs text-amber-900/85 leading-relaxed">
                    @foreach ($blockers as $blocker)
                        <li class="flex items-start space-x-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                            <span>{{ $blocker }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-200/70 space-y-2.5">
                <h2 class="text-xs font-bold text-rose-900">
                    Ketentuan Pengajuan Penghapusan Akun
                </h2>
                <ul class="space-y-2 text-xs text-rose-900/80 leading-relaxed">
                    <li class="flex items-start space-x-2">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                        <span>Pengajuan akan diperiksa terlebih dahulu oleh admin sebelum akun dihapus permanen.</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                        <span>Setelah disetujui admin, seluruh data profil, alamat, dan riwayat pesanan akan dihapus permanen.</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                        <span>Tindakan penghapusan yang sudah disetujui tidak dapat dibatalkan.</span>
                    </li>
                </ul>
            </div>

            <div>
                <button
                    type="button"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                    class="w-full min-h-[44px] px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-xs transition cursor-pointer"
                >Ajukan Penghapusan Akun</button>
            </div>
        @endif
    @endif

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->has('password') || $errors->userDeletion->has('reason')" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-base font-bold text-slate-900">
                Ajukan Penghapusan Akun?
            </h2>

            <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                Pengajuan akan diteruskan ke admin untuk ditinjau. Masukkan kata sandi akun Anda untuk mengonfirmasi pengajuan ini.
            </p>

            <div class="mt-4">
                <label for="deletion_reason" class="block text-xs font-semibold text-slate-700 mb-1">Alasan (Opsional)</label>
                <textarea
                    id="deletion_reason"
                    name="reason"
                    rows="2"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 focus:ring-rose-500"
                    placeholder="Contoh: Sudah tidak menggunakan layanan"
                >{{ old('reason') }}</textarea>
                <x-input-error :messages="$errors->userDeletion->get('reason')" class="mt-1" />
            </div>

            <div class="mt-3">
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Konfirmasi <span class="text-rose-500">*</span></label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 focus:ring-rose-500"
                    placeholder="Masukkan kata sandi Anda"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1" />
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" x-on:click="$dispatch('close')" class="min-h-[44px] px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer">
                    Batal
                </button>

                <button type="submit" class="min-h-[44px] px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold transition cursor-pointer">
                    Kirim Pengajuan
                </button>
            </div>
        </form>
    </x-modal>
</section>
