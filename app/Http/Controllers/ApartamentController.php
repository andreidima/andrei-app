<?php

namespace App\Http\Controllers;

use App\Models\Apartament;
use Illuminate\Http\Request;

class ApartamentController extends Controller
{
    public function index(Request $request)
    {
        $request->session()->forget('apartamentReturnUrl');

        $search = $request->search;
        $status = $request->status;

        $apartamente = Apartament::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('adresa', 'like', "%{$search}%")
                        ->orWhere('localitate', 'like', "%{$search}%")
                        ->orWhere('agentie', 'like', "%{$search}%");
                });
            })
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->orderByRaw("CASE WHEN vizionare_at IS NULL THEN 1 ELSE 0 END")
            ->orderBy('vizionare_at')
            ->orderByDesc('updated_at')
            ->simplePaginate(50);

        $statusOptions = $this->statusOptions();

        return view('apartamente.index', compact('apartamente', 'search', 'status', 'statusOptions'));
    }

    public function create(Request $request)
    {
        $request->session()->get('apartamentReturnUrl') ?? $request->session()->put('apartamentReturnUrl', url()->previous());

        $statusOptions = $this->statusOptions();

        return view('apartamente.create', compact('statusOptions'));
    }

    public function store(Request $request)
    {
        $apartament = Apartament::create($this->validateRequest($request));

        return redirect($request->session()->get('apartamentReturnUrl') ?? '/apartamente')
            ->with('status', 'Apartamentul "' . $apartament->adresa . '" a fost adaugat cu succes!');
    }

    public function show(Request $request, Apartament $apartament)
    {
        $request->session()->get('apartamentReturnUrl') ?? $request->session()->put('apartamentReturnUrl', url()->previous());

        return view('apartamente.show', compact('apartament'));
    }

    public function edit(Request $request, Apartament $apartament)
    {
        $request->session()->get('apartamentReturnUrl') ?? $request->session()->put('apartamentReturnUrl', url()->previous());

        $statusOptions = $this->statusOptions();

        return view('apartamente.edit', compact('apartament', 'statusOptions'));
    }

    public function update(Request $request, Apartament $apartament)
    {
        $apartament->update($this->validateRequest($request));

        return redirect($request->session()->get('apartamentReturnUrl') ?? '/apartamente')
            ->with('status', 'Apartamentul "' . $apartament->adresa . '" a fost modificat cu succes!');
    }

    public function destroy(Apartament $apartament)
    {
        $apartament->delete();

        return back()->with('status', 'Apartamentul "' . $apartament->adresa . '" a fost sters cu succes!');
    }

    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'adresa' => 'required|max:255',
            'localitate' => 'nullable|max:100',
            'status' => 'required|in:' . implode(',', array_keys($this->statusOptions())),
            'vizionare_at' => 'nullable|date',
            'pret' => 'nullable|integer|min:0',
            'cheltuieli_lunare' => 'nullable|integer|min:0',
            'costuri_extra_estimate' => 'nullable|integer|min:0',
            'suprafata_mp' => 'nullable|integer|min:0',
            'camere' => 'nullable|integer|min:0',
            'etaj' => 'nullable|integer|min:-5|max:200',
            'peb' => 'nullable|max:20',
            'are_lift' => 'nullable|boolean',
            'are_balcon' => 'nullable|boolean',
            'are_parcare' => 'nullable|boolean',
            'orientare_lumina' => 'nullable|max:255',
            'renovare_necesara' => 'nullable|max:255',
            'zgomot' => 'nullable|max:255',
            'zona' => 'nullable|max:255',
            'link_anunt' => 'nullable|url|max:500',
            'agentie' => 'nullable|max:255',
            'contact' => 'nullable|max:255',
            'puncte_bune' => 'nullable|max:5000',
            'puncte_slabe' => 'nullable|max:5000',
            'riscuri_intrebari' => 'nullable|max:5000',
            'observatii' => 'nullable|max:5000',
            'scor' => 'nullable|integer|min:1|max:10',
        ]);
    }

    protected function statusOptions()
    {
        return [
            'de_vazut' => 'De vazut',
            'programat' => 'Programat',
            'vazut' => 'Vazut',
            'shortlist' => 'Shortlist',
            'de_revazut' => 'De revazut',
            'astept_raspuns' => 'Astept raspuns',
            'respins' => 'Respins',
            'oferta' => 'Oferta',
        ];
    }
}
