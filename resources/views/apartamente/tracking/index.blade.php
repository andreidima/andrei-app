@extends ('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="card rounded-3 shadow-sm">
                <div class="card-header culoare2 d-flex flex-column flex-lg-row justify-content-between gap-2 rounded-top-3">
                    <span class="badge text-light fs-5 align-self-start">
                        <i class="fa-solid fa-chart-line me-1"></i>Monitorizare anunturi
                    </span>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-sm btn-light rounded-3" href="{{ route('apartamente.index') }}">
                            <i class="fa-solid fa-building me-1"></i>Apartamente
                        </a>
                        <a class="btn btn-sm btn-light rounded-3" href="{{ route('apartamente.calendar') }}">
                            <i class="fa-solid fa-calendar-days me-1"></i>Vizionari
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @include ('errors.errors')

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase fw-bold">Cautari active</div>
                                <div class="display-6 fw-bold">{{ $stats['active_searches'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase fw-bold">Anunturi urmarite</div>
                                <div class="display-6 fw-bold">{{ $stats['tracked_listings'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase fw-bold">Evenimente neverificate</div>
                                <div class="display-6 fw-bold">{{ $stats['unreviewed_events'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase fw-bold">Noi in 7 zile</div>
                                <div class="display-6 fw-bold">{{ $stats['new_this_week'] }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-xl-8">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Cautare</th>
                                            <th>Zona</th>
                                            <th>Ultima verificare</th>
                                            <th class="text-end">Rulari</th>
                                            <th class="text-end">Evenimente</th>
                                            <th class="text-end">Actiuni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($searches as $search)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">{{ $search->name }}</div>
                                                    @if ($search->url)
                                                        <a class="small" href="{{ $search->url }}" target="_blank" rel="noopener noreferrer">
                                                            <i class="fa-solid fa-up-right-from-square me-1"></i>{{ parse_url($search->url, PHP_URL_HOST) ?: 'Link' }}
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>{{ $search->neighborhood ?: '-' }}</td>
                                                <td>{{ $search->last_checked_at ? $search->last_checked_at->format('d.m.Y H:i') : '-' }}</td>
                                                <td class="text-end">{{ $search->runs_count }}</td>
                                                <td class="text-end">{{ $search->events_count }}</td>
                                                <td class="text-end">
                                                    <a class="btn btn-sm btn-primary text-white rounded-3" href="{{ route('apartamente.tracking.searches.import', $search) }}">
                                                        <i class="fa-solid fa-file-import me-1"></i>Import
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Nu exista cautari salvate.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <form method="POST" action="{{ route('apartamente.tracking.searches.store') }}" class="border rounded-3 p-3">
                                @csrf
                                <h2 class="h5 mb-3">Cautare noua</h2>
                                <div class="mb-3">
                                    <label class="form-label" for="name">Nume</label>
                                    <input class="form-control rounded-3" id="name" name="name" value="{{ old('name') }}" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="source">Sursa</label>
                                        <input class="form-control rounded-3" id="source" name="source" value="{{ old('source', 'immoweb') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="neighborhood">Zona</label>
                                        <input class="form-control rounded-3" id="neighborhood" name="neighborhood" value="{{ old('neighborhood') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="url">URL cautare</label>
                                    <input class="form-control rounded-3" id="url" type="url" name="url" value="{{ old('url') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="notes">Note</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                </div>
                                <button class="btn btn-success text-white rounded-3" type="submit">
                                    <i class="fa-solid fa-floppy-disk me-1"></i>Salveaza
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card rounded-3 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Evenimente recente</h2>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tip</th>
                                    <th>Anunt</th>
                                    <th>Cautare</th>
                                    <th>Schimbare</th>
                                    <th class="text-end">Actiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($events as $event)
                                    <tr class="{{ $event->is_reviewed ? 'text-muted' : '' }}">
                                        <td>{{ $event->occurred_at->format('d.m.Y H:i') }}</td>
                                        <td><span class="badge {{ $event->badge_class }}">{{ $event->type_label }}</span></td>
                                        <td>
                                            <div class="fw-bold">{{ $event->listing->display_name }}</div>
                                            @if ($event->listing->url)
                                                <a class="small" href="{{ $event->listing->url }}" target="_blank" rel="noopener noreferrer">
                                                    <i class="fa-solid fa-up-right-from-square me-1"></i>Anunt
                                                </a>
                                            @endif
                                        </td>
                                        <td>{{ $event->search?->name ?: '-' }}</td>
                                        <td>
                                            @if ($event->old_value !== null || $event->new_value !== null)
                                                <span class="text-muted">{{ $event->old_value ?? '-' }}</span>
                                                <i class="fa-solid fa-arrow-right mx-1"></i>
                                                <span class="fw-bold">{{ $event->new_value ?? '-' }}</span>
                                            @else
                                                {{ $event->description }}
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if (! $event->is_reviewed)
                                                <form method="POST" action="{{ route('apartamente.tracking.events.review', $event) }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-secondary rounded-3" type="submit">
                                                        <i class="fa-solid fa-check me-1"></i>Verificat
                                                    </button>
                                                </form>
                                            @else
                                                <span class="small">Verificat</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Nu exista evenimente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
