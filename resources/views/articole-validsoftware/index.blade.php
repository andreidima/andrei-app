@extends ('layouts.app')

@php
    use App\Models\ValidSoftwareBlog\BlogArticle;
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0 0;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-newspaper me-1"></i>Articole ValidSoftware
            </span>
        </div>
        <div class="col-lg-6">
            <form method="GET" action="{{ route('validsoftware-blog.index') }}">
                <div class="row g-2 justify-content-center">
                    <div class="col-lg-4">
                        <input type="text" name="search" class="form-control rounded-3" value="{{ $search }}" placeholder="Cauta">
                    </div>
                    <div class="col-lg-3">
                        <select name="project_id" class="form-select rounded-3">
                            <option value="">Toate proiectele</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected((string) $projectId === (string) $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <select name="type" class="form-select rounded-3">
                            <option value="">Tip</option>
                            @foreach (BlogArticle::typeOptions() as $value => $label)
                                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <select name="status" class="form-select rounded-3">
                            <option value="">Status</option>
                            @foreach (BlogArticle::statusOptions() as $value => $label)
                                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                            @endforeach
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
            <a class="btn btn-sm btn-success text-white border border-dark rounded-3" href="{{ route('validsoftware-blog.articles.create') }}">
                <i class="fas fa-plus-square text-white me-1"></i>Adauga articol
            </a>
            <a class="btn btn-sm btn-outline-primary rounded-3" href="{{ route('validsoftware-blog.projects.index') }}">
                <i class="fa-solid fa-diagram-project me-1"></i>Proiecte
            </a>
        </div>
    </div>

    <div class="card-body py-3">
        @include ('errors.errors')

        <div class="row g-3 mb-3">
            @foreach (BlogArticle::statusOptions() as $value => $label)
                <div class="col-md-2">
                    <div class="border rounded-3 p-3 h-100 bg-light">
                        <div class="text-muted small">{{ $label }}</div>
                        <div class="fs-4 fw-bold">{{ $statusCounts[$value] ?? 0 }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($duplicateWarnings->count() || $projectsWithoutArticles->count() || $nextActions->count())
            <div class="row g-3 mb-3">
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>Atentionari suprapunere</div>
                        @forelse ($duplicateWarnings as $warning)
                            <div class="small mb-2">
                                <span class="badge bg-warning text-dark">{{ $warning->total }} articole</span>
                                {{ $warning->project->name ?? 'Proiect sters' }} -
                                {{ BlogArticle::typeOptions()[$warning->type] ?? $warning->type }}
                            </div>
                        @empty
                            <div class="text-muted small">Nu sunt suprapuneri pe acelasi proiect si tip.</div>
                        @endforelse
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="fw-bold mb-2"><i class="fa-solid fa-folder-open me-1"></i>Proiecte fara articole</div>
                        @forelse ($projectsWithoutArticles as $project)
                            <div class="small mb-2">
                                <a href="{{ $project->path() }}">{{ $project->name }}</a>
                            </div>
                        @empty
                            <div class="text-muted small">Toate proiectele au cel putin o idee de articol.</div>
                        @endforelse
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="fw-bold mb-2"><i class="fa-solid fa-list-check me-1"></i>Urmatoarele actiuni</div>
                        @forelse ($nextActions as $nextArticle)
                            <div class="small mb-2">
                                <a href="{{ $nextArticle->path() }}">{{ $nextArticle->project->name ?? '' }}</a>:
                                {{ $nextArticle->next_action }}
                            </div>
                        @empty
                            <div class="text-muted small">Nu sunt actiuni deschise.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        <div class="table-responsive rounded">
            <table class="table table-striped table-hover rounded align-middle">
                <thead>
                    <tr>
                        <th class="text-white culoare2">#</th>
                        <th class="text-white culoare2">Proiect</th>
                        <th class="text-white culoare2">Articol</th>
                        <th class="text-white culoare2">Tip</th>
                        <th class="text-white culoare2">Status</th>
                        <th class="text-white culoare2">Next action</th>
                        <th class="text-white culoare2 text-end">Actiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($articles as $article)
                        <tr>
                            <td>{{ ($articles->currentpage() - 1) * $articles->perpage() + $loop->index + 1 }}</td>
                            <td>
                                <a href="{{ $article->project?->path() }}">{{ $article->project->name ?? '' }}</a>
                                @if ($article->project?->local_path)
                                    <div class="text-muted small">{{ $article->project->local_path }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">{{ $article->title }}</div>
                                @if ($article->topic_summary)
                                    <div class="small">{{ Str::limit($article->topic_summary, 180) }}</div>
                                @endif
                                @if ($article->hasSimilarTypeWarning())
                                    <span class="badge bg-warning text-dark">Posibila suprapunere</span>
                                @endif
                            </td>
                            <td>{{ $article->typeLabel() }}</td>
                            <td><span class="badge {{ $article->statusBadgeClass() }}">{{ $article->statusLabel() }}</span></td>
                            <td class="small">{{ $article->next_action }}</td>
                            <td>
                                <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 justify-content-sm-end">
                                    <a href="{{ $article->path() }}"><span class="badge bg-success">Vezi</span></a>
                                    <a href="{{ $article->path() }}/modifica"><span class="badge bg-primary">Modifica</span></a>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#stergeArticol{{ $article->id }}">
                                        <span class="badge bg-danger">Sterge</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Nu exista articole.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center">
                {{ $articles->appends(Request::except('page'))->links() }}
            </ul>
        </nav>
    </div>
</div>

@foreach ($articles as $article)
    <div class="modal fade text-dark" id="stergeArticol{{ $article->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Articol: <b>{{ $article->title }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Esti sigur ca vrei sa stergi articolul?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunta</button>
                    <form method="POST" action="{{ $article->path() }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="btn btn-danger text-white">Sterge articol</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
