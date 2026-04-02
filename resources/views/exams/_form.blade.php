@csrf
@php($selectedQuestionIds = collect(old('question_ids', isset($exam) ? $exam->examQuestions->pluck('question_id')->all() : []))->map(fn($id)=>(int)$id)->all())
@php($selectedQuestions = $questions->filter(fn($q) => in_array((int) $q->id, $selectedQuestionIds, true)))
@php($selectedBankCandidates = $selectedQuestions->pluck('question_bank_id')->filter()->unique()->values())
@php($selectedBankId = old('question_bank_id', $selectedBankCandidates->count() === 1 ? (int) $selectedBankCandidates->first() : ''))

<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="tera-label">Course</label>
        <select name="course_id" class="tera-select" required>
            <option value="">Select course</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}" @selected(old('course_id', $exam->course_id ?? null) == $course->id)>
                    {{ $course->title }} - {{ $course->schoolClass?->name }} - {{ $course->semester?->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="tera-label">Title</label>
        <input name="title" value="{{ old('title', $exam->title ?? '') }}" class="tera-input" required>
    </div>
</div>

<div class="mt-4">
    <label class="tera-label">Description</label>
    <textarea name="description" rows="3" class="tera-input">{{ old('description', $exam->description ?? '') }}</textarea>
</div>

<div class="mt-4 grid md:grid-cols-4 gap-4">
    <div>
        <label class="tera-label">Exam Type</label>
        @php($examType = old('exam_type', $exam->exam_type ?? 'mixed'))
        <select name="exam_type" class="tera-select">
            <option value="objective" @selected($examType === 'objective')>Objective</option>
            <option value="essay" @selected($examType === 'essay')>Essay</option>
            <option value="mixed" @selected($examType === 'mixed')>Mixed</option>
        </select>
    </div>
    <div>
        <label class="tera-label">Start At</label>
        <input type="datetime-local" name="start_at" value="{{ old('start_at', isset($exam) && $exam->start_at ? $exam->start_at->format('Y-m-d\TH:i') : '') }}" class="tera-input" required>
    </div>
    <div>
        <label class="tera-label">End At</label>
        <input type="datetime-local" name="end_at" value="{{ old('end_at', isset($exam) && $exam->end_at ? $exam->end_at->format('Y-m-d\TH:i') : '') }}" class="tera-input" required>
    </div>
    <div>
        <label class="tera-label">Duration (minutes)</label>
        <input type="number" min="5" max="300" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes ?? 60) }}" class="tera-input" required>
    </div>
</div>

<div class="mt-4 grid md:grid-cols-3 gap-4">
    <div>
        <label class="tera-label">Status</label>
        @php($status = old('status', $exam->status ?? 'draft'))
        <select name="status" class="tera-select">
            @foreach(['draft','scheduled','active','closed','archived'] as $value)
                <option value="{{ $value }}" @selected($status === $value)>{{ ucfirst($value) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="tera-label">Max Attempts</label>
        <input type="number" min="1" max="5" name="max_attempts" value="{{ old('max_attempts', $exam->max_attempts ?? 1) }}" class="tera-input">
    </div>
</div>

<div class="mt-4">
    <label class="tera-label">Question Bank Filter</label>
    <select name="question_bank_id" id="question-bank-filter" class="tera-select">
        <option value="">All accessible banks</option>
        @foreach($questionBanks as $bank)
            <option value="{{ $bank->id }}" @selected((string) $selectedBankId === (string) $bank->id)>
                {{ $bank->title }} - {{ $bank->subject->name_id ?? '-' }} ({{ $bank->questions_count }} soal)
            </option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-slate-500">Pilih bank untuk mempermudah seleksi soal hasil import bank kamu.</p>
</div>

<div class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-4">
    <h3 class="font-semibold text-sm text-slate-700 mb-3">Exam Options</h3>
    <div class="grid md:grid-cols-3 gap-3 text-sm">
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="shuffle_questions" value="1" @checked(old('shuffle_questions', $exam->shuffle_questions ?? false)) class="rounded border-slate-300"> Shuffle Questions</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="shuffle_options" value="1" @checked(old('shuffle_options', $exam->shuffle_options ?? false)) class="rounded border-slate-300"> Shuffle Options</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="auto_submit" value="1" @checked(old('auto_submit', $exam->auto_submit ?? true)) class="rounded border-slate-300"> Auto Submit</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="show_result_after_submit" value="1" @checked(old('show_result_after_submit', $exam->show_result_after_submit ?? false)) class="rounded border-slate-300"> Show Result Setting</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="show_answer_key" value="1" @checked(old('show_answer_key', $exam->show_answer_key ?? false)) class="rounded border-slate-300"> Show Answer Key</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="show_explanation" value="1" @checked(old('show_explanation', $exam->show_explanation ?? false)) class="rounded border-slate-300"> Show Explanation</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $exam->is_published ?? false)) class="rounded border-slate-300"> Exam Published</label>
    </div>
</div>

<div class="mt-6">
    <h3 class="font-semibold mb-2">Question Selection</h3>
    <div class="max-h-72 overflow-auto border border-slate-200 rounded-xl divide-y">
        @foreach($questions as $question)
            <label class="question-item flex items-start gap-3 p-3 text-sm hover:bg-slate-50 transition-colors"
                   data-question-bank-id="{{ $question->question_bank_id }}">
                <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" @checked(in_array($question->id, $selectedQuestionIds, true)) class="mt-1 rounded border-slate-300">
                <div>
                    <div class="font-medium">{{ \Illuminate\Support\Str::limit($question->question_text, 120) }}</div>
                    <div class="text-xs text-slate-500 mt-1">
                        {{ $question->subject?->name_id }} | Bank: {{ $question->bank?->title ?? '-' }} | {{ $question->type }} | {{ $question->difficulty }} | {{ $question->points }} pts
                    </div>
                </div>
            </label>
        @endforeach
    </div>
</div>

<div class="mt-6 flex gap-2">
    <button class="tera-btn tera-btn-primary">{{ $buttonLabel }}</button>
    <a href="{{ route('exams.index') }}" class="tera-btn tera-btn-muted">Cancel</a>
</div>

<script>
    (function () {
        const bankFilter = document.getElementById('question-bank-filter');
        const items = Array.from(document.querySelectorAll('.question-item'));
        if (!bankFilter || items.length === 0) return;

        function syncQuestionVisibility() {
            const selectedBank = bankFilter.value;
            items.forEach((item) => {
                const belongsToBank = !selectedBank || item.dataset.questionBankId === selectedBank;
                item.classList.toggle('hidden', !belongsToBank);
                if (!belongsToBank) {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = false;
                }
            });
        }

        syncQuestionVisibility();
        bankFilter.addEventListener('change', syncQuestionVisibility);
    })();
</script>
