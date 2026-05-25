@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header culoare2 text-white d-flex justify-content-between align-items-center">
            <span class="fs-5"><i class="fa-solid fa-user me-1"></i>{{ $person->name }}</span>
            <a href="{{ route('wardrobe.people.edit', $person) }}" class="btn btn-sm btn-primary text-white">Edit</a>
        </div>
        <div class="card-body">
            @include('errors.errors')

            <dl class="row">
                <dt class="col-md-3">Email</dt>
                <dd class="col-md-9">{{ $person->email ?: '-' }}</dd>
                <dt class="col-md-3">Type</dt>
                <dd class="col-md-9">{{ $person->contactTypeLabel() }}</dd>
                <dt class="col-md-3">Phone</dt>
                <dd class="col-md-9">{{ $person->phone ?: '-' }}</dd>
                <dt class="col-md-3">Notes</dt>
                <dd class="col-md-9">{!! nl2br(e($person->notes ?: '-')) !!}</dd>
            </dl>

            <h2 class="h5 mt-4">Meetings</h2>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Clothing items</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($person->meetings as $meeting)
                            <tr>
                                <td>{{ $meeting->met_at?->format('Y-m-d') }}</td>
                                <td>{{ $meeting->displayTitle() }}</td>
                                <td>{{ $meeting->clothingItems->pluck('name')->join(', ') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('wardrobe.meetings.show', $meeting) }}" class="badge bg-success text-decoration-none">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No meetings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="text-center">
                <a href="{{ route('wardrobe.people.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
