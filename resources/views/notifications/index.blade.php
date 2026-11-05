<x-pwa-layout title="Pusat Notifikasi - Pijat.In" headerTitle="Notifikasi" :backUrl="url()->previous() !== url()->current() ? url()->previous() : route('dashboard')">
    <div class="py-2 space-y-4">

        <!-- Status Alerts -->
        @if (session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold shadow-xs flex items-center space-x-2.5">
                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter & Actions Bar -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-xs flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $currentFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua
                </a>
                <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 {{ $currentFilter === 'unread' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <span>Belum Dibaca</span>
                    @if ($unreadCount > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold bg-emerald-600 text-white">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </a>
            </div>

            @if ($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-xl transition cursor-pointer">
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        <!-- Notification List -->
        @if ($notifications->isEmpty())
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200/90 shadow-xs space-y-3">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Belum Ada Notifikasi</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                        Pemberitahuan aktivitas pesanan dan dompet Anda akan tampil di sini.
                    </p>
                </div>
            </div>
        @else
            <div class="space-y-2.5">
                @foreach ($notifications as $notification)
                    <div class="p-4 rounded-2xl border transition {{ $notification->read_at ? 'bg-white border-slate-200/80 shadow-2xs' : 'bg-emerald-50/50 border-emerald-200 shadow-xs' }}">
                        <div class="flex items-start space-x-3">
                            <!-- Indicator -->
                            <div class="mt-1 shrink-0">
                                <span class="w-2.5 h-2.5 rounded-full block {{ $notification->read_at ? 'bg-transparent' : 'bg-emerald-600 ring-2 ring-emerald-200' }}"></span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-extrabold text-slate-900 leading-tight">
                                        {{ $notification->title }}
                                    </h4>
                                    <span class="text-[10px] text-slate-400 font-medium shrink-0 ml-2">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    {{ $notification->body }}
                                </p>

                                <div class="mt-3 flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                                    @if ($notification->action_url)
                                        <a href="{{ $notification->action_url }}" class="font-bold text-emerald-700 hover:text-emerald-800 hover:underline">
                                            {{ $notification->action_label }} &rarr;
                                        </a>
                                    @else
                                        <span></span>
                                    @endif

                                    @if (!$notification->read_at)
                                        <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-[11px] font-semibold text-slate-500 hover:text-emerald-700 transition flex items-center space-x-1 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span>Tandai Dibaca</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-2">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</x-pwa-layout>

