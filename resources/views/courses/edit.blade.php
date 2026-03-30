@extends('layouts.app', ['title' => 'Edit Course'])
@section('content')
<form method="POST" action="{{ route('courses.update', $course) }}" class="bg-white p-4 rounded shadow space-y-4">@method('PUT') @include('courses._form')<button class="px-3 py-2 bg-primary text-white rounded">Update Course</button></form>
@endsection
