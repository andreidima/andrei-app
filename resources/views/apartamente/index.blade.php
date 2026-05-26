@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0 0;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-building me-1"></i>Apartamente
            </span>
        </div>
        <div class="col-lg-6">
            <form class="needs-validation" novalidate method="GET" action="{{ url()->current() }}">
                <div class="row mb-1 custom-search-form justify-content-center g-2">
                    <div class="col-lg-7">
                        <input
                            type="text"
                            class="form-control rounded-3"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Adresa, localitate, agentie, agent, referinta">
                    </div>
                    <div class="col-lg-4">
                        <select name="status" class="form-select rounded-3">
                            <option value="">Toate statusurile</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-1">
                        <button type="submit" class="btn btn-primary text-white rounded-3 w-100">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-lg-3 text-end">
            <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                <i class="fas fa-plus-square text-white me-1"></i>Adauga apartament
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include ('errors.errors')

        <div class="table-responsive rounded">
            <table class="table table-striped table-hover rounded align-middle">
                <thead class="text-white rounded">
                    <tr class="thead-danger">
                        <th class="text-white culoare2">#</th>
                        <th class="text-white culoare2">Apartament</th>
                        <th class="text-white culoare2">Status</th>
                        <th class="text-white culoare2">Decizie</th>
                        <th class="text-white culoare2">Vizionare</th>
                        <th class="text-white culoare2 text-end">Pret</th>
                        <th class="text-white culoare2">Date</th>
                        <th class="text-white culoare2">Agentie</th>
                        <th class="text-white culoare2">Plus / minus</th>
                        <th class="text-white culoare2 text-center">Scor</th>
                        <th class="text-white culoare2 text-end">Actiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($apartamente as $apartament)
                        <tr>
                            <td>{{ ($apartamente->currentpage() - 1) * $apartamente->perpage() + $loop->index + 1 }}</td>
                            <td>
                                <div class="fw-bold">{{ $apartament->adresa }}</div>
                                <div class="text-muted">{{ $apartament->localitate }}</div>
                                @if ($apartament->link_anunt)
                                    <a href="{{ $apartament->link_anunt }}" target="_blank" rel="noopener noreferrer" class="small">
                                        <i class="fa-solid fa-up-right-from-square me-1"></i>{{ $apartament->sursa_anunt ?: 'Anunt' }}
                                    </a>
                                    @if ($apartament->referinta_anunt)
                                        <span class="small text-muted">#{{ $apartament->referinta_anunt }}</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $apartament->status_badge }}">{{ $apartament->status_label }}</span>
                            </td>
                            <td>
                                {{ $apartament->decision_label ?: '-' }}
                                @if ($apartament->prioritate)
                                    <div class="small text-muted">P{{ $apartament->prioritate }}</div>
                                @endif
                            </td>
                            <td>
                                {{ $apartament->vizionare_at ? $apartament->vizionare_at->format('d.m.Y H:i') : '-' }}
                            </td>
                            <td class="text-end">
                                {{ $apartament->pret ? number_format($apartament->pret, 0, ',', '.') . ' EUR' : '-' }}
                                @if ($apartament->pret_maxim_oferta)
                                    <div class="small text-muted">max {{ number_format($apartament->pret_maxim_oferta, 0, ',', '.') }} EUR</div>
                                @endif
                            </td>
                            <td>
                                @if ($apartament->camere)
                                    {{ $apartament->camere }} camere<br>
                                @endif
                                @if ($apartament->suprafata_mp)
                                    {{ $apartament->suprafata_mp }} mp<br>
                                @endif
                                @if (! is_null($apartament->etaj))
                                    Etaj {{ $apartament->etaj }}{{ $apartament->etaje_cladire ? '/' . $apartament->etaje_cladire : '' }}<br>
                                @endif
                                @if ($apartament->peb)
                                    PEB {{ $apartament->peb }}
                                @endif
                            </td>
                            <td>
                                {{ $apartament->agency?->name ?: $apartament->agentie ?: '-' }}
                                @if ($apartament->agent_contact)
                                    <div class="small text-muted">{{ $apartament->agent_contact }}</div>
                                @endif
                            </td>
                            <td style="min-width: 16rem;">
                                @if ($apartament->puncte_bune)
                                    <div><span class="badge bg-success me-1">+</span>{!! nl2br(e($apartament->puncte_bune)) !!}</div>
                                @endif
                                @if ($apartament->puncte_slabe)
                                    <div><span class="badge bg-danger me-1">-</span>{!! nl2br(e($apartament->puncte_slabe)) !!}</div>
                                @endif
                                @if (! $apartament->puncte_bune && ! $apartament->puncte_slabe)
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $apartament->scor ? $apartament->scor . '/10' : '-' }}
                            </td>
                            <td>
                                <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 justify-content-sm-end">
                                    <a href="{{ $apartament->path() }}"><span class="badge bg-success">Vizualizeaza</span></a>
                                    <a href="{{ $apartament->path() }}/modifica"><span class="badge bg-primary">Modifica</span></a>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#stergeApartament{{ $apartament->id }}">
                                        <span class="badge bg-danger">Sterge</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">Nu exista apartamente pentru cautarea curenta.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center">
                {{ $apartamente->appends(Request::except('page'))->links() }}
            </ul>
        </nav>
    </div>
</div>

@foreach ($apartamente as $apartament)
    <div class="modal fade text-dark" id="stergeApartament{{ $apartament->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Apartament: <b>{{ $apartament->adresa }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Esti sigur ca vrei sa stergi apartamentul?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunta</button>
                    <form method="POST" action="{{ $apartament->path() }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="btn btn-danger text-white">Sterge apartamentul</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
