@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="card shadow-sm">
        <div class="card-header culoare2 text-white">
            <div class="row align-items-center g-2">
                <div class="col-lg-2">
                    <span class="fs-5"><i class="fa-solid fa-calendar-days me-1"></i>Meetings</span>
                </div>
                <div class="col-lg-8">
                    <form method="GET" action="{{ route('wardrobe.meetings.index') }}">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search meetings" value="{{ $search }}">
                            </div>
                            <div class="col-md-3">
                                <select name="person_id" class="form-select form-select-sm">
                                    <option value="">All people</option>
                                    @foreach ($people as $person)
                                        <option value="{{ $person->id }}" {{ (string) $personId === (string) $person->id ? 'selected' : '' }}>{{ $person->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="clothing_item_id" class="form-select form-select-sm">
                                    <option value="">All clothing items</option>
                                    @foreach ($clothingItems as $clothingItem)
                                        <option value="{{ $clothingItem->id }}" {{ (string) $clothingItemId === (string) $clothingItem->id ? 'selected' : '' }}>{{ $clothingItem->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button class="btn btn-sm btn-primary text-white flex-fill" type="submit"><i class="fa-solid fa-search me-1"></i>Search</button>
                                <a href="{{ route('wardrobe.meetings.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-2 text-lg-end">
                    <a href="{{ route('wardrobe.meetings.create') }}" class="btn btn-sm btn-success text-white">
                        <i class="fa-solid fa-plus me-1"></i>Add meeting
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            @include('errors.errors')

            <div class="row g-3">
                @forelse ($meetings as $meeting)
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="row g-3 align-items-start">
                                <div class="col-md-2">
                                    @if ($meeting->outfitPhotoUrl())
                                        <img src="{{ $meeting->outfitPhotoUrl() }}" alt="Outfit photo" class="img-fluid img-thumbnail" style="width: 100%; max-height: 220px; object-fit: cover;">
                                    @else
                                        <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="height: 140px;">
                                            <i class="fa-solid fa-image fa-2x text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-7">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <h2 class="h5 mb-0">{{ $meeting->title ?: 'Meeting' }}</h2>
                                        <span class="badge bg-secondary">{{ $meeting->met_at?->format('Y-m-d H:i') }}</span>
                                        @if ($meeting->location)
                                            <span class="badge bg-info text-dark">{{ $meeting->location }}</span>
                                        @endif
                                    </div>
                                    <div class="mb-2">
                                        @forelse ($meeting->people as $person)
                                            <a href="{{ route('wardrobe.people.show', $person) }}" class="badge bg-primary text-white text-decoration-none">{{ $person->name }}</a>
                                        @empty
                                            <span class="text-muted">No people selected.</span>
                                        @endforelse
                                    </div>
                                    <div>{!! nl2br(e($meeting->clothes_description ?: 'No clothes description.')) !!}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex flex-wrap gap-2 justify-content-md-end mb-3">
                                        @forelse ($meeting->clothingItems as $clothingItem)
                                            <a href="{{ route('wardrobe.clothing-items.show', $clothingItem) }}" class="text-decoration-none text-dark text-center" title="{{ $clothingItem->name }}">
                                                @if ($clothingItem->photoUrl())
                                                    <img src="{{ $clothingItem->photoUrl() }}" alt="{{ $clothingItem->name }}" class="img-thumbnail d-block" style="width: 58px; height: 58px; object-fit: cover;">
                                                @else
                                                    <span class="d-flex align-items-center justify-content-center bg-light border rounded" style="width: 58px; height: 58px;">
                                                        <i class="fa-solid fa-shirt text-muted"></i>
                                                    </span>
                                                @endif
                                                <span class="small d-block text-truncate" style="max-width: 58px;">{{ $clothingItem->name }}</span>
                                            </a>
                                        @empty
                                            <span class="text-muted">No items selected.</span>
                                        @endforelse
                                    </div>
                                    <div class="text-md-end">
                                        <a href="{{ route('wardrobe.meetings.show', $meeting) }}" class="badge bg-success text-decoration-none">View</a>
                                        <a href="{{ route('wardrobe.meetings.edit', $meeting) }}" class="badge bg-primary text-decoration-none">Edit</a>
                                        <a href="#" class="badge bg-danger text-decoration-none" data-bs-toggle="modal" data-bs-target="#deleteMeeting{{ $meeting->id }}">Delete</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4">No meetings found.</div>
                @endforelse
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $meetings->appends(Request::except('page'))->links() }}
            </div>
        </div>
    </div>
</div>

@foreach ($meetings as $meeting)
    <div class="modal fade" id="deleteMeeting{{ $meeting->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete meeting</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Delete this meeting from Wardrobe Tracker?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('wardrobe.meetings.destroy', $meeting) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger text-white">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
