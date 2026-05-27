<?php

namespace App\Http\Controllers\ValidSoftwareBlog;

use App\Http\Controllers\Controller;
use App\Models\ValidSoftwareBlog\BlogArticle;
use App\Models\ValidSoftwareBlog\BlogProject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlogArticleController extends Controller
{
    public function index(Request $request)
    {
        $request->session()->forget('blogArticleReturnUrl');
        $request->session()->forget('blogProjectReturnUrl');

        $search = $request->string('search')->toString();
        $projectId = $request->input('project_id');
        $type = $request->input('type');
        $status = $request->input('status');

        $articles = BlogArticle::with('project')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('topic_summary', 'like', '%' . $search . '%')
                        ->orWhere('next_action', 'like', '%' . $search . '%')
                        ->orWhereHas('project', fn ($query) => $query->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->when($projectId, fn ($query, $projectId) => $query->where('blog_project_id', $projectId))
            ->when($type, fn ($query, $type) => $query->where('type', $type))
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->simplePaginate(30);

        $projects = BlogProject::withCount('articles')->orderBy('name')->get();
        $statusCounts = BlogArticle::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $projectsWithoutArticles = BlogProject::doesntHave('articles')->orderBy('name')->get();
        $nextActions = BlogArticle::with('project')
            ->whereNotNull('next_action')
            ->whereNotIn('status', ['published'])
            ->orderByRaw("case status when 'needs_revision' then 1 when 'needs_project_review' then 2 when 'not_started' then 3 else 4 end")
            ->latest()
            ->limit(8)
            ->get();

        $duplicateWarnings = BlogArticle::query()
            ->selectRaw('blog_project_id, type, count(*) as total')
            ->with('project')
            ->groupBy('blog_project_id', 'type')
            ->having('total', '>', 1)
            ->get();

        return view('articole-validsoftware.index', compact(
            'articles',
            'projects',
            'statusCounts',
            'projectsWithoutArticles',
            'nextActions',
            'duplicateWarnings',
            'search',
            'projectId',
            'type',
            'status'
        ));
    }

    public function create(Request $request)
    {
        $this->setReturnUrl($request);

        $article = new BlogArticle([
            'blog_project_id' => $request->input('project_id'),
            'type' => $request->input('type', 'project_case_study'),
            'status' => 'not_started',
        ]);

        return view('articole-validsoftware.articles.create', [
            'article' => $article,
            'projects' => BlogProject::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $article = BlogArticle::create($this->validateRequest($request));

        return redirect($this->getReturnUrl($request))
            ->with('status', 'Articolul "' . $article->title . '" a fost adaugat.');
    }

    public function show(Request $request, BlogArticle $article)
    {
        $this->setReturnUrl($request);

        $article->load('project');

        return view('articole-validsoftware.articles.show', compact('article'));
    }

    public function edit(Request $request, BlogArticle $article)
    {
        $this->setReturnUrl($request);

        return view('articole-validsoftware.articles.edit', [
            'article' => $article,
            'projects' => BlogProject::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, BlogArticle $article)
    {
        $article->update($this->validateRequest($request));

        return redirect($this->getReturnUrl($request))
            ->with('status', 'Articolul "' . $article->title . '" a fost modificat.');
    }

    public function destroy(Request $request, BlogArticle $article)
    {
        $article->delete();

        return back()->with('status', 'Articolul a fost sters.');
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'blog_project_id' => ['required', 'exists:validsoftware_blog_projects,id'],
            'title' => ['required', 'max:255'],
            'type' => ['required', Rule::in(array_keys(BlogArticle::typeOptions()))],
            'status' => ['required', Rule::in(array_keys(BlogArticle::statusOptions()))],
            'next_action' => ['nullable', 'max:255'],
            'topic_summary' => ['nullable', 'max:5000'],
            'technical_notes' => ['nullable'],
            'article_notes' => ['nullable'],
            'internal_notes' => ['nullable'],
            'draft_doc_link' => ['nullable', 'max:1000'],
            'published_url' => ['nullable', 'max:1000'],
            'sent_to_vali_at' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    protected function setReturnUrl(Request $request): void
    {
        $request->session()->get('blogArticleReturnUrl')
            ?? $request->session()->put('blogArticleReturnUrl', url()->previous());
    }

    protected function getReturnUrl(Request $request): string
    {
        return $request->session()->get('blogArticleReturnUrl') ?? route('validsoftware-blog.index');
    }
}
