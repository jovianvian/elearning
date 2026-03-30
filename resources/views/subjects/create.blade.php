@extends('layouts.app', ['title' => 'Create Subject'])
@section('content')
<form method="POST" action="{{ route('subjects.store') }}" class="bg-white p-4 rounded shadow space-y-4">@include('subjects._form')<button class="px-3 py-2 bg-primary text-white rounded">Save</button></form>
@endsection
