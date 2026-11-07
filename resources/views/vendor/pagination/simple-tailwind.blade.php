@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex gap-2 items-center justify-between">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-300 bg-white border border-slate-200 cursor-not-allowed rounded-xl select-none">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-300 bg-white border border-slate-200 cursor-not-allowed rounded-xl select-none">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
