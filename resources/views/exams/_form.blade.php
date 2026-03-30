@csrf

<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm mb-1">Course</label>
        <select name="course_id" class="w-full rounded-lg border-slate-300" required>
            <option value="">Select course</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}" @selected(old('course_id', $exam->course_id ?? null) == $course->id)>
                    {{ $course->title }} - {{ $course->schoolClass?->name }} - {{ $course->semester?->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm mb-1">Title</label>
        <input name="title" value="{{ old('title', $exam->title ?? '') }}" class="w-full rounded-lg border-slate-300" required>
    </div>
</div>

<div class="mt-4">
    <label class="block text-sm mb-1">Description</label>
    <textarea name="description" rows="3" class="w-full rounded-lg border-slate-300">{{ old('description', $exam->description ?? '') }}</textarea>
</div>

<div class="mt-4 grid md:grid-cols-4 gap-4">
    <div>
        <label class="block text-sm mb-1">Exam Type</label>
        @php($examType = old('exam_type', $exam->exam_type ?? 'mixed'))
        <select name="exam_type" class="w-full rounded-lg border-slate-300">
            <option value="objective" @selected($examType === 'objective')>Objective</option>
            <option value="essay" @selected($examType === 'essay')>Essay</option>
            <option value="mixed" @selected($examType === 'mixed')>Mixed</option>
        </select>
    </div>
    <div>
        <label class="block text-sm mb-1">Start At</label>
        <input type="datetime-local" name="start_at" value="{{ old('start_at', isset($exam) && $exam->start_at ? $exam->start_at->format('Y-m-d\TH:i') : '') }}" class="w-full rounded-lg border-slate-300" required>
    </div>
    <div>
        <label class="block text-sm mb-1">End At</label>
        <input type="datetime-local" name="end_at" value="{{ old('end_at', isset($exam) && $exam->end_at ? $exam->end_at->format('Y-m-d\TH:i') : '') }}" class="w-full rounded-lg border-slate-300" required>
    </div>
    <div>
        <label class="block text-sm mb-1">Duration (minutes)</label>
        <input type="number" min="5" max="300" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes ?? 60) }}" class="w-full rounded-lg border-slate-300" required>
    </div>
</div>

<div class="mt-4 grid md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm mb-1">Status</label>
        @php($status = old('status', $exam->status ?? 'draft'))
        <select name="status" class="w-full rounded-lg border-slate-300">
            @foreach(['draft','scheduled','active','closed','archived'] as $value)
                <option value="{{ $value }}" @selected($status === $value)>{{ ucfirst($value) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm mb-1">Max Attempts</label>
        <input type="number" min="1" max="5" name="max_attempts" value="{{ old('max_attempts', $exam->max_attempts ?? 1) }}" class="w-full rounded-lg border-slate-300">
    </div>
</div>

<div class="mt-4 grid md:grid-cols-3 gap-3 text-sm">
    <label class="inline-flex items-center gap-2"><input type="checkbox" name="shuffle_questions" value="1" @checked(old('shuffle_questions', $exam->shuffle_questions ?? false))> Shuffle Questions</label>
    <label class="inline-flex items-center gap-2"><input type="checkbox" name="shuffle_options" value="1" @checked(old('shuffle_options', $exam->shuffle_options ?? false))> Shuffle Options</label>
    <label class="inline-flex items-center gap-2"><input type="checkbox" name="auto_submit" value="1" @checked(old('auto_submit', $exam->auto_submit ?? true))> Auto Submit</label>
    <label class="inline-flex items-center gap-2"><input type="checkbox" name="show_result_after_submit" value="1" @checked(old('show_result_after_submit', $exam->show_result_after_submit ?? false))> Show Result Setting</label>
    <label class="inline-flex items-center gap-2"><input type="checkbox" name="show_answer_key" value="1" @checked(old('show_answer_key', $exam->show_answer_key ?? false))> Show Answer Key</label>
    <label class="inline-flex items-center gap-2"><input type="checkbox" name="show_explanation" value="1" @checked(old('show_explanation', $exam->show_explanation ?? false))> Show Explanation</label>
    <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $exam->is_published ?? false))> Exam Published</label>
</div>

<div class="mt-6">
    <h3 class="font-semibold mb-2">Question Selection</h3>
    @php($selectedQuestionIds = collect(old('question_ids', isset($exam) ? $exam->examQuestions->pluck('question_id')->all() : []))->map(fn($id)=>(int)$id)->all())
    <div class="max-h-72 overflow-auto border border-slate-200 rounded-lg divide-y">
        @foreach($questions as $question)
            <label class="flex items-start gap-3 p-3 text-sm">
                <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" @checked(in_array($question->id, $selectedQuestionIds, true)) class="mt-1">
                <div>
                    <div class="font-medium">{{ \Illuminate\Support\Str::limit($question->question_text, 120) }}</div>
                    <div class="text-xs text-slate-500 mt-1">
                        {{ $question->subject?->name_id }} | {{ $question->type }} | {{ $question->difficulty }} | {{ $question->points }} pts
                    </div>
                </div>
            </label>
        @endforeach
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="px-4 py-2 bg-primary text-white rounded-lg text-sm">{{ $buttonLabel }}</button>
    <a href="{{ route('exams.index') }}" class="px-4 py-2 rounded-lg border text-sm">Cancel</a>
</div>

