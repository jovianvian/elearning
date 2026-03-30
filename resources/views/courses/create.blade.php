@extends('layouts.app', ['title' => 'Create Course'])
@section('content')
<form method="POST" action="{{ route('courses.store') }}" class="bg-white p-4 rounded shadow space-y-4">@include('courses._form')<button class="px-3 py-2 bg-primary text-white rounded">Save Course</button></form>
@endsection
