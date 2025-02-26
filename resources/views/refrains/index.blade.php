@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0 0;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-ban me-1"></i>
                Refrains
            </span>
        </div>

        {{-- Search form --}}
        <div class="col-lg-6">
            <form class="needs-validation" novalidate method="GET" action="{{ url()->current() }}">
                @csrf
                <div class="row mb-1 custom-search-form justify-content-center">
                    <div class="col-lg-6">
                        <input type="text" class="form-control rounded-3" id="searchNume" name="searchNume" placeholder="Refrain name" value="{{ $searchName }}">
                    </div>
                </div>
                <div class="row custom-search-form justify-content-center">
                    <div class="col-lg-4">
                        <button class="btn btn-sm w-100 btn-primary text-white border border-dark rounded-3" type="submit">
                            <i class="fas fa-search text-white me-1"></i>Search
                        </button>
                    </div>
                    <div class="col-lg-4">
                        <a class="btn btn-sm w-100 btn-secondary text-white border border-dark rounded-3" href="{{ url()->current() }}" role="button">
                            <i class="far fa-trash-alt text-white me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Button to add new refrain --}}
        <div class="col-lg-3 text-end">
            <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                <i class="fas fa-plus text-white me-1"></i> Add Refrain
            </a>
        </div>
    </div>

    {{-- Card Body --}}
    <div class="card-body px-0 py-3">
        @include ('errors.errors')
        <div class="table-responsive rounded">
            <table class="table table-striped table-hover rounded">
                <thead class="text-white rounded">
                    <tr class="thead-danger">
                        <th class="text-white culoare2"><i class="fa-solid fa-hashtag"></i></th>
                        <th class="text-white culoare2">Name</th>
                        <th class="text-white culoare2">Since</th>
                        <th class="text-white culoare2 text-end"><i class="fa-solid fa-cogs me-1"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($refrains as $refrain)
                        <tr>
                            <td>{{ ($refrains->currentpage() - 1) * $refrains->perpage() + $loop->index + 1 }}</td>
                            <td>{{ $refrain->name ?? '-' }}</td>
                            <td>
                                {{ $refrain->since ? \Carbon\Carbon::parse($refrain->since)->format('d.m.Y') : '-' }}
                            </td>
                            <td>
                                <div class="d-flex justify-content-end py-0">
                                    <a href="{{ $refrain->path() }}" class="flex me-1">
                                        <span class="badge bg-success" title="Show"><i class="fa-solid fa-eye"></i></span>
                                    </a>
                                    <a href="{{ $refrain->path('edit') }}" class="flex me-1">
                                        <span class="badge bg-primary" title="Modify"><i class="fa-solid fa-edit"></i></span>
                                    </a>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#stergeAchievement{{ $refrain->id }}" title="Șterge Realizare">
                                        <span class="badge bg-danger" title="Delete"><i class="fa-solid fa-trash"></i></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                <i class="fa-solid fa-exclamation-circle me-1"></i> Nu s-au găsit realizări în baza de date.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center">
                {{ $refrains->appends(Request::except('page'))->links() }}
            </ul>
        </nav>
    </div>
</div>

{{-- Modals to delete refrains --}}
@foreach ($refrains as $refrain)
    <div class="modal fade text-dark" id="stergeAchievement{{ $refrain->id }}" tabindex="-1" role="dialog" aria-labelledby="stergeAchievementLabel{{ $refrain->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="stergeAchievementLabel{{ $refrain->id }}">
                        <i class="fa-solid fa-trash-alt me-1"></i> Refrain: <b>{{ $refrain->name }}</b>
                    </h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    Are you sure that you want to delete this refrain?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ $refrain->path('destroy') }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="btn btn-danger text-white">
                            <i class="fa-solid fa-trash me-1"></i> Șterge Refrain
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
