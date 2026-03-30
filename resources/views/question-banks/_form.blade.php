@csrf

<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm mb-1">Subject</label>
        <select name="subject_id" class="w-full rounded-lg border-slate-300">
            <option value="">Select subject</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected(old('subject_id', $questionBank->subject_id ?? null) == $subject->id)>
                    {{ $subject->name_id }} ({{ $subject->code }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm mb-1">Visibility</label>
        <select name="visibility" class="w-full rounded-lg border-slate-300">
            <option value="subject_shared" @selected(old('visibility', $questionBank->visibility ?? 'subject_shared') === 'subject_shared')>Subject Shared</option>
            <option value="private" @selected(old('visibility', $questionBank->visibility ?? 'subject_shared') === 'private')>Private</option>
        </select>
    </div>
</div>

<div class="mt-4">
    <label class="block text-sm mb-1">Title</label>
    <input name="title" value="{{ old('title', $questionBank->title ?? '') }}" class="w-full rounded-lg border-slate-300" required>
</div>

<div class="mt-4">
    <label class="block text-sm mb-1">Description</label>
    <textarea name="description" class="w-full rounded-lg border-slate-300" rows="4">{{ old('description', $questionBank->description ?? '') }}</textarea>
</div>

<div class="mt-6 flex gap-2">
    <button class="px-4 py-2 bg-primary text-white rounded-lg text-sm">{{ $buttonLabel }}</button>
    <a href="{{ route('question-banks.index') }}" class="px-4 py-2 rounded-lg border text-sm">Cancel</a>
</div>

