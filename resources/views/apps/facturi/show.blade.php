@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="shadow-lg" style="border-radius: 40px 40px 40px 40px;">
                <div class="culoare2 border border-secondary p-2" style="border-radius: 40px 40px 0px 0px;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-file-invoice me-1"></i>Facturi / {{ $factura->seria }} {{ $factura->numar }}
                    </span>
                </div>

                <div class="card-body py-2 border border-secondary"
                    style="border-radius: 0px 0px 40px 40px;"
                >

            @include ('errors.errors')

                    <div class="table-responsive col-md-12 mx-auto">
                        <table class="table table-striped table-hover"
                        >
                            <tr>
                                <td class="pe-4">
                                    Seria
                                </td>
                                <td>
                                    {{ $factura->seria }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Număr
                                </td>
                                <td>
                                    {{ $factura->numar }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Data
                                </td>
                                <td>
                                    {{ $factura->data ? \Carbon\Carbon::parse($factura->data)->isoFormat('DD.MM.YYYY') : '' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Aplicație
                                </td>
                                <td>
                                    {{ $factura->actualizari->first()->aplicatie->nume ?? '' }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <table style="border: 1px solid black">
                                        @foreach ($factura->actualizari->sortBy('nume') as $actualizare)
                                            @if ($loop->first)
                                                <tr>
                                                    <th class="text-center" style="border: 1px solid black">Lucrări</th>
                                                    <th class="text-center" style="border: 1px solid black">Preț</th>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td class="px-2" style="border: 1px solid black">
                                                    <p class="my-0 fw-bold">{{ $actualizare->nume }}</p>
                                                    @if ($actualizare->descriere)
                                                        <br>
                                                        {!! $actualizare->descriere !!}
                                                    @endif
                                                    @if ($actualizare->observatii_pentru_client)
                                                        <br>
                                                        {!! $actualizare->observatii_pentru_client !!}
                                                    @endif
                                                </td>
                                                <td class="text-end px-2" style="border: 1px solid black">
                                                    <nobr>
                                                    {{ $actualizare->pret }} lei
                                                    </nobr>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="form-row mb-2 px-2">
                        <div class="col-lg-12 d-flex justify-content-center">
                            <a class="btn btn-secondary text-white rounded-3" href="{{ Session::get('facturaReturnUrl') }}">Înapoi</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
