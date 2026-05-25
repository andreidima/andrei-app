@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header culoare2 text-white d-flex justify-content-between align-items-center">
            <span class="fs-5"><i class="fa-solid fa-shirt me-1"></i>{{ $clothingItem->displayName() }}</span>
            <a href="{{ route('wardrobe.clothing-items.edit', $clothingItem) }}" class="btn btn-sm btn-primary text-white">Edit</a>
        </div>
        <div class="card-body">
            @include('errors.errors')

            <div class="row g-4">
                <div class="col-md-3">
                    @if ($clothingItem->photoUrl())
                        <img src="{{ $clothingItem->photoUrl() }}" alt="{{ $clothingItem->name }}" class="img-fluid img-thumbnail">
                    @else
                        <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="height: 180px;">
                            <i class="fa-solid fa-shirt fa-3x text-muted"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-9">
                    <dl class="row">
                        <dt class="col-md-3">Category</dt>
                        <dd class="col-md-9">{{ $clothingItem->category ?: '-' }}</dd>
                        <dt class="col-md-3">Color</dt>
                        <dd class="col-md-9">{{ $clothingItem->color ?: '-' }}</dd>
                        <dt class="col-md-3">Brand</dt>
                        <dd class="col-md-9">{{ $clothingItem->brand ?: '-' }}</dd>
                        <dt class="col-md-3">Notes</dt>
                        <dd class="col-md-9">{!! nl2br(e($clothingItem->notes ?: '-')) !!}</dd>
                    </dl>
                </div>
            </div>

            <h2 class="h5 mt-4">Meetings</h2>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Contacts</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clothingItem->meetings as $meeting)
                            <tr>
                                <td>{{ $meeting->met_at?->format('Y-m-d') }}</td>
                                <td>{{ $meeting->displayTitle() }}</td>
                                <td>{{ $meeting->people->pluck('name')->join(', ') }}</td>
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
                <a href="{{ route('wardrobe.clothing-items.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
