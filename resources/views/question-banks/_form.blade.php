@csrf

<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="tera-label">Subject</label>
        <select name="subject_id" class="tera-select">
            <option value="">Select subject</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected(old('subject_id', $questionBank->subject_id ?? null) == $subject->id)>
                    {{ $subject->name_id }} ({{ $subject->code }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="tera-label">Visibility</label>
        <select name="visibility" class="tera-select">
            <option value="subject_shared" @selected(old('visibility', $questionBank->visibility ?? 'subject_shared') === 'subject_shared')>Subject Shared</option>
            <option value="private" @selected(old('visibility', $questionBank->visibility ?? 'subject_shared') === 'private')>Private</option>
        </select>
    </div>
</div>

<div class="mt-4">
    <label class="tera-label">Title</label>
    <input name="title" value="{{ old('title', $questionBank->title ?? '') }}" class="tera-input" required>
</div>

<div class="mt-4">
    <label class="tera-label">Description</label>
    <textarea name="description" class="tera-textarea" rows="4">{{ old('description', $questionBank->description ?? '') }}</textarea>
</div>

<div class="mt-6 flex gap-2">
    <button class="tera-btn tera-btn-primary">{{ $buttonLabel }}</button>
    <a href="{{ route('question-banks.index') }}" class="tera-btn tera-btn-muted">Cancel</a>
</div>
