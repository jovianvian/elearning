@extends('layouts.app', ['title' => 'Create Semester'])
@section('content')
<form method="POST" action="{{ route('super-admin.semesters.store') }}" class="bg-white p-4 rounded shadow space-y-4">@include('semesters._form')<button class="px-3 py-2 bg-primary text-white rounded">Save</button></form>
@endsection
