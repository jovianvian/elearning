@props([
    'title',
    'subtitle' => null,
])

<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h2 class="tera-h1">{{ $title }}</h2>
        @if($subtitle)
            <p class="tera-sub mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="flex items-center gap-2">
        {{ $actions ?? '' }}
    </div>
</div>

