<x-pwa-layout title="Hapus Akun - Pijat.In" headerTitle="Hapus Akun" :backUrl="route('profile.edit')">
    <div class="py-2 space-y-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-pwa-layout>

