<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
            {{ __('Kelola Konten & Dokumen Legal') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        <!-- Master Table Card (Konsisten dengan menu-menu admin lainnya) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Daftar Dokumen Legal & Kebijakan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Naskah resmi syarat & ketentuan, kebijakan privasi, dan standar operasional platform.</p>
                </div>
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-xl">
                    {{ count($documents) }} Dokumen
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5">Nama Dokumen Legal</th>
                            <th class="px-5 py-3.5">Kategori / Sasaran</th>
                            <th class="px-5 py-3.5 text-center">Versi Aktif</th>
                            <th class="px-5 py-3.5">Terakhir Diperbarui</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($documents as $doc)
                            @php
                                $categoryLabel = match($doc->type) {
                                    'sop_kemitraan' => 'Pedoman Mitra Terapis',
                                    'terms_and_conditions' => 'Pengguna & Pelanggan',
                                    'privacy_policy' => 'Kebijakan Privasi (UU PDP)',
                                    'risk_statement' => 'Penafian Medis & Risiko',
                                    default => 'Dokumen Legal',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-4 max-w-md">
                                    <div class="font-bold text-slate-800 text-xs sm:text-sm">
                                        {{ $doc->title }}
                                    </div>
                                    @if ($doc->summary)
                                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                                            {{ $doc->summary }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-medium text-slate-700">
                                        {{ $categoryLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <span class="font-semibold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-md text-xs">
                                        Versi {{ $doc->version }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-slate-700 font-medium">{{ $doc->updated_at->format('d M Y') }}</div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $doc->updater ? 'Oleh '.$doc->updater->name : 'Sistem' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('superadmin.legal-documents.edit', $doc) }}" 
                                       class="px-3.5 py-2 bg-white hover:bg-slate-50 text-emerald-700 hover:text-emerald-800 font-semibold rounded-xl text-xs transition border border-slate-200 shadow-2xs inline-flex items-center gap-1.5 cursor-pointer">
                                        <span>Edit Naskah</span>
                                        <span>&rarr;</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
