<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('superadmin.legal-documents.index') }}" 
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition"
               title="Kembali ke Dokumen Legal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                Edit Dokumen: {{ $document->title }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold shadow-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <p>&bull; {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-6">
            <form method="POST" action="{{ route('superadmin.legal-documents.update', $document) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Judul Dokumen Resmi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               value="{{ old('title', $document->title) }}" 
                               required
                               class="w-full text-xs font-semibold rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Nomor Versi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="version" 
                               value="{{ old('version', $document->version) }}" 
                               required
                               placeholder="Contoh: 2.3"
                               class="w-full text-xs font-bold rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3">
                        <span class="block text-[11px] text-slate-400 mt-1">
                            Versi saat ini: v{{ $document->version }}
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Ringkasan / Intisari Dokumen
                    </label>
                    <input type="text" 
                           name="summary" 
                           value="{{ old('summary', $document->summary) }}" 
                           placeholder="Penjelasan singkat substansi perubahan..."
                           class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Isi Teks Lengkap Dokumen Legal <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="content" 
                              rows="14" 
                              required
                              class="w-full text-xs font-sans leading-relaxed rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 p-3.5">{{ old('content', $document->content) }}</textarea>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Catatan Alasan Perubahan Versi (Audit Trail) <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="reason" 
                              rows="2" 
                              required
                              placeholder="Contoh: Penyesuaian regulasi sanksi demerit points dan penegasan jam operasional..."
                              class="w-full text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 p-2.5">{{ old('reason') }}</textarea>
                    <span class="block text-[11px] text-slate-400 mt-0.5">Wajib diisi minimal 5 karakter untuk rekam jejak audit sistem.</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <a href="{{ route('superadmin.legal-documents.index') }}" 
                       class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer">
                        Simpan & Terbitkan Versi Baru
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
