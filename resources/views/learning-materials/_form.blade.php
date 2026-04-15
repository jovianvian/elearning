@csrf

<div x-data="{ materialType: @js(old('type', optional($learningMaterial)->type ?? \App\Models\LearningMaterial::TYPE_TEXT)) }" class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="tera-label">{{ __('ui.course') }}</label>
            <select name="course_id" class="tera-select" required>
                <option value="">{{ __('ui.select_course') }}</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" @selected((string) old('course_id', optional($learningMaterial)->course_id ?? '') === (string) $course->id)>
                        {{ $course->title }} - {{ $course->schoolClass?->name }} / {{ $course->subject?->name_id }}
                    </option>
                @endforeach
            </select>
            @error('course_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="tera-label">{{ __('ui.material_type') }}</label>
            <select name="type" class="tera-select" x-model="materialType" required>
                <option value="{{ \App\Models\LearningMaterial::TYPE_TEXT }}">{{ __('ui.material_type_text') }}</option>
                <option value="{{ \App\Models\LearningMaterial::TYPE_FILE }}">{{ __('ui.material_type_file') }}</option>
                <option value="{{ \App\Models\LearningMaterial::TYPE_LINK }}">{{ __('ui.material_type_link') }}</option>
                <option value="{{ \App\Models\LearningMaterial::TYPE_VIDEO }}">{{ __('ui.material_type_video') }}</option>
            </select>
            @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="tera-label">{{ __('ui.title') }}</label>
            <input type="text" name="title" class="tera-input" value="{{ old('title', optional($learningMaterial)->title ?? '') }}" required>
            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="tera-label">{{ __('ui.sort_order') }}</label>
            <input type="number" min="0" name="sort_order" class="tera-input" value="{{ old('sort_order', optional($learningMaterial)->sort_order ?? 0) }}">
            @error('sort_order') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="tera-label">{{ __('ui.description') }}</label>
        <textarea name="description" rows="3" class="tera-textarea">{{ old('description', optional($learningMaterial)->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div x-show="materialType === '{{ \App\Models\LearningMaterial::TYPE_TEXT }}'" x-cloak>
        <label class="tera-label">{{ __('ui.material_content') }}</label>
        <textarea name="content" rows="8" class="tera-textarea">{{ old('content', optional($learningMaterial)->content ?? '') }}</textarea>
        @error('content') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div x-show="materialType === '{{ \App\Models\LearningMaterial::TYPE_LINK }}' || materialType === '{{ \App\Models\LearningMaterial::TYPE_VIDEO }}'" x-cloak>
        <label class="tera-label">{{ __('ui.external_url') }}</label>
        <input type="url" name="external_url" class="tera-input" value="{{ old('external_url', optional($learningMaterial)->external_url ?? '') }}" placeholder="https://">
        @error('external_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div x-show="materialType === '{{ \App\Models\LearningMaterial::TYPE_FILE }}'" x-cloak>
        <label class="tera-label">{{ __('ui.upload_file') }}</label>
        <input type="file" name="upload_file" class="tera-input" />
        @error('upload_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        @if(!empty(optional($learningMaterial)->file_url))
            <div class="mt-2 text-xs text-slate-600">
                {{ __('ui.current_file') }}:
                <a href="{{ optional($learningMaterial)->file_url }}" target="_blank" class="text-primary underline">{{ optional($learningMaterial)->file_name ?? __('ui.file') }}</a>
            </div>
            <label class="mt-2 inline-flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox" name="remove_file" value="1" @checked((bool) old('remove_file'))>
                <span>{{ __('ui.remove_current_file') }}</span>
            </label>
        @endif
    </div>

    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="is_published" value="1" @checked((bool) old('is_published', optional($learningMaterial)->is_published ?? false))>
        <span>{{ __('ui.publish_now') }}</span>
    </label>

    <div class="flex items-center justify-end gap-2">
        <a href="{{ route('learning-materials.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.cancel') }}</a>
        <button type="submit" class="tera-btn tera-btn-primary">{{ $submitLabel }}</button>
    </div>
</div>
