@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header culoare2 text-white">
            <span class="fs-5"><i class="fa-solid fa-user-plus me-1"></i>Add contact</span>
        </div>
        <div class="card-body">
            @include('errors.errors')
            <form method="POST" action="{{ route('wardrobe.people.store') }}">
                @include('wardrobe.people.form', ['buttonText' => 'Add contact'])
            </form>
        </div>
    </div>
</div>
@endsection
