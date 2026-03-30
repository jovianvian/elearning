@extends('layouts.app', ['title' => 'Assign Student to Class'])
@section('content')
<form method="POST" action="{{ route('assignments.class-students.store') }}" class="bg-white p-4 rounded shadow space-y-4">@include('assignments.class-students._form')<button class="px-3 py-2 bg-primary text-white rounded">Save</button></form>
@endsection
