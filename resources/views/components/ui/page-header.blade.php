@props([
    'title',
    'subtitle' => null,
])

<div class="mb-5 flex flex-wrap items-start justify-between gap-3 sm:gap-4">
    <div class="space-y-1">
        <h2 class="tera-h1">{{ $title }}</h2>
        @if($subtitle)
            <p class="tera-sub">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="flex w-full sm:w-auto flex-wrap items-center gap-2 sm:justify-end sm:self-start">
        {{ $actions ?? '' }}
    </div>
</div>
