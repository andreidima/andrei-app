<?php

namespace App\Http\Controllers\Wardrobe;

use App\Http\Controllers\Controller;
use App\Models\Wardrobe\ClothingItem;
use App\Models\Wardrobe\Meeting;
use App\Models\Wardrobe\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $personId = $request->input('person_id');
        $clothingItemId = $request->input('clothing_item_id');

        $meetings = Meeting::query()
            ->with(['people', 'clothingItems'])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('location', 'like', '%' . $search . '%')
                        ->orWhere('clothes_description', 'like', '%' . $search . '%')
                        ->orWhere('notes', 'like', '%' . $search . '%')
                        ->orWhereHas('people', fn ($query) => $query->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('clothingItems', fn ($query) => $query->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->when($personId, fn ($query, $personId) => $query->whereHas('people', fn ($query) => $query->whereKey($personId)))
            ->when($clothingItemId, fn ($query, $clothingItemId) => $query->whereHas('clothingItems', fn ($query) => $query->whereKey($clothingItemId)))
            ->latest('met_at')
            ->simplePaginate(25);

        return view('wardrobe.meetings.index', array_merge(
            compact('meetings', 'search', 'personId', 'clothingItemId'),
            $this->formOptions()
        ));
    }

    public function create()
    {
        return view('wardrobe.meetings.create', array_merge(
            ['meeting' => new Meeting()],
            $this->formOptions()
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        $people = $data['people'] ?? [];
        $clothingItems = $data['clothing_items'] ?? [];
        unset($data['people'], $data['clothing_items'], $data['outfit_photo'], $data['remove_outfit_photo']);

        $data['outfit_photo_path'] = $this->storePhoto($request);

        $meeting = Meeting::create($data);
        $meeting->people()->sync($people);
        $meeting->clothingItems()->sync($clothingItems);

        return redirect()->route('wardrobe.meetings.index')
            ->with('status', 'Meeting was added.');
    }

    public function show(Meeting $meeting)
    {
        $meeting->load(['people', 'clothingItems']);

        return view('wardrobe.meetings.show', compact('meeting'));
    }

    public function edit(Meeting $meeting)
    {
        $meeting->load(['people', 'clothingItems']);

        return view('wardrobe.meetings.edit', array_merge(
            compact('meeting'),
            $this->formOptions()
        ));
    }

    public function update(Request $request, Meeting $meeting)
    {
        $data = $this->validateRequest($request);
        $people = $data['people'] ?? [];
        $clothingItems = $data['clothing_items'] ?? [];
        unset($data['people'], $data['clothing_items'], $data['outfit_photo'], $data['remove_outfit_photo']);

        if ($request->boolean('remove_outfit_photo')) {
            $this->deletePhoto($meeting->outfit_photo_path);
            $data['outfit_photo_path'] = null;
        }

        if ($request->hasFile('outfit_photo')) {
            $this->deletePhoto($meeting->outfit_photo_path);
            $data['outfit_photo_path'] = $this->storePhoto($request);
        }

        $meeting->update($data);
        $meeting->people()->sync($people);
        $meeting->clothingItems()->sync($clothingItems);

        return redirect()->route('wardrobe.meetings.index')
            ->with('status', 'Meeting was updated.');
    }

    public function destroy(Meeting $meeting)
    {
        $this->deletePhoto($meeting->outfit_photo_path);
        $meeting->delete();

        return redirect()->route('wardrobe.meetings.index')
            ->with('status', 'Meeting was deleted.');
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'title' => 'nullable|max:200',
            'met_at' => 'required|date',
            'location' => 'nullable|max:200',
            'clothes_description' => 'nullable|max:10000',
            'notes' => 'nullable|max:5000',
            'people' => 'nullable|array',
            'people.*' => 'exists:wardrobe_people,id',
            'clothing_items' => 'nullable|array',
            'clothing_items.*' => 'exists:wardrobe_clothing_items,id',
            'outfit_photo' => 'nullable|image|max:5120',
            'remove_outfit_photo' => 'nullable|boolean',
        ]);
    }

    protected function formOptions(): array
    {
        return [
            'people' => Person::orderBy('name')->get(),
            'clothingItems' => ClothingItem::orderBy('name')->get(),
        ];
    }

    protected function storePhoto(Request $request): ?string
    {
        return $request->hasFile('outfit_photo')
            ? $request->file('outfit_photo')->store('wardrobe/meetings', 'public')
            : null;
    }

    protected function deletePhoto(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
