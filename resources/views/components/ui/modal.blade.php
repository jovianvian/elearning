@props([
    'name' => 'open',
    'title' => 'Modal',
    'maxWidth' => 'max-w-3xl',
])

<div x-cloak x-show="{{ $name }}" class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-2 sm:p-6">
    <div class="absolute inset-0 bg-slate-950/45" @click="{{ $name }} = false"></div>

    <div
        x-show="{{ $name }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-[.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-[.98]"
        class="relative w-full {{ $maxWidth }} max-h-[92vh] sm:max-h-[88vh] rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden"
    >
        <div class="flex items-center justify-between border-b border-slate-100 px-4 sm:px-5 py-3.5 sm:py-4">
            <h3 class="text-base font-bold text-slate-900">{{ $title }}</h3>
            <button type="button" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700" @click="{{ $name }} = false">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="max-h-[calc(92vh-60px)] sm:max-h-[calc(88vh-64px)] overflow-y-auto px-4 sm:px-5 py-4 sm:py-5">
            {{ $slot }}
        </div>
    </div>
</div>
