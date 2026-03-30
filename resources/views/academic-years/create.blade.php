@extends('layouts.app', ['title' => 'Create Academic Year'])
@section('content')
<form method="POST" action="{{ route('super-admin.academic-years.store') }}" class="bg-white p-4 rounded shadow space-y-4">
@include('academic-years._form')
<button class="px-3 py-2 bg-primary text-white rounded">Save</button>
</form>
@endsection
