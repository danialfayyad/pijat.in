<section>
    <header>
        <h2 class="text-xs font-bold text-slate-900 border-b border-slate-100 pb-2">
            Informasi Pribadi
        </h2>

        <p class="mt-1 text-xs text-slate-500">
            Perbarui data nama lengkap dan alamat email aktif akun Anda.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4 space-y-4">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
            <input id="name" name="name" type="text" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-emerald-600 transition" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email <span class="text-rose-500">*</span></label>
            <input id="email" name="email" type="email" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:border-emerald-600 focus:ring-emerald-600 transition" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            <x-input-error class="mt-1" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900">
                    <p>
                        Alamat email Anda belum terverifikasi.
                        <button form="send-verification" class="underline font-bold text-amber-950 hover:text-emerald-700 cursor-pointer ml-1">
                            Kirim ulang email verifikasi
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1 font-semibold text-emerald-800">
                            Tautan verifikasi baru telah dikirimkan ke alamat email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="pt-1">
            <button type="submit" class="w-full min-h-[48px] py-3.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl shadow-sm transition cursor-pointer">
                Simpan Perubahan
            </button>
        </div>
    </form>
</section>
