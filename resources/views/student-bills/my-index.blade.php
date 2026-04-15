@extends('layouts.app', ['title' => __('ui.my_bills')])

@section('content')
<div x-data data-async-list data-fragment="#my-bills-fragment">
    <x-ui.page-header :title="__('ui.my_bills')" :subtitle="__('ui.my_bill_status_subtitle')" />

    <x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search')">
        <x-slot:filters>
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

    <div id="my-bills-fragment">
        <div class="tera-table-wrap">
            <table class="tera-table">
                <thead>
                <tr>
                    <th>{{ __('ui.no') }}</th>
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
                            <a href="{{ route('my-bills.show', $bill) }}" class="tera-btn tera-btn-primary !px-3 !py-1.5">{{ __('ui.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-slate-500">{{ __('ui.no_bills_found') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $bills->links() }}</div>
    </div>
</div>
@endsection

