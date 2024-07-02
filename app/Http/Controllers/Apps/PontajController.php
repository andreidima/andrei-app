<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Apps\Pontaj;
use App\Models\Apps\Actualizare;
use App\Models\Apps\Aplicatie;
use Carbon\Carbon;

class PontajController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('pontajReturnUrl');

        $searchAplicatie = $request->searchAplicatie;
        $searchActualizare = $request->searchActualizare;
        $searchActualizareId = $request->searchActualizareId;
        $searchData = $request->searchData;

        $query = Pontaj::with('actualizare.aplicatie')
            ->when($searchAplicatie, function ($query, $searchAplicatie) {
                $query->whereHas('actualizare', function ($query) use ($searchAplicatie) {
                    $query->whereHas('aplicatie', function ($query) use ($searchAplicatie) {
                        $query->where('nume', 'like', '%' . $searchAplicatie . '%');
                    });
                });
            })
            ->when($searchActualizare, function ($query, $searchActualizare) {
                $query->whereHas('actualizare', function ($query) use ($searchActualizare) {
                    $query->where('nume', 'like', '%' . $searchActualizare . '%');
                });
            })
            ->when($searchActualizareId, function ($query, $searchActualizareId) {
                $query->whereHas('actualizare', function ($query) use ($searchActualizareId) {
                    $query->where('id', $searchActualizareId);
                });
            })
            ->when($searchData, function ($query, $searchData) {
                $query->whereDate('inceput', $searchData . '%');
            })
            ->latest();

        $pontaje = $query->simplePaginate(50);

        return view('apps.pontaje.index', compact('pontaje', 'searchAplicatie', 'searchActualizare', 'searchActualizareId', 'searchData'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $pontaj = new Pontaj;

        // Daca a fost adaugata o resursa din pontaj, se revine in formularul pontaj si campurile trebuie sa se recompleteze automat
        $pontaj->fill($request->session()->pull('pontajRequest', []));

        $aplicatii = Aplicatie::select('id', 'nume')->orderBy('nume')->get();
        $actualizari = Actualizare::select('id', 'nume')->orderBy('nume')->get();

        $request->session()->get('pontajReturnUrl') ?? $request->session()->put('pontajReturnUrl', url()->previous());

        return view('apps.pontaje.create', compact('pontaj', 'aplicatii', 'actualizari'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request);
        $pontaj = Pontaj::create($this->validateRequest($request));

        return redirect($request->session()->get('pontajReturnUrl') ?? ('/app/pontaje'))->with('status', 'Pontajul pentru actualizarea „' . ($pontaj->actualizare->nume ?? '') . '” a fost adăugat cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Pontaj  $pontaj
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Pontaj $pontaj)
    {
        $request->session()->get('pontajReturnUrl') ?? $request->session()->put('pontajReturnUrl', url()->previous());

        return view('apps.pontaje.show', compact('pontaj'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Pontaj  $pontaj
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Pontaj $pontaj)
    {
        // Daca a fost adaugata o resursa din pontaj, se revine in formularul pontaj si campurile trebuie sa se recompleteze automat
        $pontaj->fill($request->session()->pull('pontajRequest', []));

        $aplicatii = Aplicatie::select('id', 'nume')->orderBy('nume')->get();
        $actualizari = Actualizare::select('id', 'nume')->orderBy('nume')->get();

        $request->session()->get('pontajReturnUrl') ?? $request->session()->put('pontajReturnUrl', url()->previous());

        return view('apps.pontaje.edit', compact('pontaj', 'aplicatii', 'actualizari'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Pontaj  $pontaj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pontaj $pontaj)
    {
        $pontaj->update($this->validateRequest($request));

        return redirect($request->session()->get('pontajReturnUrl') ?? ('/app/pontaje'))->with('status', 'Pontajul pentru actualizarea „' . ($pontaj->actualizare->nume ?? '') . '” a fost modificat cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Pontaj  $pontaj
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Pontaj $pontaj)
    {
        $pontaj->delete();

        return back()->with('status', 'Pontajul pentru actualizarea „' . ($pontaj->actualizare->nume ?? '') . '” a fost șters cu succes!');
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
// dd(Carbon::parse($request->inceput)->toDateString());
        return $request->validate(
            [
                'actualizare_id' => 'required',
                'inceput' => 'required',
                'sfarsit' => ['nullable',
                        function ($attribute, $value, $fail) use ($request) {
                            if ($request->sfarsit && $request->inceput) {
                                if (Carbon::parse($request->inceput)->toDateString() !== Carbon::parse($request->sfarsit)->toDateString()) {
                                    $fail('Sfărșit nu poate fi în altă zi față de început');
                                }
                            }
                        },
                    ]
            ],
            [

            ]
        );
    }

    public function adaugaResursa(Request $request, $resursa = null)
    {
        $request->session()->put('pontajRequest', $request->all());

        switch($resursa){
            case 'aplicatie':
                $request->session()->put('aplicatieReturnUrl', url()->previous());
                return redirect('/apps/aplicatii/adauga');
                break;
            case 'actualizare':
                $request->session()->put('actualizareReturnUrl', url()->previous());
                return redirect('/apps/actualizari/adauga');
                break;
        }
    }

    private function inchidePontaj ()
    {
        $pontaje = Pontaj::whereNull('sfarsit')->get();

        if ($pontaje->count() > 1) {
            return ("În acest moment sunt deschise mai multe pontaje. În această situație, pontajele deschise trebuie verificate și închise manual.");
        } elseif ($pontaje->count() == 1) {
            $pontaj = $pontaje->first();
            if (Carbon::parse($pontaj->inceput)->toDateString() !== Carbon::now()->toDateString()){
                return ("În acest moment există un pontaj deschis, dar nu cu data de astăzi. În acestă situație, pontajul trebuie verificat și închis manual.");
            }
            $pontaj->update(['sfarsit' => Carbon::now()]);
        }
        return;
    }

    public function inchide ()
    {
        // Funcția returnează ceva doar dacă este vreo eroare.
        if ($mesaj = $this->inchidePontaj()){
            return back()->with('error', $mesaj);
        }
        return back()->with('status', 'Pontajul a fost închis cu succes!');
    }

    public function deschideNou ($actualizare = null)
    {
        // Funcția returnează ceva doar dacă este vreo eroare.
        if ($mesaj = $this->inchidePontaj()){
            return back()->with('error', $mesaj);
        }

        Pontaj::create(['actualizare_id' => $actualizare, 'inceput' => Carbon::now()]);

        return back()->with('status', 'Pontajul a fost creat cu succes!');
    }

    public function statistica (Request $request)
    {
        $searchInterval = $request->searchInterval ?? Carbon::today()->startOfMonth() . ',' . Carbon::today()->endOfMonth();
        $searchAplicatiiSelectate = $request->searchAplicatiiSelectate ?? Aplicatie::select('id')->pluck('id')->toArray();
        // $searchAplicatiiSelectate = $request->searchAplicatiiSelectate ?? Aplicatie::select('id', 'nume')->pluck('id', 'nume')->get();
// dd($searchAplicatiiSelectate);
        $dataInceput = strtok($searchInterval, ',');
        $dataSfarsit = strtok( '' );

        $pontaje = Pontaj::select('inceput', 'sfarsit')
            ->selectRaw('TIMEDIFF(sfarsit, inceput) AS timp')
            ->when($searchInterval, function ($query, $searchInterval) {
                return $query->whereBetween('inceput', [strtok($searchInterval, ','), strtok( '' )]);
            })
            ->whereHas('actualizare' , function ($query) use ($searchAplicatiiSelectate) {
                return $query->whereHas('aplicatie', function ($query) use ($searchAplicatiiSelectate) {
                    return $query->whereIn('id', $searchAplicatiiSelectate);
                });
            })
            ->orderBy('inceput')
            ->get();

        // Make an array with „pontaje” cumulated by days
        $pontajeCumulatPeZi = [[]];
        foreach ($pontaje as $pontaj){
            $ziua = substr($pontaj->inceput, 0, 10); // select just the day without time
            if (isset($pontajeCumulatPeZi[$ziua])){ // if allreay this day is in array
                $azi = Carbon::today()->setTimeFromTimeString($pontajeCumulatPeZi[$ziua]); // the time that it is allready in array
                $azi->addHours(substr($pontaj->timp, 0, 2))->addMinutes(substr($pontaj->timp, 3, 2))->addSeconds(substr($pontaj->timp, 6, 2)); // the time that is allready in array + the new time
                $pontajeCumulatPeZi[$ziua] = Carbon::parse($azi)->isoFormat('HH:mm:ss');
            } else {
                $pontajeCumulatPeZi[$ziua] = $pontaj->timp;
            }
        }
        unset($pontajeCumulatPeZi[0]); // the 0 index is created automatically at the array creation, and need to be deleted to not be displayed in calendar

        // Fill the array with missing dates, for faster parsing in view
        $ziua = Carbon::parse($dataInceput)->startOfMonth();
        while($ziua->lessThan(Carbon::parse($dataSfarsit)->endOfMonth())){
            if (!isset($pontajeCumulatPeZi[$ziua->isoFormat('YYYY-MM-DD')])) {
                $pontajeCumulatPeZi[$ziua->isoFormat('YYYY-MM-DD')] = '';
            }
            $ziua->addDay();
        }
        ksort($pontajeCumulatPeZi);

        $aplicatii = Aplicatie::orderBy('nume')->get();

        return view('apps.pontaje.misc.statistica', compact('pontajeCumulatPeZi', 'aplicatii', 'searchInterval', 'searchAplicatiiSelectate'));
    }
}
