@extends('layouts.app', ['title' => 'Assign Teacher to Subject'])
@section('content')
<form method="POST" action="{{ route('assignments.subject-teachers.store') }}" class="bg-white p-4 rounded shadow space-y-4">@include('assignments.subject-teachers._form')<button class="px-3 py-2 bg-primary text-white rounded">Save</button></form>
@endsection
