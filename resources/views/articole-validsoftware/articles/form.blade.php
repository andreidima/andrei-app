@csrf

@php
    use App\Models\ValidSoftwareBlog\BlogArticle;
@endphp

<div class="row px-3">
    <div class="col-lg-6 mb-3">
        <label class="mb-0 ps-3">Proiect<span class="text-danger">*</span></label>
        <select name="blog_project_id" class="form-select rounded-3">
            <option value="">Alege proiect</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" @selected((string) old('blog_project_id', $article->blog_project_id) === (string) $project->id)>{{ $project->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3 mb-3">
        <label class="mb-0 ps-3">Tip<span class="text-danger">*</span></label>
        <select name="type" class="form-select rounded-3">
            @foreach (BlogArticle::typeOptions() as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $article->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3 mb-3">
        <label class="mb-0 ps-3">Status<span class="text-danger">*</span></label>
        <select name="status" class="form-select rounded-3">
            @foreach (BlogArticle::statusOptions() as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $article->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-12 mb-3">
        <label class="mb-0 ps-3">Titlu<span class="text-danger">*</span></label>
        <input type="text" class="form-control rounded-3" name="title" value="{{ old('title', $article->title) }}">
        @if ($article->exists && $article->hasSimilarTypeWarning())
            <small class="text-warning ps-3">Atentie: proiectul are deja cel putin un articol cu acelasi tip.</small>
        @endif
    </div>

    <div class="col-lg-12 mb-3">
        <label class="mb-0 ps-3">Next action</label>
        <input type="text" class="form-control rounded-3" name="next_action" value="{{ old('next_action', $article->next_action) }}" placeholder="Ex: inspecteaza proiectul, scrie draft, trimite catre Vali">
    </div>

    <div class="col-lg-12 mb-3">
        <label class="mb-0 ps-3">Topic summary</label>
        <textarea name="topic_summary" class="form-control rounded-3" rows="3">{{ old('topic_summary', $article->topic_summary) }}</textarea>
    </div>

    <div class="col-lg-6 mb-3">
        <label class="mb-0 ps-3">Technical notes</label>
        <textarea name="technical_notes" class="form-control rounded-3" rows="7">{{ old('technical_notes', $article->technical_notes) }}</textarea>
    </div>
    <div class="col-lg-6 mb-3">
        <label class="mb-0 ps-3">Article notes</label>
        <textarea name="article_notes" class="form-control rounded-3" rows="7">{{ old('article_notes', $article->article_notes) }}</textarea>
    </div>

    <div class="col-lg-12 mb-3">
        <label class="mb-0 ps-3">Internal notes</label>
        <textarea name="internal_notes" class="form-control rounded-3" rows="4">{{ old('internal_notes', $article->internal_notes) }}</textarea>
    </div>

    <div class="col-lg-6 mb-3">
        <label class="mb-0 ps-3">Draft DOC/link</label>
        <input type="text" class="form-control rounded-3" name="draft_doc_link" value="{{ old('draft_doc_link', $article->draft_doc_link) }}">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="mb-0 ps-3">Published URL</label>
        <input type="text" class="form-control rounded-3" name="published_url" value="{{ old('published_url', $article->published_url) }}">
    </div>

    <div class="col-lg-6 mb-3">
        <label class="mb-0 ps-3">Trimis catre Vali la</label>
        <input type="date" class="form-control rounded-3" name="sent_to_vali_at" value="{{ old('sent_to_vali_at', optional($article->sent_to_vali_at)->toDateString()) }}">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="mb-0 ps-3">Publicat la</label>
        <input type="date" class="form-control rounded-3" name="published_at" value="{{ old('published_at', optional($article->published_at)->toDateString()) }}">
    </div>

    <div class="col-lg-12 mb-2 d-flex justify-content-center">
        <button type="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
        <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('blogArticleReturnUrl', route('validsoftware-blog.index')) }}">Renunta</a>
    </div>
</div>
