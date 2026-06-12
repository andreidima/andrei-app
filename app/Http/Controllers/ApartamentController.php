<?php

namespace App\Http\Controllers;

use App\Models\Apartament;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\ApartmentInteraction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApartamentController extends Controller
{
    public function calendar(Request $request)
    {
        $request->session()->forget('apartamentReturnUrl');

        $monthQuery = $request->query('month', now()->format('Y-m'));
        try {
            $month = now()->startOfMonth();

            if (preg_match('/^\d{4}-\d{2}$/', $monthQuery)) {
                $candidate = Carbon::createFromFormat('Y-m-d', $monthQuery . '-01')->startOfMonth();
                $month = $candidate->format('Y-m') === $monthQuery ? $candidate : $month;
            }
        } catch (\Throwable) {
            $month = now()->startOfMonth();
        }

        $calendarStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $today = now()->startOfDay();

        $appointments = Apartament::query()
            ->with(['agency', 'agent'])
            ->whereNotNull('vizionare_at')
            ->whereBetween('vizionare_at', [$calendarStart->copy()->startOfDay(), $calendarEnd->copy()->endOfDay()])
            ->orderBy('vizionare_at')
            ->orderByDesc('prioritate')
            ->get();

        $appointmentsByDate = $appointments->groupBy(fn (Apartament $apartament) => $apartament->vizionare_at->toDateString());

        $calendarDays = collect();
        for ($date = $calendarStart->copy(); $date->lte($calendarEnd); $date->addDay()) {
            $calendarDays->push([
                'date' => $date->copy(),
                'in_month' => $date->isSameMonth($month),
                'is_today' => $date->isSameDay($today),
                'appointments' => $appointmentsByDate->get($date->toDateString(), collect()),
            ]);
        }

        $upcomingAppointments = Apartament::query()
            ->with(['agency', 'agent'])
            ->whereNotNull('vizionare_at')
            ->where('vizionare_at', '>=', now()->startOfDay())
            ->whereNotIn('status', ['respins'])
            ->orderBy('vizionare_at')
            ->orderByDesc('prioritate')
            ->limit(12)
            ->get();

        $nextAppointment = $upcomingAppointments->first();
        $appointmentsThisWeek = Apartament::query()
            ->whereNotNull('vizionare_at')
            ->whereBetween('vizionare_at', [now()->startOfDay(), now()->endOfWeek(Carbon::SUNDAY)])
            ->whereNotIn('status', ['respins'])
            ->count();

        return view('apartamente.calendar', [
            'month' => $month,
            'previousMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'calendarDays' => $calendarDays,
            'upcomingAppointments' => $upcomingAppointments,
            'nextAppointment' => $nextAppointment,
            'appointmentsThisWeek' => $appointmentsThisWeek,
        ]);
    }

    public function index(Request $request)
    {
        $request->session()->forget('apartamentReturnUrl');

        $search = $request->search;
        $status = $request->status;

        $apartamente = Apartament::query()
            ->with(['agency', 'agent'])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('adresa', 'like', "%{$search}%")
                        ->orWhere('localitate', 'like', "%{$search}%")
                        ->orWhere('agentie', 'like', "%{$search}%")
                        ->orWhere('contact', 'like', "%{$search}%")
                        ->orWhere('sursa_anunt', 'like', "%{$search}%")
                        ->orWhere('referinta_anunt', 'like', "%{$search}%");
                });
            })
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->orderByRaw("CASE WHEN vizionare_at IS NULL THEN 1 ELSE 0 END")
            ->orderBy('vizionare_at')
            ->orderByDesc('updated_at')
            ->simplePaginate(50);

        $statusOptions = $this->statusOptions();
        $decisionOptions = $this->decisionOptions();
        $interactionOptions = $this->interactionOptions();

        return view('apartamente.index', compact('apartamente', 'search', 'status', 'statusOptions', 'decisionOptions', 'interactionOptions'));
    }

    public function create(Request $request)
    {
        $request->session()->get('apartamentReturnUrl') ?? $request->session()->put('apartamentReturnUrl', url()->previous());

        $statusOptions = $this->statusOptions();
        $decisionOptions = $this->decisionOptions();
        $interactionOptions = $this->interactionOptions();
        $listingStatusOptions = $this->listingStatusOptions();

        return view('apartamente.create', compact('statusOptions', 'decisionOptions', 'interactionOptions', 'listingStatusOptions'));
    }

    public function store(Request $request)
    {
        [$data, $interactionData] = $this->validatedData($request);

        $apartament = Apartament::create($data);
        $this->recordInitialInteraction($apartament, $interactionData);

        return redirect($request->session()->get('apartamentReturnUrl') ?? '/apartamente')
            ->with('status', 'Apartamentul "' . $apartament->adresa . '" a fost adaugat cu succes!');
    }

    public function show(Request $request, Apartament $apartament)
    {
        $request->session()->get('apartamentReturnUrl') ?? $request->session()->put('apartamentReturnUrl', url()->previous());

        $apartament->load(['agency', 'agent', 'interactions.agency', 'interactions.agent']);

        return view('apartamente.show', compact('apartament'));
    }

    public function edit(Request $request, Apartament $apartament)
    {
        $request->session()->get('apartamentReturnUrl') ?? $request->session()->put('apartamentReturnUrl', url()->previous());

        $apartament->load(['agency', 'agent']);

        $statusOptions = $this->statusOptions();
        $decisionOptions = $this->decisionOptions();
        $interactionOptions = $this->interactionOptions();
        $listingStatusOptions = $this->listingStatusOptions();

        return view('apartamente.edit', compact('apartament', 'statusOptions', 'decisionOptions', 'interactionOptions', 'listingStatusOptions'));
    }

    public function update(Request $request, Apartament $apartament)
    {
        [$data, $interactionData] = $this->validatedData($request);

        $apartament->update($data);
        $this->recordOptionalInteraction($apartament->fresh(), $interactionData);

        return redirect($request->session()->get('apartamentReturnUrl') ?? '/apartamente')
            ->with('status', 'Apartamentul "' . $apartament->adresa . '" a fost modificat cu succes!');
    }

    public function destroy(Apartament $apartament)
    {
        $apartament->delete();

        return back()->with('status', 'Apartamentul "' . $apartament->adresa . '" a fost sters cu succes!');
    }

    protected function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'adresa' => 'required|max:255',
            'localitate' => 'nullable|max:100',
            'status' => 'required|in:' . implode(',', array_keys($this->statusOptions())),
            'decizie' => 'nullable|in:' . implode(',', array_keys($this->decisionOptions())),
            'motiv_respingere' => 'nullable|max:5000',
            'prioritate' => 'nullable|integer|min:1|max:5',
            'vizionare_at' => 'nullable|date',
            'adaugat_in_lista_at' => 'nullable|date',
            'pret' => 'nullable|integer|min:0',
            'pret_initial' => 'nullable|integer|min:0',
            'pret_curent' => 'nullable|integer|min:0',
            'pret_maxim_oferta' => 'nullable|integer|min:0',
            'ultima_verificare_at' => 'nullable|date',
            'status_anunt' => 'nullable|max:100',
            'observatii_status_anunt' => 'nullable|max:5000',
            'cheltuieli_lunare' => 'nullable|integer|min:0',
            'costuri_extra_estimate' => 'nullable|integer|min:0',
            'venit_cadastral' => 'nullable|integer|min:0',
            'motivatie_achizitie' => 'nullable|max:255',
            'suprafata_mp' => 'nullable|integer|min:0',
            'camere' => 'nullable|integer|min:0',
            'bai' => 'nullable|integer|min:0|max:20',
            'toalete' => 'nullable|integer|min:0|max:20',
            'etaj' => 'nullable|integer|min:-5|max:200',
            'an_constructie' => 'nullable|integer|min:1800|max:' . (date('Y') + 2),
            'etaje_cladire' => 'nullable|integer|min:0|max:200',
            'stare_cladire' => 'nullable|max:255',
            'stare_apartament' => 'nullable|max:255',
            'peb' => 'nullable|max:20',
            'peb_consum' => 'nullable|integer|min:0',
            'tip_incalzire' => 'nullable|max:255',
            'electricitate_conforma' => 'nullable|boolean',
            'are_lift' => 'nullable|boolean',
            'are_balcon' => 'nullable|boolean',
            'are_parcare' => 'nullable|boolean',
            'are_pivnita' => 'nullable|boolean',
            'orientare_lumina' => 'nullable|max:255',
            'orientare_terasa' => 'nullable|max:255',
            'renovare_necesara' => 'nullable|max:255',
            'zgomot' => 'nullable|max:255',
            'zona' => 'nullable|max:255',
            'disponibil_din' => 'nullable|date',
            'link_anunt' => 'nullable|url|max:500',
            'sursa_anunt' => 'nullable|max:255',
            'referinta_anunt' => 'nullable|max:255',
            'agentie' => 'nullable|max:255',
            'contact' => 'nullable|max:255',
            'agent_nume' => 'nullable|max:255',
            'agent_email' => 'nullable|email|max:255',
            'agent_telefon' => 'nullable|max:255',
            'puncte_bune' => 'nullable|max:5000',
            'puncte_slabe' => 'nullable|max:5000',
            'riscuri_intrebari' => 'nullable|max:5000',
            'observatii' => 'nullable|max:5000',
            'scor' => 'nullable|integer|min:1|max:10',
            'interaction_type' => 'nullable|in:' . implode(',', array_keys($this->interactionOptions())),
            'interaction_at' => 'nullable|date',
            'interaction_notes' => 'nullable|max:5000',
        ]);

        $interactionData = [
            'type' => $validated['interaction_type'] ?? null,
            'interacted_at' => $validated['interaction_at'] ?? null,
            'notes' => $validated['interaction_notes'] ?? null,
        ];

        $agentName = $validated['agent_nume'] ?? null;
        $agentEmail = $validated['agent_email'] ?? null;
        $agentPhone = $validated['agent_telefon'] ?? null;

        unset($validated['agent_nume'], $validated['agent_email'], $validated['agent_telefon']);
        unset($validated['interaction_type'], $validated['interaction_at'], $validated['interaction_notes']);

        $this->applyWatchlistDefaults($validated);

        [$agency, $agent] = $this->resolveAgencyAndAgent(
            $validated['agentie'] ?? null,
            $agentName,
            $agentEmail,
            $agentPhone
        );

        if ($agency) {
            $validated['agency_id'] = $agency->id;
            $validated['agentie'] = $agency->name;
        }

        if ($agent) {
            $validated['agent_id'] = $agent->id;
            $validated['contact'] = $agent->display_contact;
        }

        return [$validated, $interactionData];
    }

    protected function statusOptions()
    {
        return [
            'de_urmarit' => 'De urmarit',
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

    protected function listingStatusOptions(): array
    {
        return [
            'activ' => 'Activ',
            'pret_schimbat' => 'Pret schimbat',
            'sub_oferta' => 'Sub oferta',
            'vandut' => 'Vandut',
            'sters' => 'Sters',
        ];
    }

    protected function decisionOptions(): array
    {
        return [
            'nu' => 'Nu',
            'poate' => 'Poate',
            'shortlist' => 'Shortlist',
            'candidat_oferta' => 'Candidat oferta',
        ];
    }

    protected function interactionOptions(): array
    {
        return [
            'contacted' => 'Contactat',
            'visit_requested' => 'Vizionare ceruta',
            'visit_scheduled' => 'Vizionare programata',
            'visited' => 'Vazut',
            'follow_up' => 'Follow-up',
            'offer' => 'Oferta',
        ];
    }

    private function resolveAgencyAndAgent(?string $agencyName, ?string $agentName, ?string $agentEmail, ?string $agentPhone): array
    {
        $agency = null;

        if ($agencyName) {
            $agency = Agency::firstOrCreate(['name' => trim($agencyName)]);
        }

        if (! $agentName && ! $agentEmail && ! $agentPhone) {
            return [$agency, null];
        }

        $agentName = $agentName ?: $agentEmail ?: $agentPhone;

        $agent = Agent::firstOrCreate(
            [
                'agency_id' => $agency?->id,
                'name' => trim($agentName),
            ],
            [
                'email' => $agentEmail,
                'phone' => $agentPhone,
            ]
        );

        $agent->fill([
            'email' => $agentEmail ?: $agent->email,
            'phone' => $agentPhone ?: $agent->phone,
        ])->save();

        return [$agency, $agent];
    }

    private function applyWatchlistDefaults(array &$validated): void
    {
        if (($validated['status'] ?? null) !== 'de_urmarit') {
            return;
        }

        $validated['adaugat_in_lista_at'] ??= now();
        $validated['status_anunt'] = $validated['status_anunt'] ?: 'activ';

        if (empty($validated['pret_initial']) && ! empty($validated['pret'])) {
            $validated['pret_initial'] = $validated['pret'];
        }

        if (empty($validated['pret_curent']) && ! empty($validated['pret'])) {
            $validated['pret_curent'] = $validated['pret'];
        }

        if (empty($validated['pret']) && ! empty($validated['pret_curent'])) {
            $validated['pret'] = $validated['pret_curent'];
        }
    }

    private function recordInitialInteraction(Apartament $apartament, array $interactionData): void
    {
        $type = $interactionData['type'] ?: match ($apartament->status) {
            'programat' => 'visit_scheduled',
            'vazut' => 'visited',
            'astept_raspuns' => 'visit_requested',
            'oferta' => 'offer',
            default => null,
        };

        if (! $type) {
            return;
        }

        $this->recordInteraction($apartament, [
            'type' => $type,
            'interacted_at' => $interactionData['interacted_at'] ?: $apartament->vizionare_at,
            'notes' => $interactionData['notes'] ?: $apartament->observatii,
        ]);
    }

    private function recordOptionalInteraction(Apartament $apartament, array $interactionData): void
    {
        if (! $interactionData['type']) {
            return;
        }

        $this->recordInteraction($apartament, $interactionData);
    }

    private function recordInteraction(Apartament $apartament, array $interactionData): void
    {
        ApartmentInteraction::create([
            'apartament_id' => $apartament->id,
            'agency_id' => $apartament->agency_id,
            'agent_id' => $apartament->agent_id,
            'type' => $interactionData['type'],
            'interacted_at' => $interactionData['interacted_at'],
            'notes' => $interactionData['notes'],
        ]);
    }
}
