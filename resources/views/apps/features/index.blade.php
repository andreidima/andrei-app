@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0 0;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-layer-group me-1"></i>Features
            </span>
        </div>
        <div class="col-lg-6">
            <form method="GET" action="{{ url()->current() }}">
                <div class="row mb-1 custom-search-form justify-content-center g-2">
                    <div class="col-lg-7">
                        <input type="text" class="form-control rounded-3" name="search" value="{{ $search }}" placeholder="Feature, category, description">
                    </div>
                    <div class="col-lg-4">
                        <select name="status" class="form-select rounded-3">
                            <option value="">All</option>
                            <option value="1" @selected($status === '1')>Active</option>
                            <option value="0" @selected($status === '0')>Hidden</option>
                        </select>
                    </div>
                    <div class="col-lg-1">
                        <button type="submit" class="btn btn-primary text-white rounded-3 w-100">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-lg-3 text-end">
            <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                <i class="fas fa-plus-square text-white me-1"></i>Add feature
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
                        <th class="text-white culoare2">Feature</th>
                        <th class="text-white culoare2">Category</th>
                        <th class="text-white culoare2">Rollout</th>
                        <th class="text-white culoare2">Status</th>
                        <th class="text-white culoare2 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($features as $feature)
                        @php
                            $implemented = $feature->implementations->where('status', 'implemented')->count();
                            $partial = $feature->implementations->whereIn('status', ['partial', 'needs_review', 'in_progress'])->count();
                            $tracked = $feature->implementations->count();
                        @endphp
                        <tr>
                            <td>{{ ($features->currentpage() - 1) * $features->perpage() + $loop->index + 1 }}</td>
                            <td>
                                <div class="fw-bold">{{ $feature->name }}</div>
                                <div class="text-muted">{{ $feature->slug }}</div>
                                @if ($feature->description)
                                    <div class="small">{{ \Illuminate\Support\Str::limit($feature->description, 180) }}</div>
                                @endif
                            </td>
                            <td>{{ $feature->category }}</td>
                            <td>
                                <span class="badge bg-success">{{ $implemented }} implemented</span>
                                <span class="badge bg-warning text-dark">{{ $partial }} partial/review</span>
                                <span class="badge bg-secondary">{{ $tracked }} tracked</span>
                            </td>
                            <td>
                                <span class="badge {{ $feature->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $feature->is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 justify-content-sm-end">
                                    <a href="{{ $feature->path() }}"><span class="badge bg-success">View</span></a>
                                    <a href="{{ $feature->path() }}/modifica"><span class="badge bg-primary">Edit</span></a>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#stergeFeature{{ $feature->id }}">
                                        <span class="badge bg-danger">Delete</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No features found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center">
                {{ $features->appends(Request::except('page'))->links() }}
            </ul>
        </nav>
    </div>
</div>

@foreach ($features as $feature)
    <div class="modal fade text-dark" id="stergeFeature{{ $feature->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Feature: <b>{{ $feature->name }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this feature and its rollout records?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ $feature->path() }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="btn btn-danger text-white">Delete feature</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
