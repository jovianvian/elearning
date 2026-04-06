@csrf
@php($selectedQuestionIds = collect(old('question_ids', isset($exam) ? $exam->examQuestions->pluck('question_id')->all() : []))->map(fn($id)=>(int)$id)->all())
@php($selectedQuestions = $questions->filter(fn($q) => in_array((int) $q->id, $selectedQuestionIds, true)))
@php($selectedBankCandidates = $selectedQuestions->pluck('question_bank_id')->filter()->unique()->values())
@php($selectedBankId = old('question_bank_id', $selectedBankCandidates->count() === 1 ? (int) $selectedBankCandidates->first() : ''))
@php($scoringMode = old('scoring_mode', 'auto'))
@php($questionPoints = old('question_points', isset($exam) ? $exam->examQuestions->pluck('points', 'question_id')->all() : []))

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

<div class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-4">
    <h3 class="font-semibold text-sm text-slate-700">Automatic Scoring Distribution</h3>
    <div>
        <label class="tera-label">Scoring Mode</label>
        <div class="flex flex-wrap gap-4 text-sm">
            <label class="inline-flex items-center gap-2">
                <input type="radio" name="scoring_mode" value="auto" @checked($scoringMode === 'auto') class="rounded border-slate-300">
                <span>Auto (recommended)</span>
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="radio" name="scoring_mode" value="manual" @checked($scoringMode === 'manual') class="rounded border-slate-300">
                <span>Manual per question</span>
            </label>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
        <div>
            <label class="tera-label">Target Final Score</label>
            <input type="number" step="0.01" min="1" max="1000" name="target_score" id="target-score"
                   value="{{ old('target_score', $exam->target_score ?? 100) }}" class="tera-input">
        </div>
        <div>
            <label class="tera-label">Objective Weight (%)</label>
            <input type="number" step="0.01" min="0" max="100" name="objective_weight_percent" id="objective-weight"
                   value="{{ old('objective_weight_percent', $exam->objective_weight_percent ?? 60) }}" class="tera-input">
        </div>
        <div>
            <label class="tera-label">Essay Weight (%)</label>
            <input type="number" step="0.01" min="0" max="100" name="essay_weight_percent" id="essay-weight"
                   value="{{ old('essay_weight_percent', $exam->essay_weight_percent ?? 40) }}" class="tera-input">
        </div>
    </div>

    <div id="scoring-suggestion" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-800 leading-5">
        Select questions to see suggested points for objective and essay items.
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
    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
        <h3 class="font-semibold">Question Selection</h3>
        <div class="inline-flex items-center gap-2">
            <button type="button" id="select-all-visible" class="tera-btn tera-btn-muted !px-3 !py-1.5 text-xs">Select All Visible</button>
            <button type="button" id="clear-all-visible" class="tera-btn tera-btn-muted !px-3 !py-1.5 text-xs">Clear</button>
        </div>
    </div>
    <div class="max-h-72 overflow-auto border border-slate-200 rounded-xl divide-y">
        @foreach($questions as $question)
            <label class="question-item flex items-start gap-3 p-3 text-sm hover:bg-slate-50 transition-colors"
                   data-question-bank-id="{{ $question->question_bank_id }}"
                   data-question-type="{{ $question->type }}">
                <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" @checked(in_array($question->id, $selectedQuestionIds, true)) class="mt-1 rounded border-slate-300">
                <div>
                    <div class="font-medium">{{ \Illuminate\Support\Str::limit($question->question_text, 120) }}</div>
                    <div class="text-xs text-slate-500 mt-1">
                        {{ $question->subject?->name_id }} | Bank: {{ $question->bank?->title ?? '-' }} | {{ $question->type }} | {{ $question->difficulty }} | {{ $question->points }} pts
                    </div>
                    <div class="mt-2">
                        <label class="text-xs font-medium text-slate-600">Point</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0.1"
                            name="question_points[{{ $question->id }}]"
                            value="{{ $questionPoints[$question->id] ?? $question->points }}"
                            class="tera-input mt-1 manual-point-input"
                            data-question-id="{{ $question->id }}"
                        >
                        @error("question_points.{$question->id}")
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
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
        const examTypeField = document.querySelector('select[name="exam_type"]');
        const targetScoreField = document.getElementById('target-score');
        const objectiveWeightField = document.getElementById('objective-weight');
        const essayWeightField = document.getElementById('essay-weight');
        const scoringSuggestion = document.getElementById('scoring-suggestion');
        const scoringModeFields = Array.from(document.querySelectorAll('input[name="scoring_mode"]'));
        const selectAllVisibleButton = document.getElementById('select-all-visible');
        const clearAllVisibleButton = document.getElementById('clear-all-visible');
        const pointInputs = Array.from(document.querySelectorAll('.manual-point-input'));
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
            renderScoringSuggestion();
            syncManualPointInputsState();
        }

        function getCheckedVisibleItems() {
            return items.filter((item) => !item.classList.contains('hidden'))
                .filter((item) => {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    return checkbox && checkbox.checked;
                });
        }

        function distribute(total, count) {
            if (!count || count <= 0) return 0;
            return Math.round((total / count) * 100) / 100;
        }

        function renderScoringSuggestion() {
            if (!scoringSuggestion) return;

            const checkedItems = getCheckedVisibleItems();
            const objectiveCount = checkedItems.filter((item) => item.dataset.questionType !== 'essay').length;
            const essayCount = checkedItems.filter((item) => item.dataset.questionType === 'essay').length;
            const totalCount = objectiveCount + essayCount;

            if (totalCount === 0) {
                scoringSuggestion.textContent = 'Select questions to see suggested points for objective and essay items.';
                return;
            }

            const examType = examTypeField ? examTypeField.value : 'mixed';
            const targetScore = Math.max(1, parseFloat(targetScoreField?.value || '100'));
            let objectiveWeight = Math.max(0, Math.min(100, parseFloat(objectiveWeightField?.value || '60')));
            let essayWeight = Math.max(0, Math.min(100, parseFloat(essayWeightField?.value || '40')));

            if (examType === 'objective') {
                objectiveWeight = 100;
                essayWeight = 0;
            } else if (examType === 'essay') {
                objectiveWeight = 0;
                essayWeight = 100;
            } else {
                if (objectiveCount === 0) {
                    objectiveWeight = 0;
                    essayWeight = 100;
                } else if (essayCount === 0) {
                    objectiveWeight = 100;
                    essayWeight = 0;
                }
            }

            const objectiveTotal = Math.round((targetScore * (objectiveWeight / 100)) * 100) / 100;
            const essayTotal = Math.round((targetScore * (essayWeight / 100)) * 100) / 100;
            const objectivePerQuestion = distribute(objectiveTotal, objectiveCount);
            const essayPerQuestion = distribute(essayTotal, essayCount);

            scoringSuggestion.innerHTML =
                `Selected: <strong>${totalCount}</strong> questions ` +
                `(Objective/Short: <strong>${objectiveCount}</strong>, Essay: <strong>${essayCount}</strong>). ` +
                `Suggested points -> Objective/Short: <strong>${objectivePerQuestion}</strong> each, ` +
                `Essay: <strong>${essayPerQuestion}</strong> each. ` +
                `Target final score: <strong>${targetScore}</strong>.`;
        }

        function getScoringMode() {
            const picked = scoringModeFields.find((field) => field.checked);
            return picked ? picked.value : 'auto';
        }

        function syncManualPointInputsState() {
            const isManual = getScoringMode() === 'manual';
            pointInputs.forEach((input) => {
                const questionId = input.dataset.questionId;
                const item = items.find((it) => {
                    const cb = it.querySelector('input[type="checkbox"]');
                    return cb && cb.value === questionId;
                });
                const checkbox = item ? item.querySelector('input[type="checkbox"]') : null;
                const isVisible = item ? !item.classList.contains('hidden') : false;
                const isChecked = checkbox ? checkbox.checked : false;
                input.disabled = !isManual || !isVisible || !isChecked;
                input.classList.toggle('opacity-60', input.disabled);
            });
        }

        syncQuestionVisibility();
        bankFilter.addEventListener('change', syncQuestionVisibility);

        items.forEach((item) => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (!checkbox) return;
            checkbox.addEventListener('change', renderScoringSuggestion);
            checkbox.addEventListener('change', syncManualPointInputsState);
        });

        [examTypeField, targetScoreField, objectiveWeightField, essayWeightField].forEach((field) => {
            if (!field) return;
            field.addEventListener('input', renderScoringSuggestion);
            field.addEventListener('change', renderScoringSuggestion);
        });

        scoringModeFields.forEach((field) => {
            field.addEventListener('change', () => {
                renderScoringSuggestion();
                syncManualPointInputsState();
            });
        });

        if (selectAllVisibleButton) {
            selectAllVisibleButton.addEventListener('click', () => {
                items.forEach((item) => {
                    if (item.classList.contains('hidden')) return;
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = true;
                });
                renderScoringSuggestion();
                syncManualPointInputsState();
            });
        }

        if (clearAllVisibleButton) {
            clearAllVisibleButton.addEventListener('click', () => {
                items.forEach((item) => {
                    if (item.classList.contains('hidden')) return;
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = false;
                });
                renderScoringSuggestion();
                syncManualPointInputsState();
            });
        }

        renderScoringSuggestion();
        syncManualPointInputsState();
    })();
</script>
