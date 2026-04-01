@extends('layouts.app', ['title' => 'Restore Center'])

@section('content')
    <x-ui.page-header title="Restore Center" subtitle="Review deleted records and restore important data safely." />

    <x-ui.table-toolbar :search-value="request('q')" search-placeholder="Search deleted item label">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.entity') }}</label>
                <select name="entity" class="tera-select">
                    @foreach($map as $key => $class)
                        <option value="{{ $key }}" @selected($entity === $key)>{{ $key }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
            <tr>
                <th>No</th>
                <th>ID</th>
                <th>{{ __('ui.label') }}</th>
                <th>{{ __('ui.deleted_at') }}</th>
                <th>{{ __('ui.action') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $items->firstItem() + $loop->index }}</td>
                    <td>{{ $item->id }}</td>
                    <td>
                        {{ $item->title ?? $item->name ?? $item->name_id ?? $item->full_name ?? $item->username ?? 'Item #'.$item->id }}
                    </td>
                    <td>{{ $item->deleted_at?->format('d M Y H:i:s') }}</td>
                    <td>
                        <form method="POST" action="{{ route('super-admin.restore-center.restore', ['entity' => $entity, 'id' => $item->id]) }}">
                            @csrf
                            <button class="tera-btn tera-btn-primary !px-3 !py-1.5 !text-xs" onclick="return confirm('{{ __('ui.restore_item_question') }}')">{{ __('ui.restore') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('ui.no_deleted_items_entity') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
@endsection
