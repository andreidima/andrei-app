<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Apps\Actualizare;
use App\Models\Apps\Aplicatie;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActualizareController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->forgetSession($request, 'actualizareReturnUrl');

        $searchAplicatie = $request->searchAplicatie;
        $searchActualizare = $request->string('searchActualizare');

        $query = Actualizare::with('aplicatie', 'pontaje', 'pontajeAzi', 'pontajeAziDeschise')
            ->select('*', DB::raw('(select inceput from apps_pontaje where actualizare_id = apps_actualizari.id order by id desc limit 1) as ultimul_pontaj'))
            ->when($searchAplicatie, function ($query, $searchAplicatie) {
                $query->whereHas('aplicatie', function ($query) use ($searchAplicatie) {
                    $query->where('nume', 'like', '%' . $searchAplicatie . '%');
                });
            })
            ->when($searchActualizare, function ($query, $searchActualizare) {
                $query->where('nume', 'like', '%' . $searchActualizare . '%');
            })
            ->latest();

        $actualizari = $query->simplePaginate(50);

        return view('apps.actualizari.index', compact('actualizari', 'searchAplicatie', 'searchActualizare'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $actualizare = new Actualizare;

        // Pre-fill application ID if adding from pontaj
        $actualizare->aplicatie_id = $request->session()->get('pontajRequest.aplicatie_id', '');

        $aplicatii = Aplicatie::select('id', 'nume')->orderBy('nume')->get();

        $this->setReturnUrl($request);

        return view('apps.actualizari.create', compact('actualizare', 'aplicatii'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $actualizare = Actualizare::create($this->validateRequest($request));

        // If the update was added from the Pontaj form, store it in the session for use in Pontaj
        if ($request->session()->exists('pontajRequest')) {
            $request->session()->put('pontajRequest.actualizare_id', $actualizare->id);
        }

        return redirect($this->getReturnUrl($request))->with('status', 'Actualizarea „' . $actualizare->nume . '” a fost adăugată cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Actualizare  $actualizare
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Actualizare $actualizare)
    {
        $this->setReturnUrl($request);

        return view('apps.actualizari.show', compact('actualizare'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Actualizare  $actualizare
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Actualizare $actualizare)
    {
        $this->setReturnUrl($request);

        $aplicatii = Aplicatie::select('id', 'nume')->orderBy('nume')->get();

        return view('apps.actualizari.edit', compact('actualizare', 'aplicatii'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Actualizare  $actualizare
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Actualizare $actualizare)
    {
        $actualizare->update($this->validateRequest($request));

        return redirect($this->getReturnUrl($request))->with('status', 'Actualizarea „' . $actualizare->nume . '” a fost modificată cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Actualizare  $actualizare
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Actualizare $actualizare)
    {
        if ($actualizare->factura) {
            return back()->with('error', 'Nu puteți șterge actualizarea „' . $actualizare->nume . '” pentru că are deja factură emisă. Ștergeți mai întâi factura.');
        }

        $actualizare->delete();
        $actualizare->pontaje()->delete();

        return back()->with('status', 'Actualizarea „' . $actualizare->nume . '” a fost ștearsă cu succes!');
    }

    /**
     * Validate the request attributes.
     *
     * @return array
     */
    protected function validateRequest(Request $request)
    {
        return $request->validate(
            [
                'aplicatie_id' => 'required',
                'nume' => 'required|max:200',
                'pret' => 'nullable',
                'trimis_catre_facturare' => 'nullable',
                'confirmare_facturare' => 'nullable',
                'descriere' => 'nullable',
                'observatii_pentru_client' => 'nullable|max:5000',
                'observatii_personale' => 'nullable|max:5000',
            ]
        );
    }

    /**
     * Handle axios request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function axios(Request $request)
    {
        $actualizari = Actualizare::where('aplicatie_id', $request->aplicatie_id)->latest()->get();

        return response()->json([
            'actualizari' => $actualizari,
        ]);
    }

    /**
     * Forget a session key.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $key
     */
    protected function forgetSession(Request $request, $key)
    {
        $request->session()->forget($key);
    }

    /**
     * Set the return URL in the session.
     *
     * @param \Illuminate\Http\Request $request
     */
    protected function setReturnUrl(Request $request)
    {
        $request->session()->get('actualizareReturnUrl') ?? $request->session()->put('actualizareReturnUrl', url()->previous());
    }

    /**
     * Get the return URL from the session.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function getReturnUrl(Request $request)
    {
        return $request->session()->get('actualizareReturnUrl') ?? '/apps/actualizari';
    }
}
