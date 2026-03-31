@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between mt-4">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-500">
                    {{ __('pagination.showing') }}
                    <span class="font-semibold text-slate-700">{{ $paginator->firstItem() }}</span>
                    {{ __('pagination.to') }}
                    <span class="font-semibold text-slate-700">{{ $paginator->lastItem() }}</span>
                    {{ __('pagination.of') }}
                    <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span>
                    {{ __('pagination.results') }}
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-xl shadow-sm border border-slate-200 bg-white p-1">
                    @if ($paginator->onFirstPage())
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm text-slate-300 cursor-not-allowed">{{ __('pagination.previous') }}</span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm text-slate-600 hover:bg-slate-100">{{ __('pagination.previous') }}</a>
                    @endif

                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="inline-flex items-center px-3 py-1.5 text-sm text-slate-400">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm bg-primary text-white">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm text-slate-700 hover:bg-slate-100">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm text-slate-600 hover:bg-slate-100">{{ __('pagination.next') }}</a>
                    @else
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm text-slate-300 cursor-not-allowed">{{ __('pagination.next') }}</span>
                    @endif
                </span>
            </div>
        </div>

        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-300 bg-white border border-slate-200 rounded-lg">{{ __('pagination.previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50">{{ __('pagination.previous') }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50">{{ __('pagination.next') }}</a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-slate-300 bg-white border border-slate-200 rounded-lg">{{ __('pagination.next') }}</span>
            @endif
        </div>
    </nav>
@endif

