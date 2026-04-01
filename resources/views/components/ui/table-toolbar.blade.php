@props([
    'action' => null,
    'searchPlaceholder' => 'Search...',
    'searchName' => 'q',
    'searchValue' => '',
])

<form method="GET" action="{{ $action ?? url()->current() }}" class="mb-4 tera-card" x-data>
    <div class="tera-card-body py-3">
        <div class="grid gap-3 md:grid-cols-[1fr_auto] items-end">
            <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="tera-label">Search</label>
                    <div class="relative">
                        <input
                            type="text"
                            name="{{ $searchName }}"
                            value="{{ $searchValue }}"
                            placeholder="{{ $searchPlaceholder }}"
                            class="tera-input pr-10"
                        >
                        <button
                            type="button"
                            class="absolute inset-y-0 right-2 my-auto inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100"
                            @click="$el.previousElementSibling.value = ''"
                            title="Clear search"
                        >×</button>
                    </div>
                </div>
                {{ $filters ?? '' }}
            </div>
            <div class="flex items-center justify-end gap-2">
                <a href="{{ $action ?? url()->current() }}" class="tera-btn tera-btn-muted">Reset</a>
                <button type="submit" class="tera-btn tera-btn-primary">Apply</button>
            </div>
        </div>
    </div>
</form>
