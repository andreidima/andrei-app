@extends ('layouts.app')

@php
    use \Carbon\Carbon;
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-clock me-1"></i>Pontaje
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    @php
                        $pontajeSearchConfig = [
                            'mode' => 'linked',
                            'aplicatieInputName' => 'searchAplicatie',
                            'actualizareInputName' => 'searchActualizare',
                            'actualizareIdInputName' => 'searchActualizareId',
                            'initialAplicatie' => $searchAplicatie,
                            'initialActualizare' => $searchActualizare,
                            'initialActualizareId' => $searchActualizareId,
                            'aplicatii' => $aplicatiiOptions,
                            'actualizari' => $actualizareOptions,
                            'placeholders' => [
                                'aplicatie' => 'Aplicație',
                                'actualizare' => 'Actualizare',
                            ],
                            'columnClasses' => [
                                'aplicatie' => 'col-12 col-lg-6',
                                'actualizare' => 'col-12 col-lg-6',
                            ],
                        ];
                    @endphp

                    <div class="row mb-1 custom-search-form justify-content-center">
                        <div class="col-lg-8">
                            <div
                                data-linked-autocomplete
                                data-config='@json($pontajeSearchConfig)'
                            ></div>
                        </div>
                        <div class="col-lg-4 d-flex align-items-center" id="datePicker">
                            Data:&nbsp;
                            <vue-datepicker-next
                                data-veche="{{ $searchData }}"
                                nume-camp-db="searchData"
                                tip="date"
                                value-type="YYYY-MM-DD"
                                format="DD.MM.YYYY"
                                :latime="{ width: '125px' }"
                            ></vue-datepicker-next>
                        </div>
                    </div>
                    <div class="row custom-search-form justify-content-center">
                        <div class="col-lg-4">
                            <button class="btn btn-sm w-100 btn-primary text-white border border-dark rounded-3" type="submit">
                                <i class="fas fa-search text-white me-1"></i>Caută
                            </button>
                        </div>
                        <div class="col-lg-4">
                            <a class="btn btn-sm w-100 btn-secondary text-white border border-dark rounded-3" href="{{ url()->current() }}" role="button">
                                <i class="far fa-trash-alt text-white me-1"></i>Resetează căutarea
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-3 text-end">
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă pontaj
                </a>
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors.errors')

            <div class="table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded">
                        <tr class="thead-danger" style="padding:2rem">
                            <th class="text-white culoare2">#</th>
                            <th class="text-white culoare2">Aplicație</th>
                            <th class="text-white culoare2">Actualizare</th>
                            <th class="text-white culoare2">Data</th>
                            <th class="text-white culoare2">
                                Durata
                                @if ($searchData)
                                    @php
                                        $durataTotala = Carbon::today();
                                        foreach ($pontaje as $pontaj){
                                            $durataTotala->addSeconds(Carbon::parse($pontaj->inceput)->diffinSeconds(Carbon::parse($pontaj->sfarsit ?? Carbon::now())));
                                        }
                                    @endphp
                                    ({{ Carbon::today()->diffInHours($durataTotala) }}:{{ Carbon::today()->diff($durataTotala)->format('%I') }})
                                @endif
                            </th>
                            <th class="text-white culoare2 text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pontaje as $pontaj)
                            <tr>
                                <td align="">
                                    {{ ($pontaje ->currentpage()-1) * $pontaje ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="">
                                    {{ $pontaj->actualizare->aplicatie->nume ?? '' }}
                                </td>
                                <td class="">
                                    {{ $pontaj->actualizare->nume ?? '' }}
                                </td>
                                <td class="">
                                    {{ $pontaj->inceput ? Carbon::parse($pontaj->inceput)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                    -
                                    @if ($pontaj->sfarsit)
                                        @if (Carbon::parse($pontaj->inceput)->toDateString() !== Carbon::parse($pontaj->sfarsit)->toDateString())
                                            <span class="px-2 rounded-3 bg-danger text-white">{{ Carbon::parse($pontaj->sfarsit)->isoFormat('DD.MM.YYYY') }}</span>
                                        @endif
                                        {{ $pontaj->sfarsit ? Carbon::parse($pontaj->sfarsit)->isoFormat('HH:mm') : '' }}
                                    @else
                                        <a href="/apps/pontaje/inchide" class="flex">
                                            <span class="badge bg-warning text-dark">Închide</span>
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if ($pontaj->inceput && $pontaj->sfarsit)
                                        {{ Carbon::parse($pontaj->inceput)->diffInHours(Carbon::parse($pontaj->sfarsit)) }}:{{ Carbon::parse($pontaj->inceput)->diff(Carbon::parse($pontaj->sfarsit))->format('%I') }}
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 justify-content-sm-end">
                                        <a href="/apps/pontaje/{{ $pontaj->actualizare->id ?? '' }}/deschide-nou">
                                            <span class="badge bg-warning text-dark">Deschide nou</span>
                                        </a>
                                        <a href="{{ $pontaj->path() }}">
                                            <span class="badge bg-success">Vizualizează</span>
                                        </a>
                                        <a href="{{ $pontaj->path() }}/modifica">
                                            <span class="badge bg-primary">Modifică</span>
                                        </a>
                                        <div class="flex-sm-grow-0">
                                            <a
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#stergePontaj{{ $pontaj->id }}"
                                                title="Șterge Pontaj"
                                                >
                                                <span class="badge bg-danger">Șterge</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- <div>Nu s-au gasit înregistrări în baza de date. Încearcă alte date de căutare</div> --}}
                        @endforelse
                        </tbody>
                </table>
            </div>

                <nav>
                    <ul class="pagination justify-content-center">
                        {{$pontaje->appends(Request::except('page'))->links()}}
                    </ul>
                </nav>
        </div>
    </div>

    {{-- Modalele pentru stergere pontaje --}}
    @foreach ($pontaje as $pontaj)
        <div class="modal fade text-dark" id="stergePontaj{{ $pontaj->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Pontaj: <b>{{ $pontaj->actualizare->aplicatie->nume ?? '' }} / {{ $pontaj->actualizare->nume ?? '' }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur ca vrei să ștergi Pontajul?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="{{ $pontaj->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge pontajul
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
