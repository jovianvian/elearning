@extends('layouts.app', ['title' => 'Restore Center'])

@section('content')
    <x-ui.page-header title="Restore Center" subtitle="Review deleted records and restore important data safely." />

    <div class="tera-card">
        <div class="tera-card-body">
        <form method="GET" class="flex gap-2 items-center">
            <label class="tera-label !mb-0">{{ __('ui.entity') }}</label>
            <select name="entity" class="tera-select !w-auto !py-2 !text-sm">
                @foreach($map as $key => $class)
                    <option value="{{ $key }}" @selected($entity === $key)>{{ $key }}</option>
                @endforeach
            </select>
            <button class="tera-btn tera-btn-primary !py-2 !px-3 !text-sm">{{ __('ui.filter') }}</button>
        </form>
        </div>
    </div>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
            <tr>
                <th class="text-left">ID</th>
                <th class="text-left">{{ __('ui.label') }}</th>
                <th class="text-left">{{ __('ui.deleted_at') }}</th>
                <th class="text-right">{{ __('ui.action') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>
                        {{ $item->title ?? $item->name ?? $item->name_id ?? $item->full_name ?? $item->username ?? 'Item #'.$item->id }}
                    </td>
                    <td>{{ $item->deleted_at?->format('d M Y H:i:s') }}</td>
                    <td class="text-right">
                        <form method="POST" action="{{ route('super-admin.restore-center.restore', ['entity' => $entity, 'id' => $item->id]) }}">
                            @csrf
                            <button class="tera-btn tera-btn-primary !px-3 !py-1.5 !text-xs" onclick="return confirm('{{ __('ui.restore_item_question') }}')">{{ __('ui.restore') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-6 text-center text-slate-500">{{ __('ui.no_deleted_items_entity') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
@endsection
