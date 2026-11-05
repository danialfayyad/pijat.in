<x-pwa-layout title="Informasi Pribadi - Pijat.In" headerTitle="Informasi Pribadi" :backUrl="route('profile.edit')">
    <div class="py-2 space-y-4">
        @if (session('status') === 'profile-updated')
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-xs font-semibold shadow-2xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>Data profil berhasil diperbarui.</span>
            </div>
        @endif

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>
</x-pwa-layout>

