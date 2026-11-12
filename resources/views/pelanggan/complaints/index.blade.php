<x-pwa-layout title="Riwayat Aduan Layanan - Pijat.In" headerTitle="Aduan Saya" :backUrl="route('pelanggan.dashboard')">
    @php
        $currentStatus = $status ?? 'semua';
        $tabs = [
            ['key' => 'semua', 'label' => 'Semua'],
            ['key' => 'menunggu', 'label' => 'Aduan Baru'],
            ['key' => 'diproses', 'label' => 'Diproses'],
            ['key' => 'selesai', 'label' => 'Selesai'],
            ['key' => 'ditolak', 'label' => 'Ditolak'],
        ];
    @endphp

    <div class="py-1 space-y-3">
        <!-- Tab Filter Status -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
            @foreach ($tabs as $tab)
                @php
                    $isActive = $currentStatus === $tab['key'];
                    $url = $tab['key'] === 'semua'
                        ? route('pelanggan.complaints.index')
                        : route('pelanggan.complaints.index', ['status' => $tab['key']]);
                @endphp
                <a href="{{ $url }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition shrink-0 {{ $isActive ? 'bg-[#064e3b] text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200/80 hover:border-emerald-300' }}">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        @if ($complaints->isEmpty())
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs text-center space-y-1.5">
                <p class="text-sm font-semibold text-slate-900">Belum ada aduan</p>
                <p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed">
                    {{ $currentStatus === 'semua' ? 'Aduan yang Anda kirim dari pesanan selesai akan muncul di sini.' : 'Belum ada aduan dengan status ini.' }}
                </p>
            </div>
        @else
            @foreach ($complaints as $c)
                @php
                    $statusTextClass = match ($c->status) {
                        \App\Enums\ComplaintStatus::Menunggu => 'text-amber-700',
                        \App\Enums\ComplaintStatus::Diproses => 'text-sky-700',
                        \App\Enums\ComplaintStatus::Selesai => 'text-emerald-700',
                        \App\Enums\ComplaintStatus::Ditolak => 'text-slate-500',
                    };
                @endphp
                <a href="{{ route('pelanggan.complaints.show', $c) }}"
                   class="w-full h-[116px] flex flex-col justify-between bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-emerald-300 transition">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-medium text-slate-500 truncate">{{ $c->category_label }}</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold shrink-0 {{ $statusTextClass }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $c->status->dotClass() }}"></span>
                            <span>{{ $c->status->label() }}</span>
                        </span>
                    </div>

                    <h3 class="text-sm font-semibold text-slate-900 leading-snug line-clamp-2">
                        {{ $c->title }}
                    </h3>

                    <p class="text-[11px] text-slate-400">
                        {{ $c->created_at->translatedFormat('d M Y, H:i') }}
                    </p>
                </a>
            @endforeach

            @if ($complaints->hasPages())
                <div class="pt-1">
                    {{ $complaints->links() }}
                </div>
            @endif
        @endif
    </div>
</x-pwa-layout>
