<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApartamentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $status = $request->query('status');

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
            ->get();

        return response()->json([
            'apartamente' => $apartamente->map(fn (Apartament $apartament) => $this->formatApartament($apartament))->values(),
            'status_options' => $this->statusOptions(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $apartament = Apartament::create($this->validateRequest($request));

        return response()->json([
            'message' => 'Apartamentul a fost adaugat.',
            'apartament' => $this->formatApartament($apartament),
        ], 201);
    }

    public function show(Apartament $apartament): JsonResponse
    {
        return response()->json([
            'apartament' => $this->formatApartament($apartament),
        ]);
    }

    public function update(Request $request, Apartament $apartament): JsonResponse
    {
        $apartament->update($this->validateRequest($request));

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

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'adresa' => 'required|max:255',
            'localitate' => 'nullable|max:100',
            'status' => 'required|in:' . implode(',', array_keys($this->statusOptions())),
            'vizionare_at' => 'nullable|date',
            'pret' => 'nullable|integer|min:0',
            'suprafata_mp' => 'nullable|integer|min:0',
            'camere' => 'nullable|integer|min:0',
            'etaj' => 'nullable|integer|min:-5|max:200',
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

    protected function formatApartament(Apartament $apartament): array
    {
        return [
            'id' => $apartament->id,
            'adresa' => $apartament->adresa,
            'localitate' => $apartament->localitate,
            'status' => $apartament->status,
            'status_label' => $apartament->status_label,
            'vizionare_at' => optional($apartament->vizionare_at)->toIso8601String(),
            'pret' => $apartament->pret,
            'suprafata_mp' => $apartament->suprafata_mp,
            'camere' => $apartament->camere,
            'etaj' => $apartament->etaj,
            'link_anunt' => $apartament->link_anunt,
            'agentie' => $apartament->agentie,
            'contact' => $apartament->contact,
            'puncte_bune' => $apartament->puncte_bune,
            'puncte_slabe' => $apartament->puncte_slabe,
            'riscuri_intrebari' => $apartament->riscuri_intrebari,
            'observatii' => $apartament->observatii,
            'scor' => $apartament->scor,
        ];
    }

    protected function statusOptions(): array
    {
        return [
            'de_vazut' => 'De vazut',
            'programat' => 'Programat',
            'vazut' => 'Vazut',
            'astept_raspuns' => 'Astept raspuns',
            'respins' => 'Respins',
            'oferta' => 'Oferta',
        ];
    }
}
