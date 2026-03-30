@props([
    'title',
    'value',
    'icon' => 'bar-chart-3',
    'color' => 'primary',
    'hint' => null,
])

@php
    $palette = [
        'primary' => 'from-primary to-blue-500',
        'deep' => 'from-deep to-primary',
        'yellow' => 'from-yellowx to-amber-400 text-slate-900',
        'red' => 'from-redx to-rose-500',
        'sky' => 'from-skyx to-cyan-400 text-slate-900',
        'green' => 'from-successx to-emerald-500',
    ];
    $bg = $palette[$color] ?? $palette['primary'];
@endphp

<div class="tera-card overflow-hidden">
    <div class="h-1.5 w-full bg-gradient-to-r {{ $bg }}"></div>
    <div class="tera-card-body">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-wide font-semibold text-slate-500">{{ $title }}</p>
                <p class="text-2xl font-extrabold text-ink mt-2">{{ $value }}</p>
                @if($hint)
                    <p class="text-xs text-slate-500 mt-1">{{ $hint }}</p>
                @endif
            </div>
            <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-700 grid place-items-center">
                <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
            </div>
        </div>
    </div>
</div>

