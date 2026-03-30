@csrf

<div class="grid md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm mb-1">Type</label>
        <select name="type" id="question-type" class="w-full rounded-lg border-slate-300" required>
            @php($currentType = old('type', $question->type ?? 'multiple_choice'))
            <option value="multiple_choice" @selected($currentType === 'multiple_choice')>Multiple Choice</option>
            <option value="short_answer" @selected($currentType === 'short_answer')>Short Answer</option>
            <option value="essay" @selected($currentType === 'essay')>Essay</option>
        </select>
    </div>

    <div>
        <label class="block text-sm mb-1">Points</label>
        <input type="number" name="points" step="0.1" min="0.1" value="{{ old('points', $question->points ?? 1) }}" class="w-full rounded-lg border-slate-300" required>
    </div>

    <div>
        <label class="block text-sm mb-1">Difficulty</label>
        @php($difficulty = old('difficulty', $question->difficulty ?? 'medium'))
        <select name="difficulty" class="w-full rounded-lg border-slate-300">
            <option value="easy" @selected($difficulty === 'easy')>Easy</option>
            <option value="medium" @selected($difficulty === 'medium')>Medium</option>
            <option value="hard" @selected($difficulty === 'hard')>Hard</option>
        </select>
    </div>
</div>

<div class="mt-4">
    <label class="block text-sm mb-1">Question Text (ID)</label>
    <textarea name="question_text" rows="4" class="w-full rounded-lg border-slate-300" required>{{ old('question_text', $question->question_text ?? '') }}</textarea>
</div>

<div class="mt-4">
    <label class="block text-sm mb-1">Question Text (EN)</label>
    <textarea name="question_text_en" rows="3" class="w-full rounded-lg border-slate-300">{{ old('question_text_en', $question->question_text_en ?? '') }}</textarea>
</div>

<div id="short-answer-wrap" class="mt-4 hidden">
    <label class="block text-sm mb-1">Short Answer Key (Exact/Normalized)</label>
    <input name="short_answer_key" value="{{ old('short_answer_key', $question->short_answer_key ?? '') }}" class="w-full rounded-lg border-slate-300">
</div>

<div id="multiple-choice-wrap" class="mt-4 hidden">
    <label class="block text-sm mb-2">Multiple Choice Options</label>
    @php
        $oldOptions = old('options');
        $options = $oldOptions ?? ($question?->options?->map(fn($opt) => ['key' => $opt->option_key, 'text' => $opt->option_text])->toArray() ?? []);
        if (empty($options)) {
            $options = [
                ['key' => 'A', 'text' => ''],
                ['key' => 'B', 'text' => ''],
                ['key' => 'C', 'text' => ''],
                ['key' => 'D', 'text' => ''],
            ];
        }
        $correctOption = old('correct_option', optional($question?->options?->firstWhere('is_correct', true))->option_key);
    @endphp

    <div class="space-y-2">
        @foreach($options as $index => $option)
            <div class="grid grid-cols-12 gap-2">
                <div class="col-span-2">
                    <input name="options[{{ $index }}][key]" value="{{ $option['key'] ?? '' }}" class="w-full rounded-lg border-slate-300" placeholder="A">
                </div>
                <div class="col-span-10">
                    <input name="options[{{ $index }}][text]" value="{{ $option['text'] ?? '' }}" class="w-full rounded-lg border-slate-300" placeholder="Option text">
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3">
        <label class="block text-sm mb-1">Correct Option Key</label>
        <input name="correct_option" value="{{ $correctOption }}" class="w-full rounded-lg border-slate-300" placeholder="A">
    </div>
</div>

<div class="mt-4">
    <label class="block text-sm mb-1">Explanation (ID)</label>
    <textarea name="explanation" rows="3" class="w-full rounded-lg border-slate-300">{{ old('explanation', $question->explanation ?? '') }}</textarea>
</div>

<div class="mt-4">
    <label class="block text-sm mb-1">Explanation (EN)</label>
    <textarea name="explanation_en" rows="3" class="w-full rounded-lg border-slate-300">{{ old('explanation_en', $question->explanation_en ?? '') }}</textarea>
</div>

<label class="mt-4 inline-flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $question->is_active ?? true))>
    Active
</label>

<div class="mt-6 flex gap-2">
    <button class="px-4 py-2 bg-primary text-white rounded-lg text-sm">{{ $buttonLabel }}</button>
    <a href="{{ route('question-banks.show', $questionBankId) }}" class="px-4 py-2 border rounded-lg text-sm">Cancel</a>
</div>

<script>
    (function () {
        const typeInput = document.getElementById('question-type');
        const shortWrap = document.getElementById('short-answer-wrap');
        const mcWrap = document.getElementById('multiple-choice-wrap');

        function syncVisibility() {
            const current = typeInput.value;
            shortWrap.classList.toggle('hidden', current !== 'short_answer');
            mcWrap.classList.toggle('hidden', current !== 'multiple_choice');
        }

        syncVisibility();
        typeInput.addEventListener('change', syncVisibility);
    })();
</script>

