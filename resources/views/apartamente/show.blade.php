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
                                <td class="pe-4">Decizie</td>
                                <td>
                                    {{ $apartament->decision_label }}
                                    @if ($apartament->prioritate)
                                        <span class="text-muted">/ prioritate {{ $apartament->prioritate }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">Vizionare</td>
                                <td>{{ $apartament->vizionare_at ? $apartament->vizionare_at->format('d.m.Y H:i') : '' }}</td>
                            </tr>
                            @if ($apartament->status === 'de_urmarit' || $apartament->adaugat_in_lista_at || $apartament->pret_initial || $apartament->pret_curent || $apartament->status_anunt || $apartament->ultima_verificare_at)
                                <tr>
                                    <td class="pe-4">Monitorizare anunt</td>
                                    <td>
                                        @if ($apartament->adaugat_in_lista_at)
                                            Adaugat in lista: {{ $apartament->adaugat_in_lista_at->format('d.m.Y H:i') }}<br>
                                        @endif
                                        @if ($apartament->pret_initial)
                                            Pret initial: {{ number_format($apartament->pret_initial, 0, ',', '.') }} EUR<br>
                                        @endif
                                        @if ($apartament->pret_curent)
                                            Pret curent: {{ number_format($apartament->pret_curent, 0, ',', '.') }} EUR<br>
                                        @endif
                                        @if (! is_null($apartament->watchlist_price_difference) && $apartament->watchlist_price_difference !== 0)
                                            Diferenta: {{ $apartament->watchlist_price_difference > 0 ? '+' : '' }}{{ number_format($apartament->watchlist_price_difference, 0, ',', '.') }} EUR<br>
                                        @endif
                                        @if ($apartament->status_anunt)
                                            Status anunt: {{ $apartament->status_anunt_label }}<br>
                                        @endif
                                        @if ($apartament->ultima_verificare_at)
                                            Ultima verificare: {{ $apartament->ultima_verificare_at->format('d.m.Y H:i') }}<br>
                                        @endif
                                        @if ($apartament->observatii_status_anunt)
                                            {!! nl2br(e($apartament->observatii_status_anunt)) !!}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="pe-4">Pret</td>
                                <td>
                                    {{ $apartament->pret ? number_format($apartament->pret, 0, ',', '.') . ' EUR' : '' }}
                                    @if ($apartament->pret_maxim_oferta)
                                        <br>Oferta maxima: {{ number_format($apartament->pret_maxim_oferta, 0, ',', '.') }} EUR
                                    @endif
                                    @if ($apartament->cheltuieli_lunare)
                                        <br>Cheltuieli lunare: {{ number_format($apartament->cheltuieli_lunare, 0, ',', '.') }} EUR
                                    @endif
                                    @if ($apartament->costuri_extra_estimate)
                                        <br>Costuri extra estimate: {{ number_format($apartament->costuri_extra_estimate, 0, ',', '.') }} EUR
                                    @endif
                                    @if ($apartament->venit_cadastral)
                                        <br>Venit cadastral: {{ number_format($apartament->venit_cadastral, 0, ',', '.') }} EUR
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">Date</td>
                                <td>
                                    @if ($apartament->camere)
                                        {{ $apartament->camere }} camere<br>
                                    @endif
                                    @if ($apartament->bai)
                                        {{ $apartament->bai }} bai<br>
                                    @endif
                                    @if ($apartament->toalete)
                                        {{ $apartament->toalete }} toalete<br>
                                    @endif
                                    @if ($apartament->suprafata_mp)
                                        {{ $apartament->suprafata_mp }} mp<br>
                                    @endif
                                    @if (! is_null($apartament->etaj))
                                        Etaj {{ $apartament->etaj }}{{ $apartament->etaje_cladire ? '/' . $apartament->etaje_cladire : '' }}<br>
                                    @endif
                                    @if ($apartament->an_constructie)
                                        An constructie {{ $apartament->an_constructie }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">Tehnic</td>
                                <td>
                                    @if ($apartament->peb)
                                        PEB {{ $apartament->peb }}{{ $apartament->peb_consum ? ' / ' . $apartament->peb_consum . ' kWh/m2/an' : '' }}<br>
                                    @endif
                                    @if ($apartament->tip_incalzire)
                                        Incalzire: {{ $apartament->tip_incalzire }}<br>
                                    @endif
                                    @if (! is_null($apartament->electricitate_conforma))
                                        Electricitate conforma: {{ $apartament->electricitate_conforma ? 'Da' : 'Nu' }}<br>
                                    @endif
                                    @if ($apartament->stare_cladire)
                                        Stare cladire: {{ $apartament->stare_cladire }}<br>
                                    @endif
                                    @if ($apartament->stare_apartament)
                                        Stare apartament: {{ $apartament->stare_apartament }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">Dotari</td>
                                <td>
                                    @if (! is_null($apartament->are_lift))
                                        Lift: {{ $apartament->are_lift ? 'Da' : 'Nu' }}<br>
                                    @endif
                                    @if (! is_null($apartament->are_balcon))
                                        Balcon / terasa: {{ $apartament->are_balcon ? 'Da' : 'Nu' }}<br>
                                    @endif
                                    @if (! is_null($apartament->are_parcare))
                                        Parcare: {{ $apartament->are_parcare ? 'Da' : 'Nu' }}<br>
                                    @endif
                                    @if (! is_null($apartament->are_pivnita))
                                        Pivnita: {{ $apartament->are_pivnita ? 'Da' : 'Nu' }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">Zona / orientare</td>
                                <td>
                                    @if ($apartament->zona)
                                        Zona: {{ $apartament->zona }}<br>
                                    @endif
                                    @if ($apartament->orientare_lumina)
                                        Lumina: {{ $apartament->orientare_lumina }}<br>
                                    @endif
                                    @if ($apartament->orientare_terasa)
                                        Orientare terasa: {{ $apartament->orientare_terasa }}<br>
                                    @endif
                                    @if ($apartament->zgomot)
                                        Zgomot: {{ $apartament->zgomot }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">Link anunt</td>
                                <td>
                                    @if ($apartament->link_anunt)
                                        <a href="{{ $apartament->link_anunt }}" target="_blank" rel="noopener noreferrer">{{ $apartament->link_anunt }}</a>
                                        @if ($apartament->sursa_anunt || $apartament->referinta_anunt)
                                            <br>{{ $apartament->sursa_anunt }} {{ $apartament->referinta_anunt ? '#'.$apartament->referinta_anunt : '' }}
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">Agentie / contact</td>
                                <td>{{ $apartament->agency?->name ?: $apartament->agentie }} {{ $apartament->agent_contact ? '/ ' . $apartament->agent_contact : '' }}</td>
                            </tr>
                            <tr>
                                <td class="pe-4">Disponibilitate</td>
                                <td>{{ $apartament->disponibil_din ? $apartament->disponibil_din->format('d.m.Y') : '' }}</td>
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
                                <td class="pe-4">Motiv respingere</td>
                                <td>{!! nl2br(e($apartament->motiv_respingere)) !!}</td>
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

                    <div class="table-responsive col-md-12 mx-auto">
                        <h5 class="px-2">Istoric interactiuni</h5>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tip</th>
                                    <th>Contact</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($apartament->interactions->sortByDesc('interacted_at') as $interaction)
                                    <tr>
                                        <td>{{ $interaction->interacted_at ? $interaction->interacted_at->format('d.m.Y H:i') : '-' }}</td>
                                        <td>{{ $interaction->type_label }}</td>
                                        <td>
                                            {{ $interaction->agency?->name }}
                                            @if ($interaction->agent)
                                                <div class="small text-muted">{{ $interaction->agent->display_contact }}</div>
                                            @endif
                                        </td>
                                        <td>{!! nl2br(e($interaction->notes)) !!}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Nu exista interactiuni salvate.</td>
                                    </tr>
                                @endforelse
                            </tbody>
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
