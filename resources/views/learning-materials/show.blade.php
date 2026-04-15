@extends('layouts.app', ['title' => __('ui.learning_material_detail')])

@section('content')
    <x-ui.page-header :title="$learningMaterial->title" :subtitle="__('ui.learning_material_detail_subtitle')" />

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="tera-card">
                <div class="tera-card-body space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="tera-badge bg-slate-100 text-slate-700">{{ __('ui.material_type_'.$learningMaterial->type) }}</span>
                        <span class="tera-badge {{ $learningMaterial->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                            {{ $learningMaterial->is_published ? __('ui.published') : __('ui.draft') }}
                        </span>
                    </div>
                    @if($learningMaterial->description)
                        <p class="text-sm text-slate-700">{{ $learningMaterial->description }}</p>
                    @endif

                    @if($learningMaterial->content)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 whitespace-pre-line">{{ $learningMaterial->content }}</div>
                    @endif

                    @if($learningMaterial->external_url)
                        <a href="{{ $learningMaterial->external_url }}" target="_blank" class="tera-btn tera-btn-outline">
                            <i data-lucide="external-link" class="w-4 h-4"></i>{{ __('ui.open_external_link') }}
                        </a>
                    @endif

                    @if($learningMaterial->file_url)
                        <a href="{{ $learningMaterial->file_url }}" target="_blank" class="tera-btn tera-btn-muted">
                            <i data-lucide="download" class="w-4 h-4"></i>{{ $learningMaterial->file_name ?? __('ui.download_file') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="tera-card">
                <div class="tera-card-body space-y-2 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.course') }}</span>
                        <span class="font-semibold text-slate-800 text-right">{{ $learningMaterial->course?->title ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.subjects') }}</span>
                        <span class="font-semibold text-slate-800 text-right">{{ $learningMaterial->course?->subject?->name_id ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.classes') }}</span>
                        <span class="font-semibold text-slate-800 text-right">{{ $learningMaterial->course?->schoolClass?->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.sort_order') }}</span>
                        <span class="font-semibold text-slate-800">{{ $learningMaterial->sort_order }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.published_at') }}</span>
                        <span class="font-semibold text-slate-800">{{ optional($learningMaterial->published_at)->format('d M Y H:i') ?: '-' }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('learning-materials.index') }}" class="tera-btn tera-btn-muted w-full">{{ __('ui.back') }}</a>
        </div>
    </div>
@endsection

