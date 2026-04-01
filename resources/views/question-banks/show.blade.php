@extends('layouts.app')

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold">{{ $questionBank->title }}</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Subject: {{ $questionBank->subject->name_id ?? '-' }} |
                    Visibility: {{ $questionBank->visibility }}
                </p>
                @if($questionBank->description)
                    <p class="text-sm mt-3 text-slate-600">{{ $questionBank->description }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                <a href="{{ route('question-banks.questions.create', $questionBank) }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm">Add Question</a>
                <a href="{{ route('question-banks.index') }}" class="px-4 py-2 border rounded-lg text-sm">Back</a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 mobile-table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="px-4 py-3 text-left">Question</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-left">Points</th>
                <th class="px-4 py-3 text-left">Difficulty</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-right">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($questions as $question)
                <tr class="border-t border-slate-100 align-top">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ \Illuminate\Support\Str::limit($question->question_text, 90) }}</div>
                        @if($question->isMultipleChoice())
                            <div class="text-xs text-slate-500 mt-1">
                                @foreach($question->options as $option)
                                    <div>
                                        {{ $option->option_key }}. {{ \Illuminate\Support\Str::limit($option->option_text, 50) }}
                                        @if($option->is_correct)
                                            <span class="text-emerald-600 font-semibold">(Correct)</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $question->type }}</td>
                    <td class="px-4 py-3">{{ $question->points }}</td>
                    <td class="px-4 py-3">{{ ucfirst($question->difficulty) }}</td>
                    <td class="px-4 py-3">
                        @if($question->is_active)
                            <span class="px-2 py-1 rounded text-xs bg-emerald-100 text-emerald-700">Active</span>
                        @else
                            <span class="px-2 py-1 rounded text-xs bg-slate-200 text-slate-700">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('questions.edit', $question) }}" class="px-3 py-1.5 rounded border text-xs">Edit</a>
                            <form method="POST" action="{{ route('questions.destroy', $question) }}" onsubmit="return confirm('Delete this question?')">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1.5 rounded bg-redx text-white text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No questions in this bank yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $questions->links() }}
    </div>
@endsection
