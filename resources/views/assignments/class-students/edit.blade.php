@extends('layouts.app', ['title' => 'Edit Class Student Assignment'])
@section('content')
<form method="POST" action="{{ route('assignments.class-students.update', $class_student) }}" class="bg-white p-4 rounded shadow space-y-4">@method('PUT') @include('assignments.class-students._form')<button class="px-3 py-2 bg-primary text-white rounded">Update</button></form>
@endsection
