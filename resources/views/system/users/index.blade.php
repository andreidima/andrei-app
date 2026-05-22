@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0 0;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-users-gear me-1"></i>Users
            </span>
        </div>
        <div class="col-lg-6 text-end">
            <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-4" href="{{ route('system.users.create') }}">
                <i class="fas fa-plus-square text-white me-1"></i>Add user
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include ('errors.errors')

        <div class="table-responsive rounded">
            <table class="table table-striped table-hover rounded align-middle">
                <thead class="text-white rounded">
                    <tr>
                        <th class="text-white culoare2">#</th>
                        <th class="text-white culoare2">Name</th>
                        <th class="text-white culoare2">Email</th>
                        <th class="text-white culoare2">Role</th>
                        <th class="text-white culoare2 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ ($users->currentpage() - 1) * $users->perpage() + $loop->index + 1 }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge {{ $user->isAdmin() ? 'bg-primary' : 'bg-success' }}">{{ App\Models\User::roleOptions()[$user->role] ?? $user->role }}</span></td>
                            <td>
                                <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 justify-content-sm-end">
                                    <a href="{{ route('system.users.edit', $user) }}"><span class="badge bg-primary">Edit</span></a>
                                    @if ($user->id !== auth()->id())
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#stergeUser{{ $user->id }}">
                                            <span class="badge bg-danger">Delete</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center">
                {{ $users->links() }}
            </ul>
        </nav>
    </div>
</div>

@foreach ($users as $user)
    <div class="modal fade text-dark" id="stergeUser{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">User: <b>{{ $user->name }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this user?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('system.users.destroy', $user) }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="btn btn-danger text-white">Delete user</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
