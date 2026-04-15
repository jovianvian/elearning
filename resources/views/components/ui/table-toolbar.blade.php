@props([
    'action' => null,
    'searchPlaceholder' => __('ui.search_placeholder'),
    'searchName' => 'q',
    'searchValue' => '',
    'live' => true,
    'debounceMs' => 350,
    'showApply' => null,
])

@php($renderApply = $showApply ?? !((bool) $live))

<form
    method="GET"
    action="{{ $action ?? url()->current() }}"
    class="mb-4 tera-card"
    x-data="{
        live: @js((bool) $live),
        debounceMs: @js((int) $debounceMs),
        loading: false,
        timer: null,
        filterTimer: null,
        triggerSubmit() {
            if (!this.live) return;
            this.loading = true;
            this.$nextTick(() => this.$refs.form.requestSubmit());
        },
        triggerSubmitDebounced(ms = 220) {
            if (!this.live) return;
            clearTimeout(this.filterTimer);
            this.filterTimer = setTimeout(() => this.triggerSubmit(), ms);
        },
        onSearchInput() {
            if (!this.live) return;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.triggerSubmit(), this.debounceMs);
        },
        onFilterInput(event) {
            if (!this.live) return;
            const tag = event?.target?.tagName ?? '';
            if (tag === 'SELECT' || event?.target?.type === 'checkbox') {
                this.triggerSubmit();
                return;
            }
            this.triggerSubmitDebounced();
        },
        clearSearch() {
            const input = this.$refs.searchInput;
            if (!input) return;
            input.value = '';
            this.onSearchInput();
        }
    }"
    x-ref="form"
    x-on:teramia:async-start.window="loading = true"
    x-on:teramia:async-end.window="loading = false"
>
    <div class="tera-card-body py-3">
        <div class="tera-toolbar-main">
            <div class="tera-toolbar-fields">
                <div>
                    <label class="tera-label">{{ __('ui.search') }}</label>
                    <div class="relative">
                        <input
                            type="text"
                            name="{{ $searchName }}"
                            value="{{ $searchValue }}"
                            placeholder="{{ $searchPlaceholder }}"
                            class="tera-input pr-10"
                            x-ref="searchInput"
                            @input="onSearchInput"
                        >
                        <button
                            type="button"
                            class="absolute inset-y-0 right-2 my-auto inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100"
                            @click="clearSearch"
                            title="{{ __('ui.clear_search') }}"
                        >&times;</button>
                    </div>
                </div>
                <div
                    class="tera-toolbar-filters"
                    @change="triggerSubmit"
                    @input="onFilterInput($event)"
                >
                    {{ $filters ?? '' }}
                </div>
            </div>
            <div class="tera-toolbar-actions">
                <a href="{{ $action ?? url()->current() }}" class="tera-btn tera-btn-reset">{{ __('ui.reset') }}</a>
                @if($renderApply)
                    <button type="submit" class="tera-btn tera-btn-primary">{{ __('ui.apply') }}</button>
                @endif
                <span class="text-xs text-slate-500" x-show="loading" x-cloak>{{ __('ui.processing') }}</span>
            </div>
        </div>
    </div>
</form>
