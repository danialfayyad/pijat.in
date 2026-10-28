@if (auth()->user()->isAdmin() || auth()->user()->isSuperadmin())
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profile') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
@else
    <x-pwa-layout title="Akun Saya - Pijat.In" headerTitle="Pengaturan Akun">
        <div class="py-2 space-y-4">
            <div class="p-5 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="p-5 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                @include('profile.partials.update-password-form')
            </div>

            <div class="p-5 bg-white shadow-xs rounded-2xl border border-slate-200/80">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </x-pwa-layout>
@endif
