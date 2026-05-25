@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header culoare2 text-white">
            <span class="fs-5"><i class="fa-solid fa-plus me-1"></i>Add clothing item</span>
        </div>
        <div class="card-body">
            @include('errors.errors')
            <form method="POST" action="{{ route('wardrobe.clothing-items.store') }}" enctype="multipart/form-data">
                @include('wardrobe.clothing_items.form', ['buttonText' => 'Add item'])
            </form>
        </div>
    </div>
</div>
@endsection
