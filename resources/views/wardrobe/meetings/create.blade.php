@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header culoare2 text-white">
            <span class="fs-5"><i class="fa-solid fa-calendar-plus me-1"></i>Add meeting</span>
        </div>
        <div class="card-body">
            @include('errors.errors')
            <form method="POST" action="{{ route('wardrobe.meetings.store') }}" enctype="multipart/form-data">
                @include('wardrobe.meetings.form', ['buttonText' => 'Add meeting'])
            </form>
        </div>
    </div>
</div>
@endsection
