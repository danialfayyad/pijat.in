<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Identitas & Branding Aplikasi') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-2xl flex items-center gap-2.5 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded-2xl shadow-2xs space-y-1">
                @foreach ($errors->all() as $error)
                    <p>&bull; {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-6">
            <form action="{{ route('superadmin.branding.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
                  x-data="{
                      logoPreview: '{{ $appLogo ? asset('storage/' . $appLogo) : '' }}',
                      faviconPreview: '{{ $appFavicon ? asset('storage/' . $appFavicon) : '' }}',
                      removeLogo: false,
                      removeFavicon: false,
                      handleLogoChange(event) {
                          const file = event.target.files[0];
                          if (file) {
                              this.removeLogo = false;
                              const reader = new FileReader();
                              reader.onload = (e) => { this.logoPreview = e.target.result; };
                              reader.readAsDataURL(file);
                          }
                      },
                      handleFaviconChange(event) {
                          const file = event.target.files[0];
                          if (file) {
                              this.removeFavicon = false;
                              const reader = new FileReader();
                              reader.onload = (e) => { this.faviconPreview = e.target.result; };
                              reader.readAsDataURL(file);
                          }
                      }
                  }">
                @csrf
                @method('PUT')

                <!-- BAGIAN 1: IDENTITAS MEREK -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 pb-3 border-b border-slate-100">
                        Identitas Merek
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="app_name" class="block text-xs font-semibold text-slate-700 mb-1">
                                Nama Aplikasi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="app_name" name="app_name" required maxlength="50"
                                   value="{{ old('app_name', $appName) }}"
                                   placeholder="Pijat.In"
                                   class="w-full text-xs font-semibold rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3">
                        </div>

                        <div>
                            <label for="app_tagline" class="block text-xs font-semibold text-slate-700 mb-1">
                                Tagline / Slogan
                            </label>
                            <input type="text" id="app_tagline" name="app_tagline" maxlength="150"
                                   value="{{ old('app_tagline', $appTagline) }}"
                                   placeholder="Layanan Pijat & Relaksasi Profesional"
                                   class="w-full text-xs font-medium rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3">
                        </div>

                        <div class="md:col-span-2">
                            <label for="app_description" class="block text-xs font-semibold text-slate-700 mb-1">
                                Deskripsi Meta / SEO
                            </label>
                            <textarea id="app_description" name="app_description" rows="2" maxlength="500"
                                      placeholder="Deskripsi singkat platform untuk cuplikan pencarian..."
                                      class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2 px-3">{{ old('app_description', $appDescription) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 2: ASET VISUAL (LOGO & FAVICON) -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 pb-3 border-b border-slate-100">
                        Aset Visual
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <!-- LOGO UTAMA -->
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-slate-700">
                                Logo Utama (Navbar & Shell)
                            </label>
                            <div class="flex items-center gap-3">
                                <!-- Pratinjau Kotak -->
                                <div class="w-16 h-16 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center p-1.5 shrink-0 overflow-hidden">
                                    <template x-if="logoPreview && !removeLogo">
                                        <img :src="logoPreview" alt="Logo" class="max-h-full max-w-full object-contain">
                                    </template>
                                    <template x-if="!logoPreview || removeLogo">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-700 text-white flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                            </svg>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex-1 space-y-1.5 min-w-0">
                                    <input type="file" name="app_logo" id="app_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                           @change="handleLogoChange($event)"
                                           class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 hover:file:bg-slate-200 file:text-slate-700 cursor-pointer">
                                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                                        <span>PNG, JPG, SVG maks 2MB</span>
                                        @if ($appLogo)
                                            <label class="inline-flex items-center gap-1 text-rose-600 hover:text-rose-700 cursor-pointer font-medium">
                                                <input type="checkbox" name="remove_logo" value="1" x-model="removeLogo" class="rounded text-rose-600 border-slate-300 w-3 h-3">
                                                <span>Reset default</span>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FAVICON -->
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-slate-700">
                                Favicon (Tab Peramban)
                            </label>
                            <div class="flex items-center gap-3">
                                <!-- Pratinjau Kotak -->
                                <div class="w-16 h-16 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center p-1.5 shrink-0 overflow-hidden">
                                    <template x-if="faviconPreview && !removeFavicon">
                                        <img :src="faviconPreview" alt="Favicon" class="w-7 h-7 object-contain">
                                    </template>
                                    <template x-if="!faviconPreview || removeFavicon">
                                        <div class="w-7 h-7 rounded bg-white border border-slate-200 flex items-center justify-center text-slate-400">
                                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                            </svg>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex-1 space-y-1.5 min-w-0">
                                    <input type="file" name="app_favicon" id="app_favicon" accept="image/png,image/x-icon,image/svg+xml"
                                           @change="handleFaviconChange($event)"
                                           class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 hover:file:bg-slate-200 file:text-slate-700 cursor-pointer">
                                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                                        <span>PNG, ICO maks 1MB</span>
                                        @if ($appFavicon)
                                            <label class="inline-flex items-center gap-1 text-rose-600 hover:text-rose-700 cursor-pointer font-medium">
                                                <input type="checkbox" name="remove_favicon" value="1" x-model="removeFavicon" class="rounded text-rose-600 border-slate-300 w-3 h-3">
                                                <span>Reset default</span>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 3: ALASAN PERUBAHAN & AKSI SIMPAN -->
                <div class="pt-4 border-t border-slate-100 space-y-4">
                    <div>
                        <label for="reason" class="block text-xs font-semibold text-slate-700 mb-1">
                            Alasan Perubahan <span class="text-slate-400 font-normal">(Audit Log)</span>
                        </label>
                        <input type="text" id="reason" name="reason" maxlength="255"
                               placeholder="Contoh: Pembaruan logo resmi dan penyesuaian nama platform"
                               class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3">
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="submit"
                                class="py-2.5 px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
