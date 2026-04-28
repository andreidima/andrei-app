<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apps\Actualizare;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActualizareController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $search = trim($validated['search'] ?? '');
        $limit = $validated['limit'] ?? 25;

        $actualizari = Actualizare::with('aplicatie:id,nume')
            ->select('id', 'nume', 'aplicatie_id')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nume', 'like', "%{$search}%")
                        ->orWhereHas('aplicatie', function ($query) use ($search) {
                            $query->where('nume', 'like', "%{$search}%");
                        });
                });
            })
            ->when($search, fn ($query) => $query->orderBy('nume'), fn ($query) => $query->latest())
            ->limit($limit)
            ->get()
            ->map(fn (Actualizare $actualizare) => [
                'id' => $actualizare->id,
                'nume' => $actualizare->nume,
                'aplicatie_id' => $actualizare->aplicatie_id,
                'aplicatie_nume' => $actualizare->aplicatie->nume ?? '',
            ])
            ->values();

        return response()->json([
            'actualizari' => $actualizari,
        ]);
    }
}
