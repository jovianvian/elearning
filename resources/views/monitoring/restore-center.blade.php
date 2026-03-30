@extends('layouts.app', ['title' => 'Restore Center'])

@section('content')
    <div>
        <h2 class="text-xl font-semibold">Restore Center</h2>
        <p class="text-sm text-slate-500">Restore soft-deleted entities (Super Admin only).</p>
    </div>

    <div class="bg-white border rounded-xl p-4">
        <form method="GET" class="flex gap-2 items-center">
            <label class="text-sm">Entity</label>
            <select name="entity" class="rounded border-slate-300 text-sm">
                @foreach($map as $key => $class)
                    <option value="{{ $key }}" @selected($entity === $key)>{{ $key }}</option>
                @endforeach
            </select>
            <button class="px-3 py-1.5 border rounded text-sm">Filter</button>
        </form>
    </div>

    <div class="bg-white border rounded-xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-3 text-left">ID</th>
                <th class="p-3 text-left">Label</th>
                <th class="p-3 text-left">Deleted At</th>
                <th class="p-3 text-right">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $item)
                <tr class="border-t border-slate-100">
                    <td class="p-3">{{ $item->id }}</td>
                    <td class="p-3">
                        {{ $item->title ?? $item->name ?? $item->name_id ?? $item->full_name ?? $item->username ?? 'Item #'.$item->id }}
                    </td>
                    <td class="p-3">{{ $item->deleted_at?->format('d M Y H:i:s') }}</td>
                    <td class="p-3 text-right">
                        <form method="POST" action="{{ route('super-admin.restore-center.restore', ['entity' => $entity, 'id' => $item->id]) }}">
                            @csrf
                            <button class="px-3 py-1.5 rounded bg-emerald-600 text-white text-xs" onclick="return confirm('Restore this item?')">Restore</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="p-6 text-center text-slate-500">No deleted items in this entity.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $items->links() }}
@endsection

