@extends('layouts.app', ['title' => 'Edit Subject Teacher Assignment'])
@section('content')
<form method="POST" action="{{ route('assignments.subject-teachers.update', $subject_teacher) }}" class="bg-white p-4 rounded shadow space-y-4">@method('PUT') @include('assignments.subject-teachers._form')<button class="px-3 py-2 bg-primary text-white rounded">Update</button></form>
@endsection
