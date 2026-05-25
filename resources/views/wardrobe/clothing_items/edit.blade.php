@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header culoare2 text-white">
            <span class="fs-5"><i class="fa-solid fa-pen me-1"></i>Edit clothing item</span>
        </div>
        <div class="card-body">
            @include('errors.errors')
            <form method="POST" action="{{ route('wardrobe.clothing-items.update', $clothingItem) }}" enctype="multipart/form-data">
                @method('PATCH')
                @include('wardrobe.clothing_items.form', ['buttonText' => 'Update item'])
            </form>
        </div>
    </div>
</div>
@endsection
