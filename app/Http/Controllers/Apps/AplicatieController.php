<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Apps\Aplicatie;
use Carbon\Carbon;

class AplicatieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('aplicatieReturnUrl');

        $searchNume = $request->searchNume;

        $query = Aplicatie::
            when($searchNume, function ($query, $searchNume) {
                return $query->where('nume', $searchNume);
            })
            ->latest();

        $aplicatii = $query->simplePaginate(50);

        return view('apps.aplicatii.index', compact('aplicatii', 'searchNume'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->session()->get('aplicatieReturnUrl') ?? $request->session()->put('aplicatieReturnUrl', url()->previous());

        return view('apps.aplicatii.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $aplicatie = Aplicatie::create($this->validateRequest($request));

        // Daca aplicatia a fost adaugata din formularul Pontaj, se trimite in sesiune, pentru a fi folosita in Pontaj
        if ($request->session()->exists('pontajRequest')) {
            $pontajRequest = $request->session()->put('pontajRequest.aplicatie_id', $aplicatie->id);
        }

        return redirect($request->session()->get('aplicatieReturnUrl') ?? ('/apps/aplicatii'))->with('status', 'Aplicația „' . $aplicatie->nume . '” a fost adăugată cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Aplicatie  $aplicatie
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Aplicatie $aplicatie)
    {
        $request->session()->get('aplicatieReturnUrl') ?? $request->session()->put('aplicatieReturnUrl', url()->previous());

        return view('apps.aplicatii.show', compact('aplicatie'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Aplicatie  $aplicatie
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Aplicatie $aplicatie)
    {
        $request->session()->get('aplicatieReturnUrl') ?? $request->session()->put('aplicatieReturnUrl', url()->previous());

        return view('apps.aplicatii.edit', compact('aplicatie'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Aplicatie  $aplicatie
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Aplicatie $aplicatie)
    {
        $aplicatie->update($this->validateRequest($request));

        return redirect($request->session()->get('aplicatieReturnUrl') ?? ('/apps/aplicatii'))->with('status', 'Aplicația „' . $aplicatie->nume . '” a fost modificată cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Aplicatie  $aplicatie
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Aplicatie $aplicatie)
    {
        if (($nrFacturi = $aplicatie->actualizari->whereNotNull('factura_id')->count()) > 0) {
            return back()->with('error', 'Nu puteți șterge aplicația „' . $aplicatie->nume . '” pentru că are deja emise ' . $nrFacturi . ' facturi. Ștergeți mai întâi facturile!');
        }

        $aplicatie->pontaje()->delete();
        $aplicatie->actualizari()->delete();
        $aplicatie->delete();

        return back()->with('status', 'Aplicația „' . $aplicatie->nume . '” a fost ștearsă cu succes!');
    }

    /**
     * Validate the request attributes.
     *
     * @return array
     */
    protected function validateRequest(Request $request)
    {
        // Se adauga userul doar la adaugare, iar la modificare nu se schimba
        // if ($request->isMethod('post')) {
        //     $request->request->add(['user_id' => $request->user()->id]);
        // }

        // if ($request->isMethod('post')) {
        //     $request->request->add(['cheie_unica' => uniqid()]);
        // }

        return $request->validate(
            [
                'nume' => 'required|max:200',
                'local_url' => 'nullable|max:200',
                'online_url' => 'nullable|max:200',
                'github_url' => 'nullable|max:200',
            ],
            [
                // 'tara_id.required' => 'Câmpul țara este obligatoriu'
            ]
        );
    }
}
