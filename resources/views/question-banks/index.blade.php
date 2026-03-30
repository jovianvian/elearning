@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold">Question Banks</h2>
            <p class="text-sm text-slate-500">Manage shared/private subject question banks.</p>
        </div>
        <a href="{{ route('question-banks.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm">Create Bank</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="px-4 py-3 text-left">Title</th>
                <th class="px-4 py-3 text-left">Subject</th>
                <th class="px-4 py-3 text-left">Visibility</th>
                <th class="px-4 py-3 text-left">Questions</th>
                <th class="px-4 py-3 text-left">Creator</th>
                <th class="px-4 py-3 text-right">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($banks as $bank)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-medium">{{ $bank->title }}</td>
                    <td class="px-4 py-3">{{ $bank->subject->name_id ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @if($bank->visibility === 'subject_shared')
                            <span class="px-2 py-1 rounded text-xs bg-skyx/20 text-sky-700">Shared</span>
                        @else
                            <span class="px-2 py-1 rounded text-xs bg-slate-200 text-slate-700">Private</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $bank->questions_count }}</td>
                    <td class="px-4 py-3">{{ $bank->creator->full_name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('question-banks.show', $bank) }}" class="px-3 py-1.5 rounded border text-xs">Detail</a>
                            <a href="{{ route('question-banks.edit', $bank) }}" class="px-3 py-1.5 rounded border text-xs">Edit</a>
                            <form method="POST" action="{{ route('question-banks.destroy', $bank) }}" onsubmit="return confirm('Delete this question bank?')">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1.5 rounded bg-redx text-white text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No question banks found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $banks->links() }}
    </div>
@endsection

