<x-pwa-layout title="Pusat Bantuan Mitra - Pijat.In" headerTitle="Pusat Bantuan Mitra" :backUrl="route('mitra.dashboard')">
    <div class="space-y-4 py-2" x-data="{
        activeFaq: null,
        toggleFaq(id) {
            this.activeFaq = this.activeFaq === id ? null : id;
        }
    }">
        <!-- Header Banner / Intro -->
        <div class="bg-gradient-to-br from-emerald-700 to-teal-800 rounded-2xl p-5 text-white shadow-md relative overflow-hidden">
            <div class="relative z-10 space-y-1.5">
                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-emerald-50">
                    Panduan & FAQ Terapis Mitra
                </span>
                <h2 class="text-lg font-black leading-snug">Pusat Kendala & Informasi Mitra</h2>
                <p class="text-xs text-emerald-100 leading-relaxed">
                    Panduan seputar pesanan bursa, komisi, pencairan saldo, sanksi poin, dan SOP pelayanan.
                </p>
            </div>
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        </div>

        <!-- Active Ticket Banner (If any) -->
        @if ($activeTicket)
            <div class="bg-white rounded-2xl p-4 border border-emerald-200/90 shadow-xs flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0 text-emerald-600">
                        <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-bold font-mono text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">{{ $activeTicket->ticket_code }}</span>
                            <span class="text-[10px] font-semibold text-slate-500">• Sesi Aktif</span>
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
                   placeholder="Cari kendala mitra, pencairan, pesanan..."
                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-emerald-500 focus:border-emerald-500 shadow-2xs">
        </form>

        <!-- Category Filter Pills -->
        @if ($categories->isNotEmpty())
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                <a href="{{ route('mitra.help.index', ['search' => $search]) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ empty($selectedCategory) ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                    Semua
                </a>
                @foreach ($categories as $cat)
                    <a href="{{ route('mitra.help.index', ['category' => $cat, 'search' => $search]) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $selectedCategory === $cat ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 shadow-2xs' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- FAQ Accordion List -->
        <div class="space-y-2">
            @forelse ($faqs as $faq)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden transition">
                    <button type="button"
                            @click="toggleFaq({{ $faq->id }})"
                            class="w-full p-4 text-left flex items-center justify-between gap-3 cursor-pointer">
                        <div class="space-y-1 min-w-0">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                {{ $faq->category }}
                            </span>
                            <div class="text-xs sm:text-sm font-bold text-slate-800 leading-snug">
                                {{ $faq->question }}
                            </div>
                        </div>
                        <div class="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center shrink-0 text-slate-400 transition"
                             :class="{ 'rotate-180 bg-emerald-50 text-emerald-600': activeFaq === {{ $faq->id }} }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </button>
                    <div x-show="activeFaq === {{ $faq->id }}"
                         x-collapse
                         class="px-4 pb-4 pt-1 text-xs text-slate-600 leading-relaxed border-t border-slate-100 bg-slate-50/50">
                        {{ $faq->answer }}
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-6 text-center border border-slate-200/80 space-y-2">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="text-xs font-bold text-slate-700">Pertanyaan tidak ditemukan</div>
                    <p class="text-[11px] text-slate-400">Coba gunakan kata kunci lain atau langsung hubungi admin operasional wilayah Anda.</p>
                </div>
            @endforelse
        </div>

        <!-- Live Chat Escalation Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs space-y-3">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800">Perlu Bantuan Operasional?</h3>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Jika ada kendala di lapangan, pembatalan pesanan, atau perselisihan, admin wilayah siap mendampingi melalui live chat.
                    </p>
                </div>
            </div>
            <a href="{{ route('mitra.help.chat') }}"
               class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                <span>Live Chat Admin Wilayah</span>
            </a>
        </div>
    </div>

    <!-- Floating Live Chat Bubble Button (FAB CS) -->
    <div class="fixed bottom-6 left-0 right-0 max-w-md mx-auto pointer-events-none z-40 px-4">
        <div class="flex justify-end">
            <a href="{{ route('mitra.help.chat') }}"
               class="pointer-events-auto w-14 h-14 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 hover:from-emerald-700 hover:to-teal-600 active:scale-95 text-white shadow-xl flex items-center justify-center transition-all duration-200 group relative border-2 border-white/90"
               title="Live Chat Bantuan CS"
               aria-label="Live Chat Bantuan CS">
                <!-- CS Headset Icon -->
                <svg class="w-7 h-7 text-white drop-shadow-xs" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

