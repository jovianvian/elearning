@extends('layouts.app', ['title' => 'Users'])
@section('content')
<x-ui.page-header title="Users" subtitle="Kelola akun Super Admin, Admin, Principal, Teacher, dan Student.">
    <x-slot:actions>
        <a href="{{ route('users.create') }}" class="tera-btn tera-btn-primary"><i data-lucide="user-plus" class="w-4 h-4"></i>Add User</a>
    </x-slot:actions>
</x-ui.page-header>

<div class="tera-table-wrap">
    <table class="tera-table">
        <thead>
            <tr>
                <th class="text-left">Name</th>
                <th class="text-left">Role</th>
                <th class="text-center">NIS</th>
                <th class="text-center">NIP</th>
                <th class="text-left">Email</th>
                <th class="text-center">Status</th>
                <th class="text-right">Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>
                    <p class="font-semibold text-slate-800">{{ $user->full_name }}</p>
                    <p class="text-xs text-slate-500">{{ $user->username }}</p>
                </td>
                <td>{{ $user->role?->name }}</td>
                <td class="text-center">{{ $user->nis ?: '-' }}</td>
                <td class="text-center">{{ $user->nip ?: '-' }}</td>
                <td>{{ $user->email ?: '-' }}</td>
                <td class="text-center">
                    <span class="tera-badge {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-right">
                    <div class="inline-flex items-center gap-2">
                        <a class="tera-btn tera-btn-muted !px-3 !py-1.5" href="{{ route('users.edit',$user) }}">Edit</a>
                        <form class="inline" method="POST" action="{{ route('users.destroy',$user) }}">
                            @csrf
                            @method('DELETE')
                            <button class="tera-btn tera-btn-danger !px-3 !py-1.5">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $users->links() }}
@endsection
