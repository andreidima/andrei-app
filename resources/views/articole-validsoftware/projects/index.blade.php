@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0 0;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-diagram-project me-1"></i>Proiecte articole
            </span>
        </div>
        <div class="col-lg-6">
            <form method="GET" action="{{ route('validsoftware-blog.projects.index') }}">
                <div class="row g-2">
                    <div class="col-lg-10">
                        <input type="text" name="search" class="form-control rounded-3" value="{{ $search }}" placeholder="Cauta proiect">
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary text-white rounded-3 w-100">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-lg-3 text-end">
            <a class="btn btn-sm btn-outline-secondary rounded-3" href="{{ route('validsoftware-blog.index') }}">
                <i class="fa-solid fa-newspaper me-1"></i>Dashboard
            </a>
            <a class="btn btn-sm btn-success text-white border border-dark rounded-3" href="{{ route('validsoftware-blog.projects.create') }}">
                <i class="fas fa-plus-square text-white me-1"></i>Adauga proiect
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include ('errors.errors')

        <div class="table-responsive rounded">
            <table class="table table-striped table-hover rounded align-middle">
                <thead>
                    <tr>
                        <th class="text-white culoare2">#</th>
                        <th class="text-white culoare2">Proiect</th>
                        <th class="text-white culoare2">Local path</th>
                        <th class="text-white culoare2">Status</th>
                        <th class="text-white culoare2 text-center">Articole</th>
                        <th class="text-white culoare2 text-end">Actiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr>
                            <td>{{ ($projects->currentpage() - 1) * $projects->perpage() + $loop->index + 1 }}</td>
                            <td>
                                <div class="fw-bold">{{ $project->name }}</div>
                                <div class="small text-muted">{{ $project->public_name }}</div>
                            </td>
                            <td class="small">{{ $project->local_path }}</td>
                            <td><span class="badge bg-secondary">{{ $project->statusLabel() }}</span></td>
                            <td class="text-center">{{ $project->articles_count }}</td>
                            <td>
                                <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 justify-content-sm-end">
                                    <a href="{{ $project->path() }}"><span class="badge bg-success">Vezi</span></a>
                                    <a href="{{ $project->path() }}/modifica"><span class="badge bg-primary">Modifica</span></a>
                                    <a href="{{ route('validsoftware-blog.articles.create', ['project_id' => $project->id]) }}"><span class="badge bg-info text-dark">Articol nou</span></a>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#stergeProiect{{ $project->id }}">
                                        <span class="badge bg-danger">Sterge</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Nu exista proiecte.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center">
                {{ $projects->appends(Request::except('page'))->links() }}
            </ul>
        </nav>
    </div>
</div>

@foreach ($projects as $project)
    <div class="modal fade text-dark" id="stergeProiect{{ $project->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Proiect: <b>{{ $project->name }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Esti sigur ca vrei sa stergi proiectul? Se vor sterge si articolele lui.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunta</button>
                    <form method="POST" action="{{ $project->path() }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="btn btn-danger text-white">Sterge proiect</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
