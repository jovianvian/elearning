@extends('layouts.app', ['title' => __('ui.my_bills')])

@section('content')
    <x-ui.page-header :title="__('ui.my_bills')" :subtitle="__('ui.my_bill_detail_subtitle')" />

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2">
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
        <div>
            <div class="tera-card">
                <div class="tera-card-body space-y-2 text-sm">
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.academic_year') }}</span>
                        <span class="font-semibold">{{ $studentBill->academicYear?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.semester') }}</span>
                        <span class="font-semibold">{{ $studentBill->semester?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.total_amount') }}</span>
                        <span class="font-semibold">{{ number_format((float) $studentBill->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.paid_amount') }}</span>
                        <span class="font-semibold">{{ number_format((float) $studentBill->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-500">{{ __('ui.bill_status') }}</span>
                        <span class="tera-badge {{ $studentBill->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($studentBill->status === 'partial' ? 'bg-sky-100 text-sky-700' : 'bg-slate-200 text-slate-700') }}">
                            {{ __('ui.status_'.$studentBill->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

