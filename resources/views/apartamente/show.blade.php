@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="shadow-lg" style="border-radius: 40px;">
                <div class="culoare2 border border-secondary p-2" style="border-radius: 40px 40px 0 0;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-building me-1"></i>Apartamente / {{ $apartament->adresa }}
                    </span>
                </div>

                <div class="card-body py-2 border border-secondary" style="border-radius: 0 0 40px 40px;">
                    @include ('errors.errors')

                    <div class="table-responsive col-md-12 mx-auto">
                        <table class="table table-striped table-hover">
                            <tr>
                                <td class="pe-4">Adresa</td>
                                <td>{{ $apartament->adresa }}</td>
                            </tr>
                            <tr>
                                <td class="pe-4">Localitate</td>
                                <td>{{ $apartament->localitate }}</td>
                            </tr>
                            <tr>
                                <td class="pe-4">Status</td>
                                <td><span class="badge {{ $apartament->status_badge }}">{{ $apartament->status_label }}</span></td>
                            </tr>
                            <tr>
                                <td class="pe-4">Vizionare</td>
                                <td>{{ $apartament->vizionare_at ? $apartament->vizionare_at->format('d.m.Y H:i') : '' }}</td>
                            </tr>
                            <tr>
                                <td class="pe-4">Pret</td>
                                <td>{{ $apartament->pret ? number_format($apartament->pret, 0, ',', '.') . ' EUR' : '' }}</td>
                            </tr>
                            <tr>
                                <td class="pe-4">Date</td>
                                <td>
                                    @if ($apartament->camere)
                                        {{ $apartament->camere }} camere<br>
                                    @endif
                                    @if ($apartament->suprafata_mp)
                                        {{ $apartament->suprafata_mp }} mp<br>
                                    @endif
                                    @if (! is_null($apartament->etaj))
                                        Etaj {{ $apartament->etaj }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">Link anunt</td>
                                <td>
                                    @if ($apartament->link_anunt)
                                        <a href="{{ $apartament->link_anunt }}" target="_blank" rel="noopener noreferrer">{{ $apartament->link_anunt }}</a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">Agentie / contact</td>
                                <td>{{ $apartament->agentie }} {{ $apartament->contact ? '/ ' . $apartament->contact : '' }}</td>
                            </tr>
                            <tr>
                                <td class="pe-4">Ce ne place</td>
                                <td>{!! nl2br(e($apartament->puncte_bune)) !!}</td>
                            </tr>
                            <tr>
                                <td class="pe-4">Ce nu ne place</td>
                                <td>{!! nl2br(e($apartament->puncte_slabe)) !!}</td>
                            </tr>
                            <tr>
                                <td class="pe-4">Riscuri si intrebari</td>
                                <td>{!! nl2br(e($apartament->riscuri_intrebari)) !!}</td>
                            </tr>
                            <tr>
                                <td class="pe-4">Observatii</td>
                                <td>{!! nl2br(e($apartament->observatii)) !!}</td>
                            </tr>
                            <tr>
                                <td class="pe-4">Scor</td>
                                <td>{{ $apartament->scor ? $apartament->scor . '/10' : '' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="form-row mb-2 px-2">
                        <div class="col-lg-12 d-flex justify-content-center gap-2">
                            <a class="btn btn-primary text-white rounded-3" href="{{ $apartament->path() }}/modifica">Modifica</a>
                            <a class="btn btn-secondary text-white rounded-3" href="{{ Session::get('apartamentReturnUrl') }}">Inapoi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
