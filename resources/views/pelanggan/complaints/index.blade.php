<x-pwa-layout title="Riwayat Aduan Layanan - Pijat.In" headerTitle="Aduan Saya" :backUrl="route('pelanggan.dashboard')">
    <div class="py-2 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daftar Tiket Aduan</h2>
            <span class="text-xs font-bold text-emerald-800">{{ $complaints->total() }} Tiket</span>
        </div>

        @if ($complaints->isEmpty())
            <div class="bg-white rounded-2xl p-8 border border-slate-200/90 shadow-xs text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Belum Ada Aduan</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                        Anda belum pernah mengajukan aduan layanan. Jika mengalami kendala saat pesanan selesai, Anda dapat mengajukan aduan dalam 24 jam.
                    </p>
                </div>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($complaints as $c)
                    <a href="{{ route('pelanggan.complaints.show', $c) }}" 
                       class="block bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs hover:border-emerald-300 transition space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-extrabold text-slate-900 font-mono">{{ $c->complaint_code }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $c->status->badgeClasses() }}">
                                {{ $c->status->label() }}
                            </span>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold text-slate-900 line-clamp-1">{{ $c->title }}</h4>
                            <p class="text-[11px] text-slate-500 mt-0.5">Pesanan: {{ $c->order?->order_code ?? '-' }} • {{ $c->created_at->diffForHumans() }}</p>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                            <span class="text-slate-400 font-medium">{{ $c->category_label }}</span>
                            <span class="font-bold text-emerald-700 flex items-center space-x-0.5">
                                <span>Rincian</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </a>
                @endforeach

                <div class="pt-2">
                    {{ $complaints->links() }}
                </div>
            </div>
        @endif
    </div>
</x-pwa-layout>

