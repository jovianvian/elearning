@extends('layouts.app', ['title' => 'Edit Semester'])
@section('content')
<form method="POST" action="{{ route('super-admin.semesters.update', $semester) }}" class="bg-white p-4 rounded shadow space-y-4">@method('PUT') @include('semesters._form')<button class="px-3 py-2 bg-primary text-white rounded">Update</button></form>
@endsection
