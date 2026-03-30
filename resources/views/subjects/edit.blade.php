@extends('layouts.app', ['title' => 'Edit Subject'])
@section('content')
<form method="POST" action="{{ route('subjects.update', $subject) }}" class="bg-white p-4 rounded shadow space-y-4">@method('PUT') @include('subjects._form')<button class="px-3 py-2 bg-primary text-white rounded">Update</button></form>
@endsection
