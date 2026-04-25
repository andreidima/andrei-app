<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apps\Actualizare;
use App\Models\Apps\Pontaj;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PontajController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $date = $request->query('date', Carbon::today()->toDateString());

        $pontaje = Pontaj::with('actualizare.aplicatie')
            ->whereDate('inceput', $date)
            ->latest('inceput')
            ->get();

        return response()->json([
            'date' => $date,
            'open_pontaj' => $this->formatPontaj($this->getOpenPontaj()),
            'today_total_seconds' => $pontaje->sum(fn (Pontaj $pontaj) => $this->durationSeconds($pontaj)),
            'pontaje' => $pontaje->map(fn (Pontaj $pontaj) => $this->formatPontaj($pontaj))->values(),
            'actualizari' => $this->actualizariOptions(),
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'actualizare_id' => ['required', 'integer', 'exists:apps_actualizari,id'],
        ]);

        $this->closeOpenPontaj();

        $pontaj = Pontaj::create([
            'actualizare_id' => $validated['actualizare_id'],
            'inceput' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Pontajul a fost pornit.',
            'open_pontaj' => $this->formatPontaj($pontaj->load('actualizare.aplicatie')),
        ], 201);
    }

    public function stop(): JsonResponse
    {
        $closedPontaj = $this->closeOpenPontaj();

        return response()->json([
            'message' => $closedPontaj ? 'Pontajul a fost oprit.' : 'Nu exista pontaj deschis.',
            'closed_pontaj' => $this->formatPontaj($closedPontaj),
        ]);
    }

    private function closeOpenPontaj(): ?Pontaj
    {
        $openPontaje = Pontaj::whereNull('sfarsit')->get();

        if ($openPontaje->count() > 1) {
            throw ValidationException::withMessages([
                'pontaj' => 'Exista mai multe pontaje deschise. Inchideti-le manual in aplicatia web.',
            ]);
        }

        if ($openPontaje->isEmpty()) {
            return null;
        }

        $pontaj = $openPontaje->first();

        if (Carbon::parse($pontaj->inceput)->toDateString() !== Carbon::today()->toDateString()) {
            throw ValidationException::withMessages([
                'pontaj' => 'Exista un pontaj deschis din alta zi. Inchideti-l manual in aplicatia web.',
            ]);
        }

        $pontaj->update(['sfarsit' => Carbon::now()]);

        return $pontaj->fresh('actualizare.aplicatie');
    }

    private function getOpenPontaj(): ?Pontaj
    {
        return Pontaj::with('actualizare.aplicatie')
            ->whereNull('sfarsit')
            ->latest('inceput')
            ->first();
    }

    private function actualizariOptions()
    {
        return Actualizare::with('aplicatie:id,nume')
            ->select('id', 'nume', 'aplicatie_id')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (Actualizare $actualizare) => [
                'id' => $actualizare->id,
                'nume' => $actualizare->nume,
                'aplicatie_id' => $actualizare->aplicatie_id,
                'aplicatie_nume' => $actualizare->aplicatie->nume ?? '',
            ])
            ->values();
    }

    private function formatPontaj(?Pontaj $pontaj): ?array
    {
        if (! $pontaj) {
            return null;
        }

        $pontaj->loadMissing('actualizare.aplicatie');

        return [
            'id' => $pontaj->id,
            'actualizare_id' => $pontaj->actualizare_id,
            'actualizare_nume' => $pontaj->actualizare->nume ?? '',
            'aplicatie_nume' => $pontaj->actualizare->aplicatie->nume ?? '',
            'inceput' => optional($pontaj->inceput)->toIso8601String(),
            'sfarsit' => optional($pontaj->sfarsit)->toIso8601String(),
            'duration_seconds' => $this->durationSeconds($pontaj),
            'is_open' => $pontaj->sfarsit === null,
        ];
    }

    private function durationSeconds(Pontaj $pontaj): int
    {
        if (! $pontaj->inceput) {
            return 0;
        }

        $end = $pontaj->sfarsit ?? Carbon::now();

        return $pontaj->inceput->diffInSeconds($end);
    }
}
