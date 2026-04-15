@extends('layouts.app', ['title' => __('ui.spp_bills')])

@section('content')
    <x-ui.page-header
        :title="($studentBill->student?->full_name ?? '-') . ' - ' . __('ui.spp_bills')"
        :subtitle="__('ui.student_bill_detail_subtitle')"
    />

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="tera-table-wrap">
                <table class="tera-table">
                    <thead>
                    <tr>
                        <th>{{ __('ui.month') }}</th>
                        <th>{{ __('ui.total_amount') }}</th>
                        <th>{{ __('ui.paid_amount') }}</th>
                        <th>{{ __('ui.outstanding_amount') }}</th>
                        <th>{{ __('ui.bill_item_status') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($studentBill->items as $item)
                        <tr>
                            <td>{{ $item->month_name }}</td>
                            <td>{{ number_format((float) $item->amount, 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $item->paid_amount, 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $item->amount - (float) $item->paid_amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="tera-badge {{ $item->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($item->status === 'partial' ? 'bg-sky-100 text-sky-700' : 'bg-slate-200 text-slate-700') }}">
                                    {{ __('ui.status_'.$item->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="tera-card">
                <div class="tera-card-body space-y-2 text-sm">
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.student') }}</span>
                        <span class="font-semibold text-right">{{ $studentBill->student?->full_name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.academic_year') }}</span>
                        <span class="font-semibold text-right">{{ $studentBill->academicYear?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.semester') }}</span>
                        <span class="font-semibold text-right">{{ $studentBill->semester?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.total_amount') }}</span>
                        <span class="font-semibold text-right">{{ number_format((float) $studentBill->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.paid_amount') }}</span>
                        <span class="font-semibold text-right">{{ number_format((float) $studentBill->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.bill_status') }}</span>
                        <span class="tera-badge {{ $studentBill->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($studentBill->status === 'partial' ? 'bg-sky-100 text-sky-700' : 'bg-slate-200 text-slate-700') }}">
                            {{ __('ui.status_'.$studentBill->status) }}
                        </span>
                    </div>
                </div>
            </div>

            @if(auth()->user()->hasRole('super_admin', 'admin'))
                <div class="tera-card">
                    <div class="tera-card-body">
                        <form method="POST" action="{{ route('student-bills.payments.store', $studentBill) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="tera-label">{{ __('ui.select_months_to_pay') }}</label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($studentBill->items as $item)
                                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                            <input type="checkbox" name="month_numbers[]" value="{{ $item->month_number }}" @checked(in_array($item->month_number, old('month_numbers', []), true)) {{ $item->status === 'paid' ? 'disabled' : '' }}>
                                            <span>{{ $item->month_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="tera-label">{{ __('ui.payment_amount') }}</label>
                                <input type="number" step="0.01" min="1" name="payment_amount" class="tera-input" value="{{ old('payment_amount') }}" required>
                            </div>
                            <button type="submit" class="tera-btn tera-btn-primary w-full">{{ __('ui.record_payment') }}</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

