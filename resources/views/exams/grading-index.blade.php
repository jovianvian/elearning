@extends('layouts.app', ['title' => 'Exam Grading'])

@section('content')
    <div>
        <h2 class="text-xl font-semibold">Exam Grading Queue</h2>
        <p class="text-sm text-slate-500">Submitted attempts for review and essay grading.</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-3 text-left">Exam</th>
                <th class="p-3 text-left">Student</th>
                <th class="p-3 text-center">Status</th>
                <th class="p-3 text-center">Objective</th>
                <th class="p-3 text-center">Essay</th>
                <th class="p-3 text-center">Final</th>
                <th class="p-3 text-right">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($attempts as $attempt)
                <tr class="border-t border-slate-100">
                    <td class="p-3">
                        <div class="font-medium">{{ $attempt->exam?->title }}</div>
                        <div class="text-xs text-slate-500">{{ $attempt->exam?->course?->subject?->name_id }}</div>
                    </td>
                    <td class="p-3">{{ $attempt->student?->full_name }}</td>
                    <td class="p-3 text-center">{{ $attempt->status }}</td>
                    <td class="p-3 text-center">{{ $attempt->score_objective }}</td>
                    <td class="p-3 text-center">{{ $attempt->score_essay }}</td>
                    <td class="p-3 text-center font-semibold">{{ $attempt->final_score }}</td>
                    <td class="p-3 text-right">
                        <a href="{{ route('exam-grading.show', $attempt) }}" class="px-3 py-1.5 border rounded text-xs">Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-6 text-center text-slate-500">No attempts to grade.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $attempts->links() }}
@endsection

