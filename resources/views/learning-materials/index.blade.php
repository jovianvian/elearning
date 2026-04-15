@extends('layouts.app', ['title' => __('ui.learning_materials')])

@section('content')
<div x-data data-async-list data-fragment="#materials-table-fragment">
    <x-ui.page-header :title="__('ui.learning_material_management_title')" :subtitle="__('ui.learning_material_management_subtitle')">
        <x-slot:actions>
            @if($canManage)
                <a href="{{ route('learning-materials.create') }}" class="tera-btn tera-btn-primary">
                    <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.add_learning_material') }}
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_material_or_course')">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.course') }}</label>
                <select name="course_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected((string) request('course_id') === (string) $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.material_type') }}</label>
                <select name="type" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach(\App\Models\LearningMaterial::availableTypes() as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ __('ui.material_type_'.$type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.status') }}</label>
                <select name="is_published" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    <option value="1" @selected(request('is_published') === '1')>{{ __('ui.published') }}</option>
                    <option value="0" @selected(request('is_published') === '0')>{{ __('ui.draft') }}</option>
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div id="materials-table-fragment">
        <div class="tera-table-wrap">
            <table class="tera-table">
                <thead>
                <tr>
                    <th>{{ __('ui.no') }}</th>
                    <th>{{ __('ui.title') }}</th>
                    <th>{{ __('ui.course') }}</th>
                    <th>{{ __('ui.material_type') }}</th>
                    <th>{{ __('ui.sort_order') }}</th>
                    <th>{{ __('ui.status') }}</th>
                    <th>{{ __('ui.published_at') }}</th>
                    <th>{{ __('ui.action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($materials as $material)
                    <tr>
                        <td>{{ $materials->firstItem() + $loop->index }}</td>
                        <td class="!text-left">
                            <div class="font-semibold text-slate-800">{{ $material->title }}</div>
                            <div class="text-xs text-slate-500">{{ $material->description ? \Illuminate\Support\Str::limit($material->description, 70) : '-' }}</div>
                        </td>
                        <td>{{ $material->course?->title ?? '-' }}</td>
                        <td><span class="tera-badge bg-slate-100 text-slate-700">{{ __('ui.material_type_'.$material->type) }}</span></td>
                        <td>{{ $material->sort_order }}</td>
                        <td>
                            <span class="tera-badge {{ $material->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                                {{ $material->is_published ? __('ui.published') : __('ui.draft') }}
                            </span>
                        </td>
                        <td>{{ optional($material->published_at)->format('d M Y H:i') ?: '-' }}</td>
                        <td>
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('learning-materials.show', $material) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">{{ __('ui.view') }}</a>
                                @if($canManage && auth()->user()->hasRole('super_admin', 'admin', 'teacher'))
                                    <a href="{{ route('learning-materials.edit', $material) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">{{ __('ui.edit') }}</a>
                                    <form method="POST" action="{{ route('learning-materials.toggle-publish', $material) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="tera-btn tera-btn-outline !px-3 !py-1.5">
                                            {{ $material->is_published ? __('ui.unpublish') : __('ui.publish') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('learning-materials.destroy', $material) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="tera-btn tera-btn-danger !px-3 !py-1.5">{{ __('ui.delete') }}</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-slate-500">{{ __('ui.no_learning_materials') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $materials->links() }}</div>
    </div>
</div>
@endsection

