<?php

namespace App\Http\Controllers\ValidSoftwareBlog;

use App\Http\Controllers\Controller;
use App\Models\ValidSoftwareBlog\BlogProject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogProjectController extends Controller
{
    public function index(Request $request)
    {
        $request->session()->forget('blogProjectReturnUrl');

        $search = $request->string('search')->toString();

        $projects = BlogProject::withCount('articles')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('public_name', 'like', '%' . $search . '%')
                    ->orWhere('client_name', 'like', '%' . $search . '%')
                    ->orWhere('local_path', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->simplePaginate(50);

        return view('articole-validsoftware.projects.index', compact('projects', 'search'));
    }

    public function create(Request $request)
    {
        $this->setReturnUrl($request);

        return view('articole-validsoftware.projects.create', [
            'project' => new BlogProject(['status' => 'active']),
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $this->validateRequest($request);
        $attributes['slug'] = $attributes['slug'] ?: Str::slug($attributes['name']);

        $project = BlogProject::create($attributes);

        return redirect($this->getReturnUrl($request))
            ->with('status', 'Proiectul "' . $project->name . '" a fost adaugat.');
    }

    public function show(Request $request, BlogProject $project)
    {
        $this->setReturnUrl($request);

        $project->load(['articles' => fn ($query) => $query->latest()]);

        return view('articole-validsoftware.projects.show', compact('project'));
    }

    public function edit(Request $request, BlogProject $project)
    {
        $this->setReturnUrl($request);

        return view('articole-validsoftware.projects.edit', compact('project'));
    }

    public function update(Request $request, BlogProject $project)
    {
        $attributes = $this->validateRequest($request, $project);
        $attributes['slug'] = $attributes['slug'] ?: Str::slug($attributes['name']);

        $project->update($attributes);

        return redirect($this->getReturnUrl($request))
            ->with('status', 'Proiectul "' . $project->name . '" a fost modificat.');
    }

    public function destroy(Request $request, BlogProject $project)
    {
        $project->delete();

        return back()->with('status', 'Proiectul si articolele lui au fost sterse.');
    }

    protected function validateRequest(Request $request, ?BlogProject $project = null): array
    {
        return $request->validate([
            'name' => ['required', 'max:255'],
            'slug' => ['nullable', 'max:255', Rule::unique('validsoftware_blog_projects', 'slug')->ignore($project?->id)],
            'local_path' => ['nullable', 'max:500'],
            'client_name' => ['nullable', 'max:255'],
            'public_name' => ['nullable', 'max:255'],
            'status' => ['required', Rule::in(array_keys(BlogProject::statusOptions()))],
            'notes' => ['nullable', 'max:10000'],
        ]);
    }

    protected function setReturnUrl(Request $request): void
    {
        $request->session()->get('blogProjectReturnUrl')
            ?? $request->session()->put('blogProjectReturnUrl', url()->previous());
    }

    protected function getReturnUrl(Request $request): string
    {
        return $request->session()->get('blogProjectReturnUrl') ?? route('validsoftware-blog.projects.index');
    }
}
