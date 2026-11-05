<x-pwa-layout title="Preferensi Layanan - Pijat.In" headerTitle="Preferensi Layanan" :backUrl="route('mitra.profile')">
    <div class="py-2 space-y-4">
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Jenis Layanan yang Dilayani</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pilih keahlian pijat yang bersedia Anda ambil. Minimal harus ada 1 jenis layanan yang aktif.
                </p>
            </div>

            <form method="POST" action="{{ route('mitra.profile.services.update') }}" class="space-y-3.5">
                @csrf

                <div class="space-y-2.5">
                    @forelse ($services as $service)
                        @php
                            $isEnabled = $user->partnerProfile ? $user->partnerProfile->isServiceEnabled($service->id) : true;
                        @endphp
                        <label class="flex items-start justify-between p-3.5 rounded-xl border border-slate-200/90 hover:border-emerald-300 bg-slate-50/50 hover:bg-emerald-50/20 transition cursor-pointer">
                            <div class="pr-3">
                                <span class="text-xs font-bold text-slate-900 block">{{ $service->name }}</span>
                                @if ($service->description)
                                    <span class="text-[11px] text-slate-500 block mt-0.5 leading-relaxed">{{ $service->description }}</span>
                                @endif
                            </div>
                            <div class="flex items-center min-h-[24px]">
                                <input type="checkbox"
                                       name="services[]"
                                       value="{{ $service->id }}"
                                       {{ $isEnabled ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer">
                            </div>
                        </label>
                    @empty
                        <p class="text-xs text-slate-500 italic py-4 text-center">Belum ada layanan aktif yang terdaftar di sistem.</p>
                    @endforelse
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer">
                        Simpan Preferensi Layanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-pwa-layout>

