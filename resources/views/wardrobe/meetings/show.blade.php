@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header culoare2 text-white d-flex justify-content-between align-items-center">
            <span class="fs-5"><i class="fa-solid fa-calendar-days me-1"></i>{{ $meeting->title ?: 'Meeting' }}</span>
            <a href="{{ route('wardrobe.meetings.edit', $meeting) }}" class="btn btn-sm btn-primary text-white">Edit</a>
        </div>
        <div class="card-body">
            @include('errors.errors')

            <div class="row g-4">
                <div class="col-md-4">
                    @if ($meeting->outfitPhotoUrl())
                        <img src="{{ $meeting->outfitPhotoUrl() }}" alt="Outfit photo" class="img-fluid img-thumbnail">
                    @else
                        <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="height: 220px;">
                            <i class="fa-solid fa-image fa-3x text-muted"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-8">
                    <dl class="row">
                        <dt class="col-md-3">Date</dt>
                        <dd class="col-md-9">{{ $meeting->met_at?->format('Y-m-d H:i') }}</dd>
                        <dt class="col-md-3">Location</dt>
                        <dd class="col-md-9">{{ $meeting->location ?: '-' }}</dd>
                        <dt class="col-md-3">People</dt>
                        <dd class="col-md-9">
                            @forelse ($meeting->people as $person)
                                <a href="{{ route('wardrobe.people.show', $person) }}" class="badge bg-primary text-white text-decoration-none">{{ $person->name }}</a>
                            @empty
                                -
                            @endforelse
                        </dd>
                        <dt class="col-md-3">Description</dt>
                        <dd class="col-md-9">{!! nl2br(e($meeting->clothes_description ?: '-')) !!}</dd>
                        <dt class="col-md-3">Notes</dt>
                        <dd class="col-md-9">{!! nl2br(e($meeting->notes ?: '-')) !!}</dd>
                    </dl>
                </div>
            </div>

            <h2 class="h5 mt-4">Clothing items</h2>
            <div class="row g-3">
                @forelse ($meeting->clothingItems as $clothingItem)
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="{{ route('wardrobe.clothing-items.show', $clothingItem) }}" class="text-decoration-none text-dark">
                            @if ($clothingItem->photoUrl())
                                <img src="{{ $clothingItem->photoUrl() }}" alt="{{ $clothingItem->name }}" class="img-thumbnail w-100" style="aspect-ratio: 1; object-fit: cover;">
                            @else
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="aspect-ratio: 1;">
                                    <i class="fa-solid fa-shirt fa-2x text-muted"></i>
                                </div>
                            @endif
                            <div class="small mt-1">{{ $clothingItem->name }}</div>
                        </a>
                    </div>
                @empty
                    <div class="col-12 text-muted">No clothing items selected.</div>
                @endforelse
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('wardrobe.meetings.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
