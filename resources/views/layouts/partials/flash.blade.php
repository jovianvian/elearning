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

@if($errors->any())
    <div class="tera-card border-amber-200 bg-amber-50/80">
        <div class="tera-card-body flex items-center gap-3 text-amber-800">
            <i data-lucide="circle-alert" class="w-5 h-5"></i>
            <p class="text-sm font-medium">{{ $errors->first() }}</p>
        </div>
    </div>
@endif

