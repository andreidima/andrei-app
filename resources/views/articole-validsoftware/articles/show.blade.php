@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="shadow-lg" style="border-radius: 40px;">
        <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0 0;">
            <span class="badge text-light fs-5">
                <i class="fa-solid fa-newspaper me-1"></i>{{ $article->title }}
            </span>
        </div>
        <div class="card-body py-3 border border-secondary" style="border-radius: 0 0 40px 40px;">
            @include ('errors.errors')

            @if ($article->hasSimilarTypeWarning())
                <div class="alert alert-warning">
                    Atentie: proiectul are mai multe articole de acelasi tip. Verifica sa nu se suprapuna subiectele.
                </div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div><b>Proiect:</b> <a href="{{ $article->project?->path() }}">{{ $article->project->name ?? '' }}</a></div>
                        <div><b>Tip:</b> {{ $article->typeLabel() }}</div>
                        <div><b>Status:</b> <span class="badge {{ $article->statusBadgeClass() }}">{{ $article->statusLabel() }}</span></div>
                        <div><b>Trimis catre Vali:</b> {{ optional($article->sent_to_vali_at)->format('d.m.Y') }}</div>
                        <div><b>Publicat:</b> {{ optional($article->published_at)->format('d.m.Y') }}</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div><b>Next action:</b> {{ $article->next_action }}</div>
                        <div><b>Draft DOC/link:</b> {{ $article->draft_doc_link }}</div>
                        <div><b>Published URL:</b>
                            @if ($article->published_url)
                                <a href="{{ $article->published_url }}" target="_blank">{{ $article->published_url }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @foreach ([
                'Topic summary' => $article->topic_summary,
                'Technical notes' => $article->technical_notes,
                'Article notes' => $article->article_notes,
                'Internal notes' => $article->internal_notes,
            ] as $label => $value)
                <div class="border rounded-3 p-3 mb-3">
                    <div class="fw-bold mb-2">{{ $label }}</div>
                    <div style="white-space: pre-wrap;">{{ $value }}</div>
                </div>
            @endforeach

            <div class="text-center mt-3">
                <a class="btn btn-secondary rounded-3" href="{{ Session::get('blogArticleReturnUrl', route('validsoftware-blog.index')) }}">Inapoi</a>
                <a class="btn btn-primary text-white rounded-3" href="{{ $article->path() }}/modifica">Modifica</a>
            </div>
        </div>
    </div>
</div>
@endsection
