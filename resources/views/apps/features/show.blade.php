@extends ('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="shadow-lg" style="border-radius: 40px;">
                <div class="culoare2 border border-secondary p-2 d-flex justify-content-between align-items-center" style="border-radius: 40px 40px 0 0;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-layer-group me-1"></i>Features / {{ $feature->name }}
                    </span>
                    <a class="btn btn-sm btn-light rounded-3" href="{{ $feature->path() }}/modifica">
                        <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                    </a>
                </div>

                <div class="card-body py-3 border border-secondary" style="border-radius: 0 0 40px 40px;">
                    @include ('errors.errors')

                    <div class="row g-3 mb-3">
                        <div class="col-lg-4">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted small">Slug</div>
                                <div class="fw-bold">{{ $feature->slug }}</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted small">Category</div>
                                <div class="fw-bold">{{ $feature->category }}</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="text-muted small">Status</div>
                                <span class="badge {{ $feature->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $feature->is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if ($feature->description)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="fw-bold mb-2">Description</div>
                            <div>{!! nl2br(e($feature->description)) !!}</div>
                        </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-lg-12">
                            <div class="border rounded-3 p-3">
                                <div class="fw-bold mb-2">Standard prompt</div>
                                <pre class="bg-light border rounded-3 p-3 small mb-0" style="white-space: pre-wrap">{{ $feature->standard_prompt }}</pre>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="fw-bold mb-2">Implementation notes</div>
                                <div>{!! nl2br(e($feature->implementation_notes)) !!}</div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="fw-bold mb-2">Verification notes</div>
                                <div>{!! nl2br(e($feature->verification_notes)) !!}</div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('apps.features.implementations.save', $feature) }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Application</th>
                                        <th>Status</th>
                                        <th>Commit</th>
                                        <th>Dates</th>
                                        <th>Notes</th>
                                        <th>Differences</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($aplicatii as $aplicatie)
                                        @php
                                            $implementation = $implementationsByApp->get($aplicatie->id);
                                            $statusValue = old("implementations.{$aplicatie->id}.status", $implementation->status ?? 'not_started');
                                        @endphp
                                        <tr>
                                            <td style="min-width: 14rem;">
                                                <div class="fw-bold">{{ $aplicatie->nume }}</div>
                                                @if ($implementation)
                                                    <span class="badge {{ $implementation->status_badge }}">{{ $implementation->status_label }}</span>
                                                @endif
                                            </td>
                                            <td style="min-width: 10rem;">
                                                <select class="form-select form-select-sm" name="implementations[{{ $aplicatie->id }}][status]">
                                                    @foreach ($statusOptions as $value => $label)
                                                        <option value="{{ $value }}" @selected($statusValue === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td style="min-width: 11rem;">
                                                <input class="form-control form-control-sm mb-2" name="implementations[{{ $aplicatie->id }}][git_commit]" value="{{ old("implementations.{$aplicatie->id}.git_commit", $implementation->git_commit ?? '') }}" placeholder="Git commit">
                                                <input class="form-control form-control-sm" name="implementations[{{ $aplicatie->id }}][production_url]" value="{{ old("implementations.{$aplicatie->id}.production_url", $implementation->production_url ?? '') }}" placeholder="Production URL">
                                            </td>
                                            <td style="min-width: 11rem;">
                                                <input type="date" class="form-control form-control-sm mb-2" name="implementations[{{ $aplicatie->id }}][implemented_at]" value="{{ old("implementations.{$aplicatie->id}.implemented_at", optional($implementation?->implemented_at)->format('Y-m-d')) }}">
                                                <input type="date" class="form-control form-control-sm" name="implementations[{{ $aplicatie->id }}][production_updated_at]" value="{{ old("implementations.{$aplicatie->id}.production_updated_at", optional($implementation?->production_updated_at)->format('Y-m-d')) }}">
                                            </td>
                                            <td style="min-width: 16rem;">
                                                <textarea class="form-control form-control-sm" name="implementations[{{ $aplicatie->id }}][notes]" rows="3">{{ old("implementations.{$aplicatie->id}.notes", $implementation->notes ?? '') }}</textarea>
                                            </td>
                                            <td style="min-width: 16rem;">
                                                <textarea class="form-control form-control-sm" name="implementations[{{ $aplicatie->id }}][differences]" rows="3">{{ old("implementations.{$aplicatie->id}.differences", $implementation->differences ?? '') }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button class="btn btn-primary text-white rounded-3" type="submit">Save rollout statuses</button>
                            <a class="btn btn-secondary text-white rounded-3" href="{{ Session::get('featureReturnUrl') ?? '/apps/features' }}">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
