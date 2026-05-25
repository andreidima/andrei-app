@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header culoare2 text-white">
            <span class="fs-5"><i class="fa-solid fa-user-pen me-1"></i>Edit contact</span>
        </div>
        <div class="card-body">
            @include('errors.errors')
            <form method="POST" action="{{ route('wardrobe.people.update', $person) }}">
                @method('PATCH')
                @include('wardrobe.people.form', ['buttonText' => 'Update contact'])
            </form>
        </div>
    </div>
</div>
@endsection
