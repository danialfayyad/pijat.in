<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Profil Akun & Kata Sandi') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold rounded-2xl flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold rounded-2xl shadow-xs">
                <div class="font-bold mb-1">Gagal memperbarui kata sandi:</div>
                <ul class="list-disc pl-5 space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Col: Data Diri & Wilayah Penugasan -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Identitas Akun</h3>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div>
                            <span class="text-slate-400 block">Nama Lengkap</span>
                            <span class="font-bold text-slate-800 text-sm">{{ $admin->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Alamat Email</span>
                            <span class="font-medium text-slate-700">{{ $admin->email }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Peran Sistem</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80 capitalize mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                {{ str_replace('_', ' ', $admin->role->value ?? $admin->role) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                    <div class="border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Wilayah Tugas Aktif</h3>
                    </div>

                    <div class="space-y-1.5">
                        @forelse ($assignedRegions as $region)
                            <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 font-semibold">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ $region->name }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">Belum ada wilayah tugas.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Col: Form Ubah Password (ADM-14) -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-6">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-slate-800 text-base">Ubah Kata Sandi Akun</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Pastikan kata sandi baru Anda aman dan memenuhi standar kompleksitas.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" 
                                   name="current_password" 
                                   required 
                                   autocomplete="current-password"
                                   class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" 
                                   name="password" 
                                   required 
                                   autocomplete="new-password"
                                   class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   required 
                                   autocomplete="new-password"
                                   class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3">
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end">
                            <button type="submit" 
                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer">
                                Simpan Kata Sandi Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

