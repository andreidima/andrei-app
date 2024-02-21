@extends ('layouts.app')

@php
    use \Carbon\Carbon;
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-file-invoice me-1"></i>Facturi
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        <div class="col-lg-6">
                            {{-- <input type="text" class="form-control rounded-3" id="searchNume" name="searchNume" placeholder="Nume" value="{{ $searchNume }}"> --}}
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
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă factură
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
                            <th class="text-white culoare2">Seria<br>Număr</th>
                            <th class="text-white culoare2">Data</th>
                            <th class="text-white culoare2">Confirmare<br>client</th>
                            <th class="text-white culoare2">Confirmare<br>validsoftware</th>
                            <th class="text-white culoare2">Aplicație</th>
                            <th class="text-white culoare2">Actualizări</th>
                            <th class="text-white culoare2 text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($facturi as $factura)
                            <tr>
                                <td align="">
                                    {{ ($facturi ->currentpage()-1) * $facturi ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="">
                                    {{ $factura->seria }}{{ $factura->numar }}
                                </td>
                                <td class="">
                                    {{ $factura->data ? Carbon::parse($factura->data)->isoFormat('DD.MM.YYYY') : '' }}
                                </td>
                                <td class="">
                                    {{ $factura->confirmare_client ? Carbon::parse($factura->confirmare_client)->isoFormat('DD.MM.YYYY') : '' }}
                                </td>
                                <td class="">
                                    {{ $factura->confirmare_validsoftware ? Carbon::parse($factura->confirmare_validsoftware)->isoFormat('DD.MM.YYYY') : '' }}
                                </td>
                                <td class="">
                                    {{ $factura->actualizari->first()->aplicatie->nume ?? '' }}
                                </td>
                                <td class="">
                                    @foreach ($factura->actualizari->sortBy('nume') as $actualizare)
                                        {{ $actualizare->nume }}
                                        <br>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ $factura->path() }}" class="flex me-1">
                                            <span class="badge bg-success">Vizualizează</span>
                                        </a>
                                        <a href="{{ $factura->path() }}/modifica" class="flex me-1">
                                            <span class="badge bg-primary">Modifică</span>
                                        </a>
                                        <div style="flex" class="">
                                            <a
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#stergeFactura{{ $factura->id }}"
                                                title="Șterge Factura"
                                                >
                                                <span class="badge bg-danger">Șterge</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                        </tbody>
                </table>
            </div>

                <nav>
                    <ul class="pagination justify-content-center">
                        {{$facturi->appends(Request::except('page'))->links()}}
                    </ul>
                </nav>
        </div>
    </div>

    {{-- Modalele pentru stergere facturi --}}
    @foreach ($facturi as $factura)
        <div class="modal fade text-dark" id="stergeFactura{{ $factura->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Factura: <b>{{ $factura->aplicatie->nume ?? '' }} / {{ $factura->data ? Carbon::parse($factura->data)->isoFormat('DD.MM.YYYY') : '' }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur ca vrei să ștergi factura?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="{{ $factura->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge factura
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
