@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header culoare2 text-white">
            <div class="row align-items-center g-2">
                <div class="col-lg-3">
                    <span class="fs-5"><i class="fa-solid fa-users me-1"></i>Contacts</span>
                </div>
                <div class="col-lg-6">
                    <form method="GET" action="{{ route('wardrobe.people.index') }}">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Search contacts" value="{{ $search }}">
                            <button class="btn btn-primary text-white" type="submit"><i class="fa-solid fa-search me-1"></i>Search</button>
                            <a href="{{ route('wardrobe.people.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
                <div class="col-lg-3 text-lg-end">
                    <a href="{{ route('wardrobe.people.create') }}" class="btn btn-sm btn-success text-white">
                        <i class="fa-solid fa-plus me-1"></i>Add contact
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
                            <th>Name</th>
                            <th>Type</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th class="text-end">Meetings</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($people as $person)
                            <tr>
                                <td>{{ $person->name }}</td>
                                <td>{{ $person->contactTypeLabel() }}</td>
                                <td>{{ $person->email }}</td>
                                <td>{{ $person->phone }}</td>
                                <td class="text-end">{{ $person->meetings_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('wardrobe.people.show', $person) }}" class="badge bg-success text-decoration-none">View</a>
                                    <a href="{{ route('wardrobe.people.edit', $person) }}" class="badge bg-primary text-decoration-none">Edit</a>
                                    <a href="#" class="badge bg-danger text-decoration-none" data-bs-toggle="modal" data-bs-target="#deletePerson{{ $person->id }}">Delete</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No contacts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $people->appends(Request::except('page'))->links() }}
            </div>
        </div>
    </div>
</div>

@foreach ($people as $person)
    <div class="modal fade" id="deletePerson{{ $person->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete {{ $person->name }}</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Delete this contact from Wardrobe Tracker?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('wardrobe.people.destroy', $person) }}">
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
