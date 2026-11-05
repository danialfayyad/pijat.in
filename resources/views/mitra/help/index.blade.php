<x-pwa-layout title="Pusat Bantuan Mitra - Pijat.In" headerTitle="Pusat Bantuan Mitra" :backUrl="route('mitra.dashboard')">
    <div class="space-y-4 py-2" x-data="{
        activeFaq: null,
        toggleFaq(id) {
            this.activeFaq = this.activeFaq === id ? null : id;
        }
    }">
        <!-- Header Text -->
        <div class="px-1 space-y-1">
            <h2 class="text-lg font-extrabold text-slate-900 leading-tight">Pusat Informasi & Bantuan</h2>
            <p class="text-xs text-slate-500">Temukan solusi cepat seputar operasional kemitraan dan pesanan.</p>
        </div>

        <!-- Active Ticket Banner (If any) -->
        @if ($activeTicket)
            <div class="bg-white rounded-2xl p-4 border border-emerald-200/90 shadow-2xs flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0 text-emerald-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-bold font-mono text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">{{ $activeTicket->ticket_code }}</span>
                            <span class="text-[10px] font-semibold text-slate-400">• Sesi Berjalan</span>
                        </div>
                        <div class="text-xs font-bold text-slate-800 truncate mt-0.5">{{ $activeTicket->subject }}</div>
                    </div>
                </div>
                <a href="{{ route('mitra.help.ticket', $activeTicket) }}"
                   class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs whitespace-nowrap shrink-0">
                    Buka Chat
                </a>
            </div>
        @endif

        <!-- Search Input -->
        <form method="GET" action="{{ route('mitra.help.index') }}" class="relative">
            @if ($selectedCategory)
                <input type="hidden" name="category" value="{{ $selectedCategory }}">
            @endif
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <input type="text"
                   name="search"
                   value="{{ $search }}"
                   placeholder="Cari kendala, kata kunci, atau pertanyaan..."
                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200/90 rounded-2xl text-xs text-slate-800 focus:ring-emerald-500 focus:border-emerald-500 shadow-2xs outline-none placeholder:text-slate-400">
        </form>

        <!-- Category Filter Pills (Clean Text Only) -->
        @if ($categories->isNotEmpty())
            <div class="flex items-center gap-2 overflow-x-auto pb-0.5 scrollbar-none">
                <a href="{{ route('mitra.help.index', ['search' => $search]) }}"
                   class="px-4 py-1.5 rounded-2xl text-xs font-bold transition whitespace-nowrap {{ empty($selectedCategory) ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/90' }}">
                    Semua Topik
                </a>
                @foreach ($categories as $cat)
                    <a href="{{ route('mitra.help.index', ['category' => $cat, 'search' => $search]) }}"
                       class="px-4 py-1.5 rounded-2xl text-xs font-bold transition whitespace-nowrap {{ $selectedCategory === $cat ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/90' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- FAQ Section: Single Grouped Container -->
        <div class="space-y-1.5">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pertanyaan Sering Diajukan</h3>
                @if ($selectedCategory || $search)
                    <a href="{{ route('mitra.help.index') }}" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700">Reset</a>
                @endif
            </div>

            @if ($faqs->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                    @foreach ($faqs as $faq)
                        <div>
                            <button type="button"
                                    @click="toggleFaq({{ $faq->id }})"
                                    class="w-full p-4 text-left flex items-center justify-between gap-3 hover:bg-slate-50/70 transition cursor-pointer">
                                <span class="text-xs sm:text-sm font-bold text-slate-800 leading-snug">
                                    {{ $faq->question }}
                                </span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-200"
                                     :class="{ 'rotate-180 text-emerald-600': activeFaq === {{ $faq->id }} }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="activeFaq === {{ $faq->id }}"
                                 x-collapse
                                 class="px-4 pb-4 pt-1 text-xs text-slate-600 leading-relaxed bg-slate-50/50 border-t border-slate-100">
                                {{ $faq->answer }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-2xl p-6 text-center border border-slate-200/90 space-y-1.5 shadow-2xs">
                    <p class="text-xs font-bold text-slate-700">Pertanyaan tidak ditemukan</p>
                    <p class="text-[11px] text-slate-400">Gunakan kata kunci pencarian lain atau tanyakan langsung ke admin wilayah.</p>
                </div>
            @endif
        </div>

        <!-- Inline Footer Assistance Link -->
        <div class="pt-2 text-center text-xs text-slate-500">
            <span>Masalah belum terselesaikan? </span>
            <a href="{{ route('mitra.help.chat') }}" class="font-bold text-emerald-600 hover:text-emerald-700 underline underline-offset-2">
                Hubungi Admin via Live Chat
            </a>
        </div>
    </div>

    <!-- Floating Live Chat Bubble Button (FAB CS) -->
    <div class="fixed bottom-6 left-0 right-0 max-w-md mx-auto pointer-events-none z-40 px-4">
        <div class="flex justify-end">
            <a href="{{ route('mitra.help.chat') }}"
               class="pointer-events-auto w-14 h-14 rounded-full bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white shadow-xl flex items-center justify-center transition-all duration-200 group relative border-2 border-white/90"
               title="Live Chat Bantuan CS"
               aria-label="Live Chat Bantuan CS">
                <!-- CS Headset Icon -->
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 18v-6a9 9 0 0118 0v6" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3v5zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3v5z" />
                </svg>

                <!-- Tooltip Label on Hover -->
                <span class="absolute right-16 px-2.5 py-1 bg-slate-900/90 text-white text-[11px] font-semibold rounded-lg shadow-md whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                    Live Chat CS
                </span>

                @if ($activeTicket)
                    <!-- Active Ticket Ping Dot -->
                    <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-white"></span>
                    </span>
                @endif
            </a>
        </div>
    </div>
</x-pwa-layout>

