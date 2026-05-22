<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Apps\Aplicatie;
use App\Models\Apps\Feature;
use App\Models\Apps\FeatureImplementation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeatureController extends Controller
{
    public function index(Request $request)
    {
        $request->session()->forget('featureReturnUrl');

        $search = $request->search;
        $status = $request->status;

        $features = Feature::with('implementations')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', (bool) $status))
            ->orderBy('name')
            ->simplePaginate(50);

        return view('apps.features.index', compact('features', 'search', 'status'));
    }

    public function create(Request $request)
    {
        $request->session()->get('featureReturnUrl') ?? $request->session()->put('featureReturnUrl', url()->previous());

        return view('apps.features.create');
    }

    public function store(Request $request)
    {
        $attributes = $this->validateRequest($request);
        $attributes['slug'] = $attributes['slug'] ?: Str::slug($attributes['name']);
        $attributes['is_active'] = $request->boolean('is_active');

        $feature = Feature::create($attributes);

        return redirect($feature->path())->with('status', 'Feature-ul "' . $feature->name . '" a fost adaugat cu succes!');
    }

    public function show(Request $request, Feature $feature)
    {
        $request->session()->get('featureReturnUrl') ?? $request->session()->put('featureReturnUrl', url()->previous());

        $feature->load('implementations.aplicatie');
        $aplicatii = Aplicatie::orderBy('nume')->get();
        $implementationsByApp = $feature->implementations->keyBy('aplicatie_id');
        $statusOptions = FeatureImplementation::statusOptions();

        return view('apps.features.show', compact('feature', 'aplicatii', 'implementationsByApp', 'statusOptions'));
    }

    public function edit(Request $request, Feature $feature)
    {
        $request->session()->get('featureReturnUrl') ?? $request->session()->put('featureReturnUrl', url()->previous());

        return view('apps.features.edit', compact('feature'));
    }

    public function update(Request $request, Feature $feature)
    {
        $attributes = $this->validateRequest($request, $feature);
        $attributes['slug'] = $attributes['slug'] ?: Str::slug($attributes['name']);
        $attributes['is_active'] = $request->boolean('is_active');

        $feature->update($attributes);

        return redirect($feature->path())->with('status', 'Feature-ul "' . $feature->name . '" a fost modificat cu succes!');
    }

    public function destroy(Feature $feature)
    {
        $feature->implementations()->delete();
        $feature->delete();

        return redirect('/apps/features')->with('status', 'Feature-ul a fost sters cu succes!');
    }

    public function saveImplementations(Request $request, Feature $feature)
    {
        $validated = $request->validate([
            'implementations' => 'array',
            'implementations.*.status' => 'required|in:' . implode(',', array_keys(FeatureImplementation::statusOptions())),
            'implementations.*.git_commit' => 'nullable|max:100',
            'implementations.*.production_url' => 'nullable|max:500',
            'implementations.*.implemented_at' => 'nullable|date',
            'implementations.*.production_updated_at' => 'nullable|date',
            'implementations.*.notes' => 'nullable|max:5000',
            'implementations.*.differences' => 'nullable|max:5000',
        ]);

        foreach ($validated['implementations'] ?? [] as $aplicatieId => $attributes) {
            $hasUsefulData = collect($attributes)
                ->except('status')
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->isNotEmpty();

            if ($attributes['status'] === 'not_started' && ! $hasUsefulData) {
                FeatureImplementation::where('feature_id', $feature->id)
                    ->where('aplicatie_id', $aplicatieId)
                    ->delete();

                continue;
            }

            FeatureImplementation::updateOrCreate(
                [
                    'feature_id' => $feature->id,
                    'aplicatie_id' => $aplicatieId,
                ],
                $attributes
            );
        }

        return back()->with('status', 'Implementarile feature-ului au fost salvate.');
    }

    protected function validateRequest(Request $request, ?Feature $feature = null)
    {
        $featureId = $feature?->id;

        return $request->validate([
            'name' => 'required|max:255',
            'slug' => 'nullable|max:255|unique:apps_features,slug,' . $featureId,
            'category' => 'nullable|max:255',
            'description' => 'nullable|max:5000',
            'standard_prompt' => 'nullable',
            'implementation_notes' => 'nullable',
            'verification_notes' => 'nullable',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
