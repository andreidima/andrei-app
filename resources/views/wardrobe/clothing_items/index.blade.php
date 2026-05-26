@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="card shadow-sm">
        <div class="card-header culoare2 text-white">
            <div class="row align-items-center g-2">
                <div class="col-lg-3">
                    <span class="fs-5"><i class="fa-solid fa-shirt me-1"></i>Clothing items</span>
                </div>
                <div class="col-lg-7">
                    <form method="GET" action="{{ route('wardrobe.clothing-items.index') }}">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search items" value="{{ $search }}">
                            </div>
                            <div class="col-md-3">
                                <select name="category" class="form-select form-select-sm">
                                    <option value="">All categories</option>
                                    @foreach ($categories as $categoryOption)
                                        <option value="{{ $categoryOption }}" {{ $category == $categoryOption ? 'selected' : '' }}>{{ $categoryOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button class="btn btn-sm btn-primary text-white flex-fill" type="submit"><i class="fa-solid fa-search me-1"></i>Search</button>
                                <a href="{{ route('wardrobe.clothing-items.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-2 text-lg-end">
                    <a href="{{ route('wardrobe.clothing-items.create') }}" class="btn btn-sm btn-success text-white">
                        <i class="fa-solid fa-plus me-1"></i>Add item
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            @include('errors.errors')

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Color</th>
                            <th>Brand</th>
                            <th class="text-end">Meetings</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clothingItems as $clothingItem)
                            <tr>
                                <td style="width: 180px;">
                                    @if ($clothingItem->photoUrl())
                                        <img src="{{ $clothingItem->photoUrl() }}" alt="{{ $clothingItem->name }}" class="img-thumbnail" style="width: 160px; height: 160px; object-fit: cover;">
                                    @else
                                        <span class="d-inline-flex align-items-center justify-content-center bg-light border rounded" style="width: 160px; height: 160px;">
                                            <i class="fa-solid fa-shirt fa-2x text-muted"></i>
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $clothingItem->displayName() }}</td>
                                <td>{{ $clothingItem->category }}</td>
                                <td>{{ $clothingItem->color }}</td>
                                <td>{{ $clothingItem->brand }}</td>
                                <td class="text-end">{{ $clothingItem->meetings_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('wardrobe.clothing-items.show', $clothingItem) }}" class="badge bg-success text-decoration-none">View</a>
                                    <a href="{{ route('wardrobe.clothing-items.edit', $clothingItem) }}" class="badge bg-primary text-decoration-none">Edit</a>
                                    <a href="#" class="badge bg-danger text-decoration-none" data-bs-toggle="modal" data-bs-target="#deleteClothingItem{{ $clothingItem->id }}">Delete</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No clothing items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $clothingItems->appends(Request::except('page'))->links() }}
            </div>
        </div>
    </div>
</div>

@foreach ($clothingItems as $clothingItem)
    <div class="modal fade" id="deleteClothingItem{{ $clothingItem->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete {{ $clothingItem->name }}</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Delete this clothing item and remove it from meetings?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('wardrobe.clothing-items.destroy', $clothingItem) }}">
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
