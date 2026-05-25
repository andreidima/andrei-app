@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header culoare2 text-white">
            <span class="fs-5"><i class="fa-solid fa-calendar-check me-1"></i>Edit meeting</span>
        </div>
        <div class="card-body">
            @include('errors.errors')
            <form method="POST" action="{{ route('wardrobe.meetings.update', $meeting) }}" enctype="multipart/form-data">
                @method('PATCH')
                @include('wardrobe.meetings.form', ['buttonText' => 'Update meeting'])
            </form>
        </div>
    </div>
</div>
@endsection
