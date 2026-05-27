@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="shadow-lg" style="border-radius: 40px;">
        <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0 0;">
            <span class="badge text-light fs-5">
                <i class="fa-solid fa-diagram-project me-1"></i>{{ $project->name }}
            </span>
        </div>
        <div class="card-body py-3 border border-secondary" style="border-radius: 0 0 40px 40px;">
            @include ('errors.errors')

            <div class="row g-3 mb-3">
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div><b>Nume public:</b> {{ $project->public_name }}</div>
                        <div><b>Client:</b> {{ $project->client_name }}</div>
                        <div><b>Status:</b> {{ $project->statusLabel() }}</div>
                        <div><b>Local path:</b> {{ $project->local_path }}</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="fw-bold mb-2">Notes</div>
                        <div style="white-space: pre-wrap;">{{ $project->notes }}</div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <h5>Articole</h5>
                <a class="btn btn-sm btn-success text-white rounded-3" href="{{ route('validsoftware-blog.articles.create', ['project_id' => $project->id]) }}">
                    <i class="fa-solid fa-plus me-1"></i>Articol nou
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Titlu</th>
                            <th>Tip</th>
                            <th>Status</th>
                            <th>Next action</th>
                            <th class="text-end">Actiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($project->articles as $article)
                            <tr>
                                <td>{{ $article->title }}</td>
                                <td>{{ $article->typeLabel() }}</td>
                                <td><span class="badge {{ $article->statusBadgeClass() }}">{{ $article->statusLabel() }}</span></td>
                                <td>{{ $article->next_action }}</td>
                                <td class="text-end">
                                    <a href="{{ $article->path() }}"><span class="badge bg-success">Vezi</span></a>
                                    <a href="{{ $article->path() }}/modifica"><span class="badge bg-primary">Modifica</span></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">Nu exista articole pentru proiect.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-3">
                <a class="btn btn-secondary rounded-3" href="{{ Session::get('blogProjectReturnUrl', route('validsoftware-blog.projects.index')) }}">Inapoi</a>
                <a class="btn btn-primary text-white rounded-3" href="{{ $project->path() }}/modifica">Modifica</a>
            </div>
        </div>
    </div>
</div>
@endsection
