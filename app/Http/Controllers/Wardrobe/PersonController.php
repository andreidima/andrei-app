<?php

namespace App\Http\Controllers\Wardrobe;

use App\Http\Controllers\Controller;
use App\Models\Wardrobe\Person;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $people = Person::query()
            ->withCount('meetings')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('notes', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->simplePaginate(50);

        return view('wardrobe.people.index', compact('people', 'search'));
    }

    public function create()
    {
        return view('wardrobe.people.create', ['person' => new Person()]);
    }

    public function store(Request $request)
    {
        $person = Person::create($this->validateRequest($request));

        return redirect()->route('wardrobe.people.index')
            ->with('status', 'Person "' . $person->name . '" was added.');
    }

    public function show(Person $person)
    {
        $person->load(['meetings' => function ($query) {
            $query->latest('met_at')->with('clothingItems');
        }]);

        return view('wardrobe.people.show', compact('person'));
    }

    public function edit(Person $person)
    {
        return view('wardrobe.people.edit', compact('person'));
    }

    public function update(Request $request, Person $person)
    {
        $person->update($this->validateRequest($request));

        return redirect()->route('wardrobe.people.index')
            ->with('status', 'Person "' . $person->name . '" was updated.');
    }

    public function destroy(Person $person)
    {
        $person->delete();

        return redirect()->route('wardrobe.people.index')
            ->with('status', 'Person "' . $person->name . '" was deleted.');
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'name' => 'required|max:200',
            'email' => 'nullable|email|max:200',
            'phone' => 'nullable|max:100',
            'notes' => 'nullable|max:5000',
        ]);
    }
}
