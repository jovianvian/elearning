@extends('layouts.app', ['title' => 'Edit Academic Year'])
@section('content')
<form method="POST" action="{{ route('super-admin.academic-years.update', $academicYear) }}" class="bg-white p-4 rounded shadow space-y-4">
@method('PUT')
@include('academic-years._form')
<button class="px-3 py-2 bg-primary text-white rounded">Update</button>
</form>
@endsection
