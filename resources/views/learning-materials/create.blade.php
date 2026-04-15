@extends('layouts.app', ['title' => __('ui.add_learning_material')])

@section('content')
    <x-ui.page-header :title="__('ui.add_learning_material')" :subtitle="__('ui.learning_material_create_subtitle')" />

    @if($courses->isEmpty())
        <div class="tera-card">
            <div class="tera-card-body">
                <p class="text-sm text-slate-600">{{ __('ui.no_courses_for_material_create') }}</p>
                <div class="mt-3">
                    <a href="{{ route('my-courses.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.my_courses') }}</a>
                </div>
            </div>
        </div>
    @else
        <div class="tera-card">
            <div class="tera-card-body">
                <form method="POST" action="{{ route('learning-materials.store') }}" enctype="multipart/form-data">
                    @include('learning-materials._form', [
                        'submitLabel' => __('ui.save'),
                        'learningMaterial' => null,
                    ])
                </form>
            </div>
        </div>
    @endif
@endsection
