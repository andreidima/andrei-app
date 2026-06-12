<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartament;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\ApartmentInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApartamentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $status = $request->query('status');

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
            ->get();

        return response()->json([
            'apartamente' => $apartamente->map(fn (Apartament $apartament) => $this->formatApartament($apartament))->values(),
            'status_options' => $this->statusOptions(),
            'decision_options' => $this->decisionOptions(),
            'interaction_options' => $this->interactionOptions(),
            'listing_status_options' => $this->listingStatusOptions(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        [$data, $interactionData] = $this->validatedData($request);

        $apartament = Apartament::create($data);
        $this->recordInitialInteraction($apartament, $interactionData);

        return response()->json([
            'message' => 'Apartamentul a fost adaugat.',
            'apartament' => $this->formatApartament($apartament->fresh(['agency', 'agent'])),
        ], 201);
    }

    public function show(Apartament $apartament): JsonResponse
    {
        return response()->json([
            'apartament' => $this->formatApartament($apartament->load(['agency', 'agent', 'interactions'])),
        ]);
    }

    public function update(Request $request, Apartament $apartament): JsonResponse
    {
        [$data, $interactionData] = $this->validatedData($request);

        $apartament->update($data);
        $this->recordOptionalInteraction($apartament->fresh(), $interactionData);

        return response()->json([
            'message' => 'Apartamentul a fost modificat.',
            'apartament' => $this->formatApartament($apartament->fresh()),
        ]);
    }

    public function destroy(Apartament $apartament): JsonResponse
    {
        $apartament->delete();

        return response()->json([
            'message' => 'Apartamentul a fost sters.',
        ]);
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

    protected function formatApartament(Apartament $apartament): array
    {
        return [
            'id' => $apartament->id,
            'adresa' => $apartament->adresa,
            'localitate' => $apartament->localitate,
            'status' => $apartament->status,
            'status_label' => $apartament->status_label,
            'decizie' => $apartament->decizie,
            'decision_label' => $apartament->decision_label,
            'motiv_respingere' => $apartament->motiv_respingere,
            'prioritate' => $apartament->prioritate,
            'vizionare_at' => optional($apartament->vizionare_at)->toIso8601String(),
            'adaugat_in_lista_at' => optional($apartament->adaugat_in_lista_at)->toIso8601String(),
            'pret' => $apartament->pret,
            'pret_initial' => $apartament->pret_initial,
            'pret_curent' => $apartament->pret_curent,
            'watchlist_price_difference' => $apartament->watchlist_price_difference,
            'pret_maxim_oferta' => $apartament->pret_maxim_oferta,
            'ultima_verificare_at' => optional($apartament->ultima_verificare_at)->toIso8601String(),
            'status_anunt' => $apartament->status_anunt,
            'status_anunt_label' => $apartament->status_anunt_label,
            'observatii_status_anunt' => $apartament->observatii_status_anunt,
            'cheltuieli_lunare' => $apartament->cheltuieli_lunare,
            'costuri_extra_estimate' => $apartament->costuri_extra_estimate,
            'venit_cadastral' => $apartament->venit_cadastral,
            'motivatie_achizitie' => $apartament->motivatie_achizitie,
            'suprafata_mp' => $apartament->suprafata_mp,
            'camere' => $apartament->camere,
            'bai' => $apartament->bai,
            'toalete' => $apartament->toalete,
            'etaj' => $apartament->etaj,
            'an_constructie' => $apartament->an_constructie,
            'etaje_cladire' => $apartament->etaje_cladire,
            'stare_cladire' => $apartament->stare_cladire,
            'stare_apartament' => $apartament->stare_apartament,
            'peb' => $apartament->peb,
            'peb_consum' => $apartament->peb_consum,
            'tip_incalzire' => $apartament->tip_incalzire,
            'electricitate_conforma' => $apartament->electricitate_conforma,
            'are_lift' => $apartament->are_lift,
            'are_balcon' => $apartament->are_balcon,
            'are_parcare' => $apartament->are_parcare,
            'are_pivnita' => $apartament->are_pivnita,
            'orientare_lumina' => $apartament->orientare_lumina,
            'orientare_terasa' => $apartament->orientare_terasa,
            'renovare_necesara' => $apartament->renovare_necesara,
            'zgomot' => $apartament->zgomot,
            'zona' => $apartament->zona,
            'disponibil_din' => optional($apartament->disponibil_din)->toDateString(),
            'link_anunt' => $apartament->link_anunt,
            'sursa_anunt' => $apartament->sursa_anunt,
            'referinta_anunt' => $apartament->referinta_anunt,
            'agentie' => $apartament->agentie,
            'contact' => $apartament->contact,
            'agency' => $apartament->agency ? [
                'id' => $apartament->agency->id,
                'name' => $apartament->agency->name,
            ] : null,
            'agent' => $apartament->agent ? [
                'id' => $apartament->agent->id,
                'name' => $apartament->agent->name,
                'email' => $apartament->agent->email,
                'phone' => $apartament->agent->phone,
            ] : null,
            'puncte_bune' => $apartament->puncte_bune,
            'puncte_slabe' => $apartament->puncte_slabe,
            'riscuri_intrebari' => $apartament->riscuri_intrebari,
            'observatii' => $apartament->observatii,
            'scor' => $apartament->scor,
            'interactions' => $apartament->relationLoaded('interactions')
                ? $apartament->interactions->map(fn (ApartmentInteraction $interaction) => [
                    'id' => $interaction->id,
                    'type' => $interaction->type,
                    'type_label' => $interaction->type_label,
                    'interacted_at' => optional($interaction->interacted_at)->toIso8601String(),
                    'notes' => $interaction->notes,
                ])->values()
                : null,
        ];
    }

    protected function statusOptions(): array
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
