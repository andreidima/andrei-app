<?php

namespace App\Http\Controllers\Wardrobe;

use App\Http\Controllers\Controller;
use App\Models\Wardrobe\ClothingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClothingItemController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $category = trim((string) $request->input('category', ''));

        $clothingItems = ClothingItem::query()
            ->withCount('meetings')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('category', 'like', '%' . $search . '%')
                        ->orWhere('color', 'like', '%' . $search . '%')
                        ->orWhere('brand', 'like', '%' . $search . '%')
                        ->orWhere('notes', 'like', '%' . $search . '%');
                });
            })
            ->when($category, fn ($query, $category) => $query->where('category', $category))
            ->orderBy('name')
            ->simplePaginate(50);

        $categories = ClothingItem::query()
            ->whereNotNull('category')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('wardrobe.clothing_items.index', compact('clothingItems', 'search', 'category', 'categories'));
    }

    public function create()
    {
        return view('wardrobe.clothing_items.create', array_merge(
            ['clothingItem' => new ClothingItem()],
            $this->formOptions()
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        $data = $this->prepareData($data);
        $data['photo_path'] = $this->storePhoto($request);
        unset($data['photo'], $data['remove_photo']);

        $clothingItem = ClothingItem::create($data);

        return redirect()->route('wardrobe.clothing-items.index')
            ->with('status', 'Clothing item "' . $clothingItem->displayName() . '" was added.');
    }

    public function show(ClothingItem $clothingItem)
    {
        $clothingItem->load(['meetings' => function ($query) {
            $query->latest('met_at')->with('people');
        }]);

        return view('wardrobe.clothing_items.show', compact('clothingItem'));
    }

    public function edit(ClothingItem $clothingItem)
    {
        return view('wardrobe.clothing_items.edit', array_merge(
            compact('clothingItem'),
            $this->formOptions()
        ));
    }

    public function update(Request $request, ClothingItem $clothingItem)
    {
        $data = $this->validateRequest($request);
        $data = $this->prepareData($data);

        if ($request->boolean('remove_photo')) {
            $this->deletePhoto($clothingItem->photo_path);
            $data['photo_path'] = null;
        }

        if ($request->hasFile('photo')) {
            $this->deletePhoto($clothingItem->photo_path);
            $data['photo_path'] = $this->storePhoto($request);
        }
        unset($data['photo'], $data['remove_photo']);

        $clothingItem->update($data);

        return redirect()->route('wardrobe.clothing-items.index')
            ->with('status', 'Clothing item "' . $clothingItem->displayName() . '" was updated.');
    }

    public function destroy(ClothingItem $clothingItem)
    {
        $this->deletePhoto($clothingItem->photo_path);
        $clothingItem->delete();

        return redirect()->route('wardrobe.clothing-items.index')
            ->with('status', 'Clothing item "' . $clothingItem->displayName() . '" was deleted.');
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'name' => 'nullable|required_without:category|max:200',
            'category' => 'nullable|max:120',
            'color' => 'nullable|max:120',
            'brand' => 'nullable|max:120',
            'notes' => 'nullable|max:5000',
            'photo' => 'nullable|image|max:5120',
            'remove_photo' => 'nullable|boolean',
        ]);
    }

    protected function prepareData(array $data): array
    {
        foreach (['name', 'category', 'color', 'brand'] as $field) {
            $data[$field] = trim((string) ($data[$field] ?? '')) ?: null;
        }

        if ($data['category']) {
            $category = strtolower($data['category']);
            $data['category'] = match ($category) {
                'shirt', 'shirts' => 'shirt',
                'short', 'shorts' => 'shorts',
                default => $category,
            };
        }

        if ($data['color']) {
            $data['color'] = strtolower($data['color']);
        }

        if ($data['brand']) {
            $data['brand'] = ucwords(strtolower($data['brand']));
        }

        if (! $data['name']) {
            $data['name'] = collect([$data['brand'], $data['color'], $data['category']])
                ->filter()
                ->join(' ') ?: 'Clothing item';
        }

        return $data;
    }

    protected function formOptions(): array
    {
        $defaultCategories = collect(['shirt', 'shorts', 'pants', 'dress', 'skirt', 'jacket', 'sweater', 'shoes', 'accessory']);

        return [
            'categories' => ClothingItem::whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->merge($defaultCategories)->unique()->sort()->values(),
            'colors' => ClothingItem::whereNotNull('color')->distinct()->orderBy('color')->pluck('color'),
            'brands' => ClothingItem::whereNotNull('brand')->distinct()->orderBy('brand')->pluck('brand'),
        ];
    }

    protected function storePhoto(Request $request): ?string
    {
        return $request->hasFile('photo')
            ? $request->file('photo')->store('wardrobe/clothing-items', 'public')
            : null;
    }

    protected function deletePhoto(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
