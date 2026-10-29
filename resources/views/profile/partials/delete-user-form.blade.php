<section class="space-y-4">
    <header>
        <h2 class="text-xs font-bold text-slate-900 border-b border-slate-100 pb-2">
            Hapus Akun
        </h2>

        <p class="mt-1 text-xs text-slate-500">
            Setelah akun Anda dihapus, seluruh data dan riwayat pesanan akan dihapus secara permanen.
        </p>
    </header>

    <div>
        <button
            type="button"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="min-h-[44px] px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition cursor-pointer"
        >Hapus Akun Saya</button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-base font-extrabold text-slate-900">
                Apakah Anda yakin ingin menghapus akun Anda?
            </h2>

            <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                Tindakan ini tidak dapat dibatalkan. Masukkan kata sandi akun Anda untuk mengonfirmasi penghapusan permanen.
            </p>

            <div class="mt-4">
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Konfirmasi</label>

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
                <button type="button" x-on:click="$dispatch('close')" class="min-h-[44px] px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                    Batal
                </button>

                <button type="submit" class="min-h-[44px] px-4 py-2 bg-rose-700 hover:bg-rose-800 text-white rounded-xl text-xs font-bold transition cursor-pointer">
                    Hapus Akun Permanen
                </button>
            </div>
        </form>
    </x-modal>
</section>
