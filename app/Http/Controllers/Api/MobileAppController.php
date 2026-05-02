<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileAppRelease;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileAppController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'in:android'],
            'version_code' => ['nullable', 'integer', 'min:0'],
        ]);

        $installedVersionCode = (int) ($validated['version_code'] ?? 0);
        $release = MobileAppRelease::query()
            ->where('platform', $validated['platform'])
            ->whereNotNull('apk_url')
            ->where(function ($query) {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('version_code')
            ->first();

        $latestVersionCode = $release?->version_code ?? $installedVersionCode;

        return response()->json([
            'update_available' => $release !== null && $latestVersionCode > $installedVersionCode,
            'latest_version_code' => $latestVersionCode,
            'latest_version_name' => $release?->version_name,
            'apk_url' => $release?->apk_url,
            'release_notes' => $release?->release_notes,
            'required' => (bool) $release?->required,
        ]);
    }
}
