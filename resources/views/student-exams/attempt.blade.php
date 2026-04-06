@extends('layouts.app', ['title' => __('ui.exam_attempt')])

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold">{{ $attempt->exam->title }}</h2>
                <p class="text-xs text-slate-500 mt-1">{{ $attempt->exam->course?->title }}</p>
            </div>
            <div class="px-3 py-2 rounded-lg bg-yellowx/20 text-amber-700 text-sm font-semibold">
                {{ __('ui.time_left') }}: <span id="countdown">--:--</span>
            </div>
        </div>
    </div>

    <form id="attempt-form" method="POST" action="{{ route('student-exams.attempt.submit', $attempt) }}" class="space-y-4">
        @csrf
        @foreach($questions as $answer)
            @php($question = $answer->question)
            <div class="bg-white rounded-xl border border-slate-200 p-3.5 sm:p-4">
                <div class="font-medium">{{ $loop->iteration }}. {{ $question?->question_text }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ ucfirst($question?->type) }} | {{ $question?->points }} pts</div>

                @if($question?->type === 'multiple_choice')
                    @php($options = $question->options->values())
                    @if($attempt->exam->shuffle_options)
                        @php($options = $options->sortBy(fn($opt) => md5($attempt->id.'-'.$question->id.'-'.$opt->id))->values())
                    @endif
                    <div class="mt-3 space-y-2">
                        @foreach($options as $option)
                            <label class="flex items-start gap-2 text-sm">
                                <input type="radio" name="answers[{{ $question->id }}][selected_option_id]" value="{{ $option->id }}" @checked((int)$answer->selected_option_id === (int)$option->id)>
                                <span>{{ $option->option_key }}. {{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="mt-3">
                        <textarea
                            name="answers[{{ $question->id }}][answer_text]"
                            rows="{{ $question?->type === 'essay' ? 5 : 2 }}"
                            class="w-full rounded-lg border-slate-300"
                            placeholder="{{ __('ui.type_your_answer') }}"
                        >{{ $answer->answer_text }}</textarea>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="flex flex-col sm:flex-row gap-2">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm w-full sm:w-auto">{{ __('ui.submit_exam') }}</button>
            <button type="button" id="save-btn" class="px-4 py-2 border rounded-lg text-sm w-full sm:w-auto">{{ __('ui.save_draft') }}</button>
        </div>
    </form>

    <form id="save-form" method="POST" action="{{ route('student-exams.attempt.save', $attempt) }}" class="hidden">
        @csrf
    </form>

    <script>
        (function () {
            const attemptForm = document.getElementById('attempt-form');
            const saveForm = document.getElementById('save-form');
            const saveBtn = document.getElementById('save-btn');
            const countdown = document.getElementById('countdown');
            const deadline = new Date(@json(optional($deadline)->toIso8601String())).getTime();
            const eventUrl = @json(route('student-exams.attempt.events', $attempt));
            const csrfToken = @json(csrf_token());

            function buildAnswerFormData() {
                const formData = new FormData();
                formData.append('_token', csrfToken);
                const inputs = attemptForm.querySelectorAll('input[name^="answers["], textarea[name^="answers["]');
                inputs.forEach((input) => {
                    if (input.type === 'radio' && !input.checked) return;
                    formData.append(input.name, input.value);
                });
                return formData;
            }

            saveBtn.addEventListener('click', () => {
                const formData = buildAnswerFormData();
                fetch(saveForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                }).then(() => window.location.reload()).catch(() => {});
            });

            function sendEvent(eventType) {
                fetch(eventUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ event_type: eventType })
                }).catch(() => {});
            }

            document.addEventListener('visibilitychange', () => {
                sendEvent(document.hidden ? 'visibility_hidden' : 'visibility_visible');
            });
            window.addEventListener('blur', () => sendEvent('window_blur'));
            window.addEventListener('focus', () => sendEvent('window_focus'));
            window.addEventListener('beforeunload', () => sendEvent('refresh'));
            window.addEventListener('online', () => sendEvent('reconnect'));

            function tick() {
                if (!deadline || Number.isNaN(deadline)) {
                    countdown.innerText = '--:--';
                    return;
                }
                const now = Date.now();
                const diff = deadline - now;
                if (diff <= 0) {
                    countdown.innerText = '00:00';
                    attemptForm.submit();
                    return;
                }
                const totalSeconds = Math.floor(diff / 1000);
                const mm = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
                const ss = String(totalSeconds % 60).padStart(2, '0');
                countdown.innerText = mm + ':' + ss;
            }

            tick();
            setInterval(tick, 1000);
            setInterval(() => {
                const formData = buildAnswerFormData();
                fetch(saveForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                }).catch(() => {});
            }, 120000);
        })();
    </script>
@endsection
