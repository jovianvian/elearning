@extends('layouts.app', ['title' => __('ui.edit_learning_material')])

@section('content')
    <x-ui.page-header :title="__('ui.edit_learning_material')" :subtitle="__('ui.learning_material_edit_subtitle')" />

    <div class="tera-card">
        <div class="tera-card-body">
            <form method="POST" action="{{ route('learning-materials.update', $learningMaterial) }}" enctype="multipart/form-data">
                @method('PUT')
                @include('learning-materials._form', [
                    'submitLabel' => __('ui.save'),
                    'learningMaterial' => $learningMaterial,
                ])
            </form>
        </div>
    </div>
@endsection
