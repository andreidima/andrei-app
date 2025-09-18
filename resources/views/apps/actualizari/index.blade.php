@extends ('layouts.app')

@php
    use \Carbon\Carbon;
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-pen-to-square me-1"></i>Actualizări
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    @php
                        $actualizariSearchConfig = [
                            'mode' => 'linked',
                            'aplicatieInputName' => 'searchAplicatie',
                            'actualizareInputName' => 'searchActualizare',
                            'initialAplicatie' => $searchAplicatie,
                            'initialActualizare' => $searchActualizare,
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

                        <div class="col-lg-12">

                            <div

                                data-linked-autocomplete

                                data-config='@json($actualizariSearchConfig)'

                            ></div>

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
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă actualizare
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
                            <th class="text-white culoare2 text-end">
                                Pontaj azi
                                <br>
                                @php
                                    $durataTotala = Carbon::today();

                                    foreach ($actualizari as $actualizare) {
                                        if ($actualizare->pontajeAziDurata()){
                                            $durataTotala->addSeconds(Carbon::today()->diffInSeconds($actualizare->pontajeAziDurata()));
                                        }
                                    }
                                @endphp
                                {{ Carbon::today()->diffInHours($durataTotala) }}:{{ Carbon::today()->diff($durataTotala)->format('%I') }}
                            </th>
                            <th class="text-white culoare2 text-end">Pontaj<br>total</th>
                            <th class="text-white culoare2 text-center">Pontaj<br>start-stop</th>
                            {{-- <th class="text-white culoare2 text-center">Pontaj<br>stop</th> --}}
                            <th class="text-white culoare2 text-end">Preț</th>
                            <th class="text-white culoare2 text-center">Factura<br>emitere</th>
                            <th class="text-white culoare2 text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($actualizari as $actualizare)
                            <tr>
                                <td align="">
                                    {{ ($actualizari ->currentpage()-1) * $actualizari ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="">
                                    {{ $actualizare->aplicatie->nume ?? '' }}
                                </td>
                                <td class="">
                                    {{ $actualizare->nume }}
                                </td>
                                <td class="text-end">
                                    <a href="/apps/pontaje?searchActualizareId={{ $actualizare->id }}&searchData={{ \Carbon\Carbon::now()->toDateString(); }}" style="text-decoration: none">
                                        @if ($actualizare->pontajeAziDurata())
                                            @if (Carbon::today()->gt($actualizare->pontajeAziDurata()))
                                                <span class="px-2 rounded-3 bg-danger text-white" title="Actualizarea are pontaje care nu se inchid corect!">
                                                    Atenție
                                                </span>
                                            @else
                                                {{ Carbon::today()->diffInHours($actualizare->pontajeAziDurata()) }}:{{ Carbon::today()->diff($actualizare->pontajeAziDurata())->format('%I') }}
                                                <br>
                                            @endif
                                        @endif
                                    </a>
                                    @foreach ($actualizare->pontajeAziDeschise as $pontaj)
                                        <a href="/apps/pontaje/inchide" class="flex">
                                            <span class="badge bg-warning text-dark">
                                                Deschis
                                                <br>
                                                {{ Carbon::parse($pontaj->inceput)->isoFormat ('HH:mm') }}
                                                <br>
                                                Închide
                                            </span>
                                        </a>
                                    @endforeach
                                </td>
                                <td class="text-end">
                                    <a href="/apps/pontaje?searchActualizareId={{ $actualizare->id }}" style="text-decoration: none">
                                        @if ($actualizare->pontajeDurata())
                                            @if (Carbon::today()->gt($actualizare->pontajeDurata()))
                                                <span class="px-2 rounded-3 bg-danger text-white" title="Actualizarea are pontaje care nu se inchid corect!">
                                                    Atenție
                                                </span>
                                            @else
                                                {{ Carbon::today()->diffInHours($actualizare->pontajeDurata()) }}:{{ Carbon::today()->diff($actualizare->pontajeDurata())->format('%I') }}
                                            @endif
                                        @endif
                                    </a>
                                </td>
                                <td class="text-center">
                                    @if ($actualizare->pontaje->first())
                                        @php $dataInceput = Carbon::parse($actualizare->pontaje->sortBy('inceput')->first()->inceput ?? '') @endphp
                                        <span title="{{ $dataInceput->isoFormat('DD.MM.YYYY') }}"> {{ $dataInceput->isoFormat('DD.MM') }} </span>
                                        -
                                        @php $dataSfarsit = Carbon::parse($actualizare->pontaje->sortByDesc('inceput')->first()->inceput ?? '') @endphp
                                        <span title="{{ $dataSfarsit->isoFormat('DD.MM.YYYY') }}"> {{ $dataSfarsit->isoFormat('DD.MM') }} </span>
                                        =
                                        {{ Carbon::parse($dataInceput)->diffInDays($dataSfarsit) + 1 }}
                                    @endif

                                </td>
                                <td class="text-end">
                                    {{ $actualizare->pret }}
                                </td>
                                <td class="text-center">
                                    @if($actualizare->factura)
                                        <a href="{{ $actualizare->factura->path() }}/modifica" style="text-decoration: none">
                                            @php $data = Carbon::parse($actualizare->factura->data) @endphp
                                            <span title="{{ $data->isoFormat('DD.MM.YYYY') }}">
                                                {{ $data->isoFormat('DD.MM') }}
                                            </span>
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <a href="/apps/pontaje/{{ $actualizare->id ?? '' }}/deschide-nou" class="flex me-1">
                                            <span class="badge bg-warning text-dark">Deschide pontaj</span>
                                        </a>
                                        <a href="{{ $actualizare->path() }}" class="flex me-1">
                                            <span class="badge bg-success">Vizualizează</span>
                                        </a>
                                        <a href="{{ $actualizare->path() }}/modifica" class="flex me-1">
                                            <span class="badge bg-primary">Modifică</span>
                                        </a>
                                        <div style="flex" class="">
                                            <a
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#stergeActualizare{{ $actualizare->id }}"
                                                title="Șterge Actualizare"
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
                        {{$actualizari->appends(Request::except('page'))->links()}}
                    </ul>
                </nav>
        </div>
    </div>

    {{-- Modalele pentru stergere inregistrari --}}
    @foreach ($actualizari as $actualizare)
        <div class="modal fade text-dark" id="stergeActualizare{{ $actualizare->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Actualizare: <b>{{ $actualizare->nume }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur ca vrei să ștergi Actualizarea?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="{{ $actualizare->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge Actualizare
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
