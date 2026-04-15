@extends('layouts.app')

@section('content')
    <x-ui.page-header :title="$questionBank->title" :subtitle="__('ui.question_bank_show_subtitle')">
        <x-slot:actions>
            @if($canManage ?? false)
                <a href="{{ route('question-banks.create') }}" class="tera-btn tera-btn-muted">{{ __('ui.add_question_bank') }}</a>
                <a href="{{ route('question-banks.edit', $questionBank) }}" class="tera-btn tera-btn-muted">{{ __('ui.edit') }} {{ __('ui.question_banks') }}</a>
            @endif
            <a href="{{ route('question-banks.questions.create', $questionBank) }}" class="tera-btn tera-btn-primary">{{ __('ui.add_question') }}</a>
            <a href="{{ route('question-banks.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.back') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <div>
            <p class="text-sm text-slate-500">
                {{ __('ui.subjects') }}: {{ $questionBank->subject->name_id ?? '-' }} |
                {{ __('ui.visibility') }}: {{ $questionBank->visibility }}
            </p>
            @if($questionBank->description)
                <p class="text-sm mt-3 text-slate-600">{{ $questionBank->description }}</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 mobile-table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="px-4 py-3 text-left">{{ __('ui.questions') }}</th>
                <th class="px-4 py-3 text-left">{{ __('ui.type') }}</th>
                <th class="px-4 py-3 text-left">{{ __('ui.points') }}</th>
                <th class="px-4 py-3 text-left">{{ __('ui.difficulty') }}</th>
                <th class="px-4 py-3 text-left">{{ __('ui.status') }}</th>
                <th class="px-4 py-3 text-right">{{ __('ui.action') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($questions as $question)
                <tr class="border-t border-slate-100 align-top">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ \Illuminate\Support\Str::limit($question->question_text, 90) }}</div>
                        @if(!empty($question->image_url))
                            <div class="mt-2">
                                <img src="{{ $question->image_url }}" alt="Question image" class="max-h-28 rounded border border-slate-200 object-contain bg-white">
                            </div>
                        @endif
                        @if($question->isMultipleChoice() || $question->isMultipleResponse())
                            <div class="text-xs text-slate-500 mt-1">
                                @foreach($question->options as $option)
                                    <div>
                                        {{ $option->option_key }}. {{ \Illuminate\Support\Str::limit($option->option_text, 50) }}
                                        @if($option->is_correct)
                                            <span class="text-emerald-600 font-semibold">({{ __('ui.correct') }})</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @switch($question->type)
                            @case('multiple_choice')
                                {{ __('ui.question_type_single_choice') }}
                                @break
                            @case('multiple_response')
                                {{ __('ui.question_type_multiple_response') }}
                                @break
                            @case('short_answer')
                                {{ __('ui.question_type_short_answer') }}
                                @break
                            @default
                                {{ __('ui.question_type_essay') }}
                        @endswitch
                    </td>
                    <td class="px-4 py-3">{{ $question->points }}</td>
                    <td class="px-4 py-3">{{ ucfirst($question->difficulty) }}</td>
                    <td class="px-4 py-3">
                        @if($question->is_active)
                            <span class="px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-700">{{ __('ui.active') }}</span>
                        @else
                            <span class="px-2 py-1 rounded text-xs bg-slate-200 text-slate-700">{{ __('ui.inactive') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('questions.edit', $question) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5 !text-xs">{{ __('ui.edit') }}</a>
                            <form method="POST" action="{{ route('questions.destroy', $question) }}" onsubmit="return confirm(@js(__('ui.delete_question_bank_question')))">
                                @csrf
                                @method('DELETE')
                                <button class="tera-btn tera-btn-danger !px-3 !py-1.5 !text-xs">{{ __('ui.delete') }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">{{ __('ui.no_questions_in_bank') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $questions->links() }}
    </div>
@endsection
