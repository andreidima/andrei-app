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
    private const WEEKDAY_TARGET_SECONDS = 7 * 3600;
    private const WEEKEND_TARGET_SECONDS = 3.5 * 3600;

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
            'summary' => $this->summaryData($date),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        return response()->json([
            'summary' => $this->summaryData($request->query('date')),
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

    public function update(Request $request, Pontaj $pontaj): JsonResponse
    {
        $validated = $request->validate([
            'actualizare_id' => ['sometimes', 'integer', 'exists:apps_actualizari,id'],
            'inceput' => ['required', 'date'],
            'sfarsit' => ['nullable', 'date'],
        ]);

        $inceput = Carbon::parse($validated['inceput']);
        $sfarsit = isset($validated['sfarsit']) ? Carbon::parse($validated['sfarsit']) : null;

        if ($sfarsit && ! $inceput->isSameDay($sfarsit)) {
            throw ValidationException::withMessages([
                'sfarsit' => 'Sfarsitul trebuie sa fie in aceeasi zi cu inceputul.',
            ]);
        }

        if ($sfarsit && $sfarsit->lessThanOrEqualTo($inceput)) {
            throw ValidationException::withMessages([
                'sfarsit' => 'Sfarsitul trebuie sa fie dupa inceput.',
            ]);
        }

        $pontaj->update([
            'actualizare_id' => $validated['actualizare_id'] ?? $pontaj->actualizare_id,
            'inceput' => $inceput,
            'sfarsit' => $sfarsit,
        ]);

        return response()->json([
            'message' => 'Pontajul a fost actualizat.',
            'pontaj' => $this->formatPontaj($pontaj->fresh('actualizare.aplicatie')),
        ]);
    }

    public function destroy(Pontaj $pontaj): JsonResponse
    {
        $pontaj->delete();

        return response()->json([
            'message' => 'Pontajul a fost sters.',
        ]);
    }

    public function restart(Pontaj $pontaj): JsonResponse
    {
        $this->closeOpenPontaj();

        $newPontaj = Pontaj::create([
            'actualizare_id' => $pontaj->actualizare_id,
            'inceput' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Pontajul a fost repornit.',
            'open_pontaj' => $this->formatPontaj($newPontaj->load('actualizare.aplicatie')),
        ], 201);
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

    private function summaryData(?string $date = null): array
    {
        $day = $date ? Carbon::parse($date)->startOfDay() : Carbon::today();
        $today = Carbon::today();

        $todayTargetSeconds = $this->targetSecondsForDate($day);
        $todayTotalSeconds = $this->totalSecondsForPeriod($day->copy()->startOfDay(), $day->copy()->endOfDay());

        return [
            'date' => $day->toDateString(),
            'today_target_seconds' => $todayTargetSeconds,
            'today_remaining_seconds' => max(0, $todayTargetSeconds - $todayTotalSeconds),
            'today_progress' => $todayTargetSeconds > 0 ? min(1, $todayTotalSeconds / $todayTargetSeconds) : 0,
            'week_total_seconds' => $this->totalSecondsForPeriod($day->copy()->startOfWeek(), $day->copy()->endOfWeek()),
            'month_total_seconds' => $this->totalSecondsForPeriod($day->copy()->startOfMonth(), $day->copy()->endOfMonth()),
            'continuous_work' => $this->continuousWorkData($today),
        ];
    }

    private function totalSecondsForPeriod(Carbon $start, Carbon $end): int
    {
        return Pontaj::whereBetween('inceput', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->get()
            ->sum(fn (Pontaj $pontaj) => $this->durationSeconds($pontaj));
    }

    private function continuousWorkData(Carbon $untilDate): array
    {
        $startDate = Carbon::parse('2024-01-01');
        $dailyDurations = [];

        for ($date = $untilDate->copy(); $date->gte($startDate); $date->subDay()) {
            $dailyDurations[$date->toDateString()] = 0;
        }

        Pontaj::select('inceput', 'sfarsit')
            ->whereDate('inceput', '>=', $startDate)
            ->whereDate('inceput', '<=', $untilDate)
            ->get()
            ->each(function (Pontaj $pontaj) use (&$dailyDurations) {
                $date = Carbon::parse($pontaj->inceput)->toDateString();

                if (isset($dailyDurations[$date])) {
                    $dailyDurations[$date] += $this->durationSeconds($pontaj);
                }
            });

        $extraSeconds = 0;

        foreach ($dailyDurations as $dateString => $durationSeconds) {
            $date = Carbon::parse($dateString);
            $targetSeconds = $this->targetSecondsForDate($date);
            $totalSeconds = $durationSeconds + $extraSeconds;

            if ($totalSeconds < $targetSeconds) {
                return [
                    'since' => $date->toDateString(),
                    'days' => $date->diffInDays($untilDate, false),
                    'additional_seconds_needed_for_next_day' => (int) ($targetSeconds - $totalSeconds),
                ];
            }

            $extraSeconds = $totalSeconds - $targetSeconds;
        }

        return [
            'since' => $startDate->toDateString(),
            'days' => $startDate->diffInDays($untilDate, false),
            'additional_seconds_needed_for_next_day' => 0,
        ];
    }

    private function targetSecondsForDate(Carbon $date): int
    {
        return (int) ($date->isWeekday() ? self::WEEKDAY_TARGET_SECONDS : self::WEEKEND_TARGET_SECONDS);
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
