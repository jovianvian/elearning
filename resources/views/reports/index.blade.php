@extends('layouts.app', ['title' => 'Reports'])

@section('content')
    <x-ui.page-header title="Reports & Analytics" subtitle="Review exam performance and operational summaries." />

    <div class="bg-white border rounded-xl p-4">
        <h3 class="font-semibold mb-3">Exam Recap by Status</h3>
        <div class="grid md:grid-cols-6 gap-2 text-sm">
            @foreach(['draft','scheduled','active','closed','graded','archived'] as $status)
                <div class="p-3 rounded border bg-slate-50">
                    <div class="text-slate-500">{{ ucfirst($status) }}</div>
                    <div class="text-lg font-semibold">{{ $examRecap[$status] ?? 0 }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white border rounded-xl p-4">
            <h3 class="font-semibold mb-3">Exam Results Per Class</h3>
            <div class="space-y-2 text-sm">
                @forelse($classResults as $row)
                    <div class="flex justify-between">
                        <span>{{ $row->class_name }} ({{ $row->attempts }} attempts)</span>
                        <span class="font-semibold">{{ number_format((float)$row->avg_score, 2) }}</span>
                    </div>
                @empty
                    <div class="text-slate-500">No data.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <h3 class="font-semibold mb-3">Exam Results Per Subject</h3>
            <div class="space-y-2 text-sm">
                @forelse($subjectResults as $row)
                    <div class="flex justify-between">
                        <span>{{ $row->subject_name }} ({{ $row->attempts }} attempts)</span>
                        <span class="font-semibold">{{ number_format((float)$row->avg_score, 2) }}</span>
                    </div>
                @empty
                    <div class="text-slate-500">No data.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white border rounded-xl p-4">
        <h3 class="font-semibold mb-3">Student Score List Per Exam</h3>
        <div class="flex flex-wrap gap-2 text-sm">
            @forelse($examList as $exam)
                <a href="{{ route('reports.exam-scores', $exam) }}" class="px-3 py-1.5 border rounded">{{ $exam->title }}</a>
            @empty
                <span class="text-slate-500">No exams yet.</span>
            @endforelse
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white border rounded-xl p-4">
            <h3 class="font-semibold mb-3">Login Activity Summary (Last 7 Days)</h3>
            <div class="space-y-2 text-sm">
                @forelse($loginSummary as $row)
                    <div class="flex justify-between">
                        <span>{{ \Illuminate\Support\Carbon::parse($row->login_date)->format('d M Y') }}</span>
                        <span class="font-semibold">{{ $row->total }}</span>
                    </div>
                @empty
                    <div class="text-slate-500">No login data.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <h3 class="font-semibold mb-3">Suspicious Activity Summary</h3>
            <div class="space-y-2 text-sm">
                @forelse($suspiciousSummary as $row)
                    <div class="flex justify-between">
                        <span>{{ $row->activity_type }}</span>
                        <span class="font-semibold">{{ $row->total }}</span>
                    </div>
                @empty
                    <div class="text-slate-500">No suspicious activity logs.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
