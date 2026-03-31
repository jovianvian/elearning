@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between mt-4">
        @if ($paginator->onFirstPage())
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-300 bg-white border border-slate-200 rounded-lg">{{ __('pagination.previous') }}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50">{{ __('pagination.previous') }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50">{{ __('pagination.next') }}</a>
        @else
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-300 bg-white border border-slate-200 rounded-lg">{{ __('pagination.next') }}</span>
        @endif
    </nav>
@endif

