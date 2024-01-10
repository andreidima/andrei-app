@extends ('layouts.app')

@php
    use \Carbon\Carbon;
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-envelope me-1"></i>Notificări
                </span>
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors.errors')

            @php
                $nrCrt = 1;
            @endphp

            <div class="table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded">
                        <tr class="thead-danger" style="padding:2rem">
                            <th class="text-white culoare2">#</th>
                            <th class="text-white culoare2">Notificare</th>
                            <th class="text-white culoare2">Resursa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (\App\Models\Apps\Factura::whereDate('data', '<=', Carbon::now())->whereNotNull('confirmare_client')->whereNull('confirmare_validsoftware')->get() as $factura)
                            <tr>
                                <td align="">
                                    {{ $nrCrt++ }}
                                </td>
                                <td class="">
                                    Factură neconfirmată de către Validsoftware
                                </td>
                                <td>
                                    <a href="{{ $factura->path() }}/modifica" style="text-decoration: none">{{ $factura->actualizari->first()->aplicatie->nume }}</a>
                                </td>
                            </tr>
                        @endforeach
                        @foreach (\App\Models\Apps\Factura::whereDate('data', '<=', Carbon::now())->whereNull('confirmare_client')->get() as $factura)
                            <tr>
                                <td align="">
                                    {{ $nrCrt++ }}
                                </td>
                                <td class="">
                                    Factură neconfirmată de către client
                                </td>
                                <td>
                                    <a href="{{ $factura->path() }}/modifica" style="text-decoration: none">{{ $factura->actualizari->first()->aplicatie->nume }}</a>
                                </td>
                            </tr>
                        @endforeach
                        @foreach (\App\Models\Apps\Actualizare::whereNotNull('pret')->whereDoesntHave('factura')->get() as $actualizare)
                            <tr>
                                <td align="">
                                    {{ $nrCrt++ }}
                                </td>
                                <td class="">
                                    Actualizare nefacturată
                                </td>
                                <td>
                                    <a href="{{ $actualizare->path() }}/modifica" style="text-decoration: none">{{ $actualizare->aplicatie->nume }} / {{ $actualizare->nume }}</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
