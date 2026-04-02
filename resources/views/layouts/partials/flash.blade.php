@if(session('success'))
    <div class="tera-card border-emerald-200 bg-emerald-50/80">
        <div class="tera-card-body flex items-center gap-3 text-emerald-700">
            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="tera-card border-red-200 bg-red-50/80">
        <div class="tera-card-body flex items-center gap-3 text-red-700">
            <i data-lucide="triangle-alert" class="w-5 h-5"></i>
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
    </div>
@endif

@php
    $flashErrorMessage = null;

    if (is_object($errors) && method_exists($errors, 'any') && $errors->any()) {
        $flashErrorMessage = $errors->first();
    } elseif (is_array($errors) && !empty($errors)) {
        $first = reset($errors);
        $flashErrorMessage = is_array($first) ? (string) reset($first) : (string) $first;
    }
@endphp

@if(!empty($flashErrorMessage))
    <div class="tera-card border-amber-200 bg-amber-50/80">
        <div class="tera-card-body flex items-center gap-3 text-amber-800">
            <i data-lucide="circle-alert" class="w-5 h-5"></i>
            <p class="text-sm font-medium">{{ $flashErrorMessage }}</p>
        </div>
    </div>
@endif
