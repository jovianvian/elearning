@extends('layouts.app', ['title' => __('ui.spp_bills')])

@section('content')
<div x-data data-async-list data-fragment="#student-bills-table-fragment">
    <x-ui.page-header :title="__('ui.student_bill_management_title')" :subtitle="__('ui.student_bill_management_subtitle')" />

    @if(auth()->user()->hasRole('super_admin', 'admin'))
        <div class="tera-card mb-4">
            <div class="tera-card-body">
                <form method="POST" action="{{ route('student-bills.generate') }}" class="grid gap-3 md:grid-cols-5">
                    @csrf
                    <div>
                        <label class="tera-label">{{ __('ui.student') }}</label>
                        <select name="student_id" class="tera-select" required>
                            <option value="">{{ __('ui.student') }}</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->nis ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="tera-label">{{ __('ui.academic_year') }}</label>
                        <select name="academic_year_id" class="tera-select" required>
                            @foreach($years as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="tera-label">{{ __('ui.semester') }}</label>
                        <select name="semester_id" class="tera-select" required>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="tera-label">{{ __('ui.monthly_amount') }}</label>
                        <input type="number" step="0.01" min="1" name="monthly_amount" class="tera-input" required value="250000">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="tera-btn tera-btn-primary w-full">{{ __('ui.generate_bill') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_student_name_or_nis')">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.classes') }}</label>
                <select name="class_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected((string) request('class_id') === (string) $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.academic_year') }}</label>
                <select name="academic_year_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($years as $year)
                        <option value="{{ $year->id }}" @selected((string) request('academic_year_id') === (string) $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.semester') }}</label>
                <select name="semester_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" @selected((string) request('semester_id') === (string) $semester->id)>{{ $semester->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.bill_status') }}</label>
                <select name="status" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    <option value="unpaid" @selected(request('status') === 'unpaid')>{{ __('ui.status_unpaid') }}</option>
                    <option value="partial" @selected(request('status') === 'partial')>{{ __('ui.status_partial') }}</option>
                    <option value="paid" @selected(request('status') === 'paid')>{{ __('ui.status_paid') }}</option>
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div id="student-bills-table-fragment">
        <div class="tera-table-wrap">
            <table class="tera-table">
                <thead>
                <tr>
                    <th>{{ __('ui.no') }}</th>
                    <th>{{ __('ui.student') }}</th>
                    <th>{{ __('ui.classes') }}</th>
                    <th>{{ __('ui.academic_year') }}</th>
                    <th>{{ __('ui.semester') }}</th>
                    <th>{{ __('ui.total_amount') }}</th>
                    <th>{{ __('ui.paid_amount') }}</th>
                    <th>{{ __('ui.outstanding_amount') }}</th>
                    <th>{{ __('ui.bill_status') }}</th>
                    <th>{{ __('ui.action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($bills as $bill)
                    <tr>
                        <td>{{ $bills->firstItem() + $loop->index }}</td>
                        <td>{{ $bill->student?->full_name ?? '-' }}</td>
                        <td>{{ $bill->student?->schoolClass?->name ?? '-' }}</td>
                        <td>{{ $bill->academicYear?->name ?? '-' }}</td>
                        <td>{{ $bill->semester?->name ?? '-' }}</td>
                        <td>{{ number_format((float) $bill->total_amount, 0, ',', '.') }}</td>
                        <td>{{ number_format((float) $bill->paid_amount, 0, ',', '.') }}</td>
                        <td>{{ number_format((float) $bill->total_amount - (float) $bill->paid_amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="tera-badge {{ $bill->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($bill->status === 'partial' ? 'bg-sky-100 text-sky-700' : 'bg-slate-200 text-slate-700') }}">
                                {{ __('ui.status_'.$bill->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('student-bills.show', $bill) }}" class="tera-btn tera-btn-primary !px-3 !py-1.5">{{ __('ui.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-slate-500">{{ __('ui.no_bills_found') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $bills->links() }}</div>
    </div>
</div>
@endsection

