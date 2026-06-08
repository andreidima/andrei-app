<?php

namespace App\Http\Controllers;

use App\Models\ApartmentListingEvent;
use App\Models\ApartmentListingSnapshot;
use App\Models\ApartmentSearch;
use App\Models\ApartmentSearchRun;
use App\Models\ExternalApartmentListing;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApartmentListingTrackerController extends Controller
{
    public function index(): View
    {
        $searches = ApartmentSearch::query()
            ->withCount(['runs', 'events'])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        $events = ApartmentListingEvent::query()
            ->with(['search', 'listing'])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(80)
            ->get();

        $stats = [
            'active_searches' => ApartmentSearch::query()->where('is_active', true)->count(),
            'tracked_listings' => ExternalApartmentListing::query()->count(),
            'unreviewed_events' => ApartmentListingEvent::query()->where('is_reviewed', false)->count(),
            'new_this_week' => ApartmentListingEvent::query()
                ->where('type', 'new_listing')
                ->where('occurred_at', '>=', now()->subWeek())
                ->count(),
        ];

        return view('apartamente.tracking.index', compact('searches', 'events', 'stats'));
    }

    public function storeSearch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'source' => 'required|string|max:50',
            'url' => 'nullable|url|max:500',
            'neighborhood' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
        ]);

        $data['is_active'] = true;

        ApartmentSearch::create($data);

        return redirect()
            ->route('apartamente.tracking.index')
            ->with('status', 'Cautarea a fost salvata.');
    }

    public function import(ApartmentSearch $search): View
    {
        $search->load(['runs' => fn ($query) => $query->orderByDesc('observed_at')->limit(10)]);

        return view('apartamente.tracking.import', compact('search'));
    }

    public function storeImport(Request $request, ApartmentSearch $search): RedirectResponse
    {
        $data = $request->validate([
            'observed_at' => 'nullable|date',
            'source_type' => 'required|in:manual,email,permitted_feed',
            'notes' => 'nullable|string|max:5000',
            'observations' => 'required|string',
        ]);

        $observations = $this->parseObservations($data['observations']);

        if (count($observations) === 0) {
            return back()
                ->withInput()
                ->with('error', 'Nu am gasit niciun anunt valid in import.');
        }

        $run = DB::transaction(function () use ($search, $data, $observations) {
            $observedAt = isset($data['observed_at']) ? Carbon::parse($data['observed_at']) : now();

            $run = ApartmentSearchRun::create([
                'apartment_search_id' => $search->id,
                'observed_at' => $observedAt,
                'source_type' => $data['source_type'],
                'observed_count' => count($observations),
                'notes' => $data['notes'] ?? null,
            ]);

            $previousRun = $search->runs()
                ->where('id', '<>', $run->id)
                ->orderByDesc('observed_at')
                ->orderByDesc('id')
                ->first();

            $previousRunListingIds = $previousRun
                ? $previousRun->snapshots()->pluck('external_apartment_listing_id')->all()
                : [];

            $currentListingIds = [];

            foreach ($observations as $observation) {
                $listing = $this->resolveListing($search, $observation, $observedAt);
                $previousSnapshot = $this->previousSnapshot($search, $listing);
                $wasSeenInPreviousRun = in_array($listing->id, $previousRunListingIds, true);
                $wasSeenInSearch = $previousSnapshot !== null;

                if (in_array($listing->id, $currentListingIds, true)) {
                    continue;
                }

                $snapshot = ApartmentListingSnapshot::create([
                    'apartment_search_run_id' => $run->id,
                    'external_apartment_listing_id' => $listing->id,
                    'title' => $observation['title'],
                    'locality' => $observation['locality'],
                    'agency' => $observation['agency'],
                    'price' => $observation['price'],
                    'bedrooms' => $observation['bedrooms'],
                    'surface' => $observation['surface'],
                    'status' => $observation['status'],
                    'under_option' => $observation['under_option'],
                    'raw_payload' => $observation['raw_payload'],
                ]);

                $currentListingIds[] = $listing->id;

                if (! $wasSeenInSearch) {
                    $this->recordEvent($search, $run, $listing, 'new_listing', null, $listing->display_name, 'Anunt nou in cautare.');
                } elseif (! $wasSeenInPreviousRun) {
                    $this->recordEvent($search, $run, $listing, 'reappeared', null, $listing->display_name, 'Anuntul a reaparut in cautare.');
                }

                $this->recordChangedFields($search, $run, $listing, $previousSnapshot, $snapshot);
                $this->updateListingLatestFields($listing, $observation, $observedAt);
            }

            if ($previousRun) {
                collect($previousRunListingIds)
                    ->diff($currentListingIds)
                    ->each(function ($missingListingId) use ($search, $run) {
                        $listing = ExternalApartmentListing::find($missingListingId);

                        if ($listing) {
                            $this->recordEvent($search, $run, $listing, 'missing_from_search', $listing->display_name, null, 'Anuntul nu mai apare in cautarea importata.');
                        }
                    });
            }

            $search->update(['last_checked_at' => $observedAt]);

            return $run;
        });

        return redirect()
            ->route('apartamente.tracking.index')
            ->with('status', 'Importul a fost comparat: ' . $run->observed_count . ' anunturi observate.');
    }

    public function reviewEvent(ApartmentListingEvent $event): RedirectResponse
    {
        $event->update(['is_reviewed' => true]);

        return back()->with('status', 'Evenimentul a fost marcat ca verificat.');
    }

    private function parseObservations(string $input): array
    {
        $input = trim($input);
        $json = json_decode($input, true);

        if (is_array($json)) {
            return collect($json)
                ->map(fn ($row) => $this->normalizeObservation(is_array($row) ? $row : []))
                ->filter()
                ->values()
                ->all();
        }

        $lines = collect(preg_split('/\r\n|\r|\n/', $input))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        if ($lines->isEmpty()) {
            return [];
        }

        $delimiter = $this->detectDelimiter($lines->first());
        $rows = $lines->map(fn ($line) => str_getcsv($line, $delimiter))->values();
        $header = $this->looksLikeHeader($rows->first())
            ? array_map(fn ($value) => str_replace([' ', '-'], '_', strtolower(trim($value))), $rows->shift())
            : null;

        return $rows
            ->map(function ($row) use ($header) {
                if ($header) {
                    $mappedRow = [];

                    foreach ($row as $index => $value) {
                        if (isset($header[$index])) {
                            $mappedRow[$header[$index]] = $value;
                        }
                    }

                    $row = $mappedRow;
                }

                return $this->normalizeObservation($row);
            })
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeObservation(array $row): ?array
    {
        $get = function (array $names, ?int $index = null) use ($row) {
            foreach ($names as $name) {
                if (array_key_exists($name, $row) && $row[$name] !== '') {
                    return $row[$name];
                }
            }

            return $index !== null && array_key_exists($index, $row) ? $row[$index] : null;
        };

        $url = trim((string) $get(['url', 'link', 'link_anunt'], 0));
        $title = trim((string) $get(['title', 'titlu', 'adresa', 'address'], 1));

        if ($url === '' && $title === '') {
            return null;
        }

        $status = trim((string) $get(['status', 'statut'], 3));
        $underOption = $this->parseBoolean($get(['under_option', 'sous_option', 'sub_optiune'], 8));

        if ($underOption === null && preg_match('/option|optie|optiune/i', $status)) {
            $underOption = true;
        }

        return [
            'url' => $url ?: null,
            'external_id' => $this->extractExternalId($url),
            'title' => $title ?: null,
            'price' => $this->parseInteger($get(['price', 'pret'], 2)),
            'status' => $status ?: null,
            'locality' => trim((string) $get(['locality', 'localitate', 'city', 'commune'], 4)) ?: null,
            'bedrooms' => $this->parseInteger($get(['bedrooms', 'camere', 'chambres'], 5)),
            'surface' => $this->parseInteger($get(['surface', 'suprafata', 'mp'], 6)),
            'agency' => trim((string) $get(['agency', 'agentie'], 7)) ?: null,
            'under_option' => $underOption,
            'raw_payload' => $row,
        ];
    }

    private function resolveListing(ApartmentSearch $search, array $observation, mixed $observedAt): ExternalApartmentListing
    {
        $source = $search->source ?: 'manual';
        $externalId = $observation['external_id'] ?: 'manual-' . sha1(implode('|', [
            $source,
            $observation['url'],
            $observation['title'],
            $observation['locality'],
            $observation['agency'],
        ]));

        $listing = ExternalApartmentListing::firstOrNew([
            'source' => $source,
            'external_id' => $externalId,
        ]);

        if (! $listing->exists) {
            $listing->first_seen_at = $observedAt;
        }

        $listing->fill([
            'url' => $observation['url'] ?: $listing->url,
            'title' => $observation['title'] ?: $listing->title,
            'locality' => $observation['locality'] ?: $listing->locality,
            'agency' => $observation['agency'] ?: $listing->agency,
        ])->save();

        return $listing;
    }

    private function previousSnapshot(ApartmentSearch $search, ExternalApartmentListing $listing): ?ApartmentListingSnapshot
    {
        return $listing->snapshots()
            ->whereHas('run', fn ($query) => $query->where('apartment_search_id', $search->id))
            ->latest('id')
            ->first();
    }

    private function recordChangedFields(
        ApartmentSearch $search,
        ApartmentSearchRun $run,
        ExternalApartmentListing $listing,
        ?ApartmentListingSnapshot $previousSnapshot,
        ApartmentListingSnapshot $snapshot
    ): void {
        if (! $previousSnapshot) {
            return;
        }

        if ($previousSnapshot->price !== null && $snapshot->price !== null && $previousSnapshot->price !== $snapshot->price) {
            $this->recordEvent($search, $run, $listing, 'price_changed', $previousSnapshot->price, $snapshot->price, 'Pretul s-a schimbat.');
        }

        if ($previousSnapshot->under_option !== null && $snapshot->under_option !== null && $previousSnapshot->under_option !== $snapshot->under_option) {
            $this->recordEvent(
                $search,
                $run,
                $listing,
                $snapshot->under_option ? 'under_option_added' : 'under_option_removed',
                $previousSnapshot->under_option ? 'da' : 'nu',
                $snapshot->under_option ? 'da' : 'nu',
                'Statusul under option s-a schimbat.'
            );
        }

        if ($previousSnapshot->status !== null && $snapshot->status !== null && $previousSnapshot->status !== $snapshot->status) {
            $this->recordEvent($search, $run, $listing, 'status_changed', $previousSnapshot->status, $snapshot->status, 'Statusul textual s-a schimbat.');
        }
    }

    private function updateListingLatestFields(ExternalApartmentListing $listing, array $observation, mixed $observedAt): void
    {
        $listing->update([
            'latest_price' => $observation['price'],
            'latest_bedrooms' => $observation['bedrooms'],
            'latest_surface' => $observation['surface'],
            'latest_status' => $observation['status'],
            'latest_under_option' => $observation['under_option'],
            'last_seen_at' => $observedAt,
        ]);
    }

    private function recordEvent(
        ApartmentSearch $search,
        ApartmentSearchRun $run,
        ExternalApartmentListing $listing,
        string $type,
        mixed $oldValue,
        mixed $newValue,
        string $description
    ): void {
        ApartmentListingEvent::create([
            'apartment_search_id' => $search->id,
            'apartment_search_run_id' => $run->id,
            'external_apartment_listing_id' => $listing->id,
            'type' => $type,
            'occurred_at' => $run->observed_at,
            'old_value' => is_null($oldValue) ? null : (string) $oldValue,
            'new_value' => is_null($newValue) ? null : (string) $newValue,
            'description' => $description,
        ]);
    }

    private function detectDelimiter(string $line): string
    {
        return collect(["\t", ';', '|', ','])
            ->sortByDesc(fn ($delimiter) => substr_count($line, $delimiter))
            ->first() ?: ',';
    }

    private function looksLikeHeader(array $row): bool
    {
        $headerText = strtolower(implode('|', $row));

        return str_contains($headerText, 'url') || str_contains($headerText, 'price') || str_contains($headerText, 'pret');
    }

    private function parseInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $digits = preg_replace('/[^\d]/', '', (string) $value);

        return $digits === '' ? null : (int) $digits;
    }

    private function parseBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = strtolower(trim((string) $value));

        return match ($value) {
            '1', 'true', 'yes', 'y', 'da', 'oui', 'sous option', 'under option' => true,
            '0', 'false', 'no', 'n', 'nu', 'non' => false,
            default => null,
        };
    }

    private function extractExternalId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        preg_match_all('/\d{6,}/', $url, $matches);

        return $matches[0] ? end($matches[0]) : null;
    }
}
