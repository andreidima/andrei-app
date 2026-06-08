@extends ('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card rounded-3 shadow-sm">
                <div class="card-header culoare2 d-flex flex-column flex-lg-row justify-content-between gap-2 rounded-top-3">
                    <span class="badge text-light fs-5 align-self-start">
                        <i class="fa-solid fa-file-import me-1"></i>Import / {{ $search->name }}
                    </span>
                    <a class="btn btn-sm btn-light rounded-3 align-self-start" href="{{ route('apartamente.tracking.index') }}">
                        <i class="fa-solid fa-arrow-left me-1"></i>Monitorizare
                    </a>
                </div>
                <div class="card-body">
                    @include ('errors.errors')

                    <form method="POST" action="{{ route('apartamente.tracking.searches.import.store', $search) }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="observed_at">Data observare</label>
                                <input
                                    class="form-control rounded-3"
                                    id="observed_at"
                                    type="datetime-local"
                                    name="observed_at"
                                    value="{{ old('observed_at', now()->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="source_type">Tip sursa</label>
                                <select class="form-select rounded-3" id="source_type" name="source_type">
                                    <option value="manual" @selected(old('source_type', 'manual') === 'manual')>Manual</option>
                                    <option value="email" @selected(old('source_type') === 'email')>Email</option>
                                    <option value="permitted_feed" @selected(old('source_type') === 'permitted_feed')>Feed permis</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="observations">Anunturi observate</label>
                            <textarea
                                class="form-control font-monospace"
                                id="observations"
                                name="observations"
                                rows="18"
                                required
                                placeholder="url,title,price,status,locality,bedrooms,surface,agency,under_option">{{ old('observations') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="notes">Note import</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>

                        <button class="btn btn-success text-white rounded-3" type="submit">
                            <i class="fa-solid fa-code-compare me-1"></i>Compara importul
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card rounded-3 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Cautare</h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nume</dt>
                        <dd class="col-sm-8">{{ $search->name }}</dd>
                        <dt class="col-sm-4">Zona</dt>
                        <dd class="col-sm-8">{{ $search->neighborhood ?: '-' }}</dd>
                        <dt class="col-sm-4">Sursa</dt>
                        <dd class="col-sm-8">{{ $search->source }}</dd>
                        <dt class="col-sm-4">URL</dt>
                        <dd class="col-sm-8">
                            @if ($search->url)
                                <a href="{{ $search->url }}" target="_blank" rel="noopener noreferrer">Deschide</a>
                            @else
                                -
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card rounded-3 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Importuri recente</h2>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th class="text-end">Anunturi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($search->runs as $run)
                                    <tr>
                                        <td>{{ $run->observed_at->format('d.m.Y H:i') }}</td>
                                        <td class="text-end">{{ $run->observed_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">Nu exista importuri.</td>
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
