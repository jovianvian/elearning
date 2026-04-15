@csrf
@php($currentType = old('type', $question->type ?? 'multiple_choice'))
@php($isSingleChoice = $currentType === 'multiple_choice')
@php($isMultiResponse = $currentType === 'multiple_response')
@php($isObjectiveOptionType = in_array($currentType, ['multiple_choice', 'multiple_response'], true))

<div class="grid md:grid-cols-3 gap-4">
    <div>
        <label class="tera-label">{{ __('ui.type') }}</label>
        <select name="type" id="question-type" class="tera-select" required>
            <option value="multiple_choice" @selected($isSingleChoice)>{{ __('ui.question_type_single_choice') }}</option>
            <option value="multiple_response" @selected($isMultiResponse)>{{ __('ui.question_type_multiple_response') }}</option>
            <option value="short_answer" @selected($currentType === 'short_answer')>{{ __('ui.question_type_short_answer') }}</option>
            <option value="essay" @selected($currentType === 'essay')>{{ __('ui.question_type_essay') }}</option>
        </select>
    </div>

    <div>
        <label class="tera-label">{{ __('ui.points') }}</label>
        <input type="number" name="points" step="0.1" min="0.1" value="{{ old('points', $question->points ?? 1) }}" class="tera-input" required>
    </div>

    <div>
        <label class="tera-label">{{ __('ui.difficulty') }}</label>
        @php($difficulty = old('difficulty', $question->difficulty ?? 'medium'))
        <select name="difficulty" class="tera-select">
            <option value="easy" @selected($difficulty === 'easy')>{{ __('ui.difficulty_easy') }}</option>
            <option value="medium" @selected($difficulty === 'medium')>{{ __('ui.difficulty_medium') }}</option>
            <option value="hard" @selected($difficulty === 'hard')>{{ __('ui.difficulty_hard') }}</option>
        </select>
    </div>
</div>

<div class="mt-4">
    <label class="tera-label">{{ __('ui.question_text_id') }}</label>
    <textarea name="question_text" rows="4" class="tera-input" required>{{ old('question_text', $question->question_text ?? '') }}</textarea>
</div>

<div class="mt-4">
    <label class="tera-label">{{ __('ui.question_text_en') }}</label>
    <textarea name="question_text_en" rows="3" class="tera-input">{{ old('question_text_en', $question->question_text_en ?? '') }}</textarea>
</div>

<div class="mt-4 grid md:grid-cols-2 gap-4">
    <div>
        <label class="tera-label">{{ __('ui.question_image_url_path_optional') }}</label>
        <input name="question_image_path" value="{{ old('question_image_path', $question->question_image_path ?? '') }}" class="tera-input" placeholder="https://... atau /storage/questions/xxx.jpg">
        <p class="mt-1 text-xs text-slate-500">{{ __('ui.question_image_path_helper') }}</p>
    </div>
    <div>
        <label class="tera-label">{{ __('ui.upload_question_image_optional') }}</label>
        <input type="file" name="question_image_file" class="tera-input" accept="image/*">
        <p class="mt-1 text-xs text-slate-500">{{ __('ui.question_image_upload_helper') }}</p>
    </div>
</div>

@if(!empty($question?->image_url))
    <div class="mt-3">
        <div class="text-xs font-semibold text-slate-600 mb-1">{{ __('ui.current_image') }}</div>
        <img src="{{ $question->image_url }}" alt="Question image" class="max-h-44 rounded-lg border border-slate-200 bg-white object-contain">
    </div>
@endif

<div id="short-answer-wrap" class="mt-4 hidden">
    <label class="tera-label">{{ __('ui.short_answer_key_normalized') }}</label>
    <input name="short_answer_key" value="{{ old('short_answer_key', $question->short_answer_key ?? '') }}" class="tera-input">
    <p class="mt-1 text-xs text-slate-500">{{ __('ui.short_answer_key_helper') }}</p>
</div>

<div id="multiple-choice-wrap" class="mt-4 hidden bg-slate-50 border border-slate-200 rounded-xl p-4">
    <label class="tera-label mb-2">{{ __('ui.objective_options') }}</label>
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
        $correctOptions = collect(old('correct_options', $question?->options?->where('is_correct', true)?->pluck('option_key')?->all() ?? []))
            ->map(fn ($value) => strtoupper((string) $value))
            ->values()
            ->all();
    @endphp

    <div class="space-y-2">
        @foreach($options as $index => $option)
            <div class="grid grid-cols-12 gap-2">
                <div class="col-span-2">
                    <input name="options[{{ $index }}][key]" value="{{ $option['key'] ?? '' }}" class="tera-input" placeholder="A">
                </div>
                <div class="col-span-10">
                    <input name="options[{{ $index }}][text]" value="{{ $option['text'] ?? '' }}" class="tera-input" placeholder="Option text">
                </div>
            </div>
        @endforeach
    </div>

    <div id="single-choice-correct-wrap" class="mt-3">
        <label class="tera-label">{{ __('ui.correct_option_key') }}</label>
        <input name="correct_option" value="{{ $correctOption }}" class="tera-input" placeholder="A">
        @error('correct_option')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div id="multi-response-correct-wrap" class="mt-3 hidden">
        <label class="tera-label">{{ __('ui.correct_options_multi_select') }}</label>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm">
            @foreach(['A','B','C','D','E'] as $candidateKey)
                <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 bg-white">
                    <input
                        type="checkbox"
                        name="correct_options[]"
                        value="{{ $candidateKey }}"
                        @checked(in_array($candidateKey, $correctOptions, true))
                    >
                    <span>{{ $candidateKey }}</span>
                </label>
            @endforeach
        </div>
        @error('correct_options')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-4">
    <label class="tera-label">{{ __('ui.explanation_id') }}</label>
    <textarea name="explanation" rows="3" class="tera-input">{{ old('explanation', $question->explanation ?? '') }}</textarea>
</div>

<div class="mt-4">
    <label class="tera-label">{{ __('ui.explanation_en') }}</label>
    <textarea name="explanation_en" rows="3" class="tera-input">{{ old('explanation_en', $question->explanation_en ?? '') }}</textarea>
</div>

<label class="mt-4 inline-flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $question->is_active ?? true)) class="rounded border-slate-300">
    {{ __('ui.active') }}
</label>

<div class="mt-6 flex gap-2">
    <button class="tera-btn tera-btn-primary">{{ $buttonLabel }}</button>
    <a href="{{ route('question-banks.show', $questionBankId) }}" class="tera-btn tera-btn-muted">{{ __('ui.cancel') }}</a>
</div>

<script>
    (function () {
        const typeInput = document.getElementById('question-type');
        const shortWrap = document.getElementById('short-answer-wrap');
        const mcWrap = document.getElementById('multiple-choice-wrap');
        const singleCorrectWrap = document.getElementById('single-choice-correct-wrap');
        const multiCorrectWrap = document.getElementById('multi-response-correct-wrap');

        function syncVisibility() {
            const current = typeInput.value;
            shortWrap.classList.toggle('hidden', current !== 'short_answer');
            mcWrap.classList.toggle('hidden', !['multiple_choice', 'multiple_response'].includes(current));
            singleCorrectWrap.classList.toggle('hidden', current !== 'multiple_choice');
            multiCorrectWrap.classList.toggle('hidden', current !== 'multiple_response');
        }

        syncVisibility();
        typeInput.addEventListener('change', syncVisibility);
    })();
</script>
