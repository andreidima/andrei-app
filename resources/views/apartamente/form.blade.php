@csrf
@php
    $selectedStatus = old('status', $apartament->status ?: 'de_vazut');
    $defaultWatchlistDate = $selectedStatus === 'de_urmarit' && ! $apartament->exists ? now()->format('Y-m-d\TH:i') : '';
@endphp

<div class="row mb-0 px-3">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0">
            <div class="col-lg-8 mb-4">
                <label for="adresa" class="mb-0 ps-3">Adresa<span class="text-danger">*</span></label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('adresa') ? 'is-invalid' : '' }}" name="adresa" value="{{ old('adresa', $apartament->adresa) }}">
            </div>
            <div class="col-lg-4 mb-4">
                <label for="localitate" class="mb-0 ps-3">Localitate</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('localitate') ? 'is-invalid' : '' }}" name="localitate" value="{{ old('localitate', $apartament->localitate) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="status" class="mb-0 ps-3">Status<span class="text-danger">*</span></label>
                <select class="form-select bg-white rounded-3 {{ $errors->has('status') ? 'is-invalid' : '' }}" name="status">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $apartament->status ?: 'de_vazut') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-4">
                <label for="decizie" class="mb-0 ps-3">Decizie</label>
                <select class="form-select bg-white rounded-3 {{ $errors->has('decizie') ? 'is-invalid' : '' }}" name="decizie">
                    <option value="">Fara decizie</option>
                    @foreach ($decisionOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('decizie', $apartament->decizie) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-4">
                <label for="vizionare_at" class="mb-0 ps-3">Data vizionare</label>
                <input
                    type="datetime-local"
                    class="form-control bg-white rounded-3 {{ $errors->has('vizionare_at') ? 'is-invalid' : '' }}"
                    name="vizionare_at"
                    value="{{ old('vizionare_at', $apartament->vizionare_at ? $apartament->vizionare_at->format('Y-m-d\TH:i') : '') }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="pret" class="mb-0 ps-3">Pret EUR</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('pret') ? 'is-invalid' : '' }}" name="pret" value="{{ old('pret', $apartament->pret) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="pret_maxim_oferta" class="mb-0 ps-3">Pret maxim oferta EUR</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('pret_maxim_oferta') ? 'is-invalid' : '' }}" name="pret_maxim_oferta" value="{{ old('pret_maxim_oferta', $apartament->pret_maxim_oferta) }}">
            </div>
            <div class="col-lg-12 mb-3">
                <div class="border rounded-3 p-3 bg-light">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <div class="fw-bold">Monitorizare anunt / De urmarit</div>
                            <div class="small text-muted">Foloseste campurile acestea pentru apartamente salvate inainte sa ceri sau sa programezi vizionarea.</div>
                        </div>
                        <span class="badge bg-warning text-dark align-self-start">De urmarit</span>
                    </div>
                    <div class="row">
                        <div class="col-lg-3 mb-3">
                            <label for="adaugat_in_lista_at" class="mb-0 ps-3">Adaugat in lista</label>
                            <input
                                type="datetime-local"
                                class="form-control bg-white rounded-3 {{ $errors->has('adaugat_in_lista_at') ? 'is-invalid' : '' }}"
                                name="adaugat_in_lista_at"
                                value="{{ old('adaugat_in_lista_at', $apartament->adaugat_in_lista_at ? $apartament->adaugat_in_lista_at->format('Y-m-d\TH:i') : $defaultWatchlistDate) }}">
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label for="pret_initial" class="mb-0 ps-3">Pret initial EUR</label>
                            <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('pret_initial') ? 'is-invalid' : '' }}" name="pret_initial" value="{{ old('pret_initial', $apartament->pret_initial) }}">
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label for="pret_curent" class="mb-0 ps-3">Pret curent EUR</label>
                            <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('pret_curent') ? 'is-invalid' : '' }}" name="pret_curent" value="{{ old('pret_curent', $apartament->pret_curent) }}">
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label for="ultima_verificare_at" class="mb-0 ps-3">Ultima verificare</label>
                            <input
                                type="datetime-local"
                                class="form-control bg-white rounded-3 {{ $errors->has('ultima_verificare_at') ? 'is-invalid' : '' }}"
                                name="ultima_verificare_at"
                                value="{{ old('ultima_verificare_at', $apartament->ultima_verificare_at ? $apartament->ultima_verificare_at->format('Y-m-d\TH:i') : '') }}">
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label for="status_anunt" class="mb-0 ps-3">Status anunt</label>
                            <select class="form-select bg-white rounded-3 {{ $errors->has('status_anunt') ? 'is-invalid' : '' }}" name="status_anunt">
                                <option value="">Fara status</option>
                                @foreach ($listingStatusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status_anunt', $apartament->status_anunt ?: ($selectedStatus === 'de_urmarit' ? 'activ' : null)) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-9 mb-3">
                            <label for="observatii_status_anunt" class="mb-0 ps-3">Observatii status anunt</label>
                            <textarea class="form-control bg-white {{ $errors->has('observatii_status_anunt') ? 'is-invalid' : '' }}" name="observatii_status_anunt" rows="2">{{ old('observatii_status_anunt', $apartament->observatii_status_anunt) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 mb-4">
                <label for="cheltuieli_lunare" class="mb-0 ps-3">Cheltuieli lunare EUR</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('cheltuieli_lunare') ? 'is-invalid' : '' }}" name="cheltuieli_lunare" value="{{ old('cheltuieli_lunare', $apartament->cheltuieli_lunare) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="costuri_extra_estimate" class="mb-0 ps-3">Costuri extra estimate EUR</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('costuri_extra_estimate') ? 'is-invalid' : '' }}" name="costuri_extra_estimate" value="{{ old('costuri_extra_estimate', $apartament->costuri_extra_estimate) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="venit_cadastral" class="mb-0 ps-3">Venit cadastral EUR</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('venit_cadastral') ? 'is-invalid' : '' }}" name="venit_cadastral" value="{{ old('venit_cadastral', $apartament->venit_cadastral) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="prioritate" class="mb-0 ps-3">Prioritate 1-5</label>
                <input type="number" min="1" max="5" class="form-control bg-white rounded-3 {{ $errors->has('prioritate') ? 'is-invalid' : '' }}" name="prioritate" value="{{ old('prioritate', $apartament->prioritate) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="scor" class="mb-0 ps-3">Scor 1-10</label>
                <input type="number" min="1" max="10" class="form-control bg-white rounded-3 {{ $errors->has('scor') ? 'is-invalid' : '' }}" name="scor" value="{{ old('scor', $apartament->scor) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="suprafata_mp" class="mb-0 ps-3">Suprafata mp</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('suprafata_mp') ? 'is-invalid' : '' }}" name="suprafata_mp" value="{{ old('suprafata_mp', $apartament->suprafata_mp) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="camere" class="mb-0 ps-3">Camere</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('camere') ? 'is-invalid' : '' }}" name="camere" value="{{ old('camere', $apartament->camere) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="bai" class="mb-0 ps-3">Bai</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('bai') ? 'is-invalid' : '' }}" name="bai" value="{{ old('bai', $apartament->bai) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="toalete" class="mb-0 ps-3">Toalete</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('toalete') ? 'is-invalid' : '' }}" name="toalete" value="{{ old('toalete', $apartament->toalete) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="etaj" class="mb-0 ps-3">Etaj</label>
                <input type="number" class="form-control bg-white rounded-3 {{ $errors->has('etaj') ? 'is-invalid' : '' }}" name="etaj" value="{{ old('etaj', $apartament->etaj) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="etaje_cladire" class="mb-0 ps-3">Etaje cladire</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('etaje_cladire') ? 'is-invalid' : '' }}" name="etaje_cladire" value="{{ old('etaje_cladire', $apartament->etaje_cladire) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="an_constructie" class="mb-0 ps-3">An constructie</label>
                <input type="number" min="1800" class="form-control bg-white rounded-3 {{ $errors->has('an_constructie') ? 'is-invalid' : '' }}" name="an_constructie" value="{{ old('an_constructie', $apartament->an_constructie) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="agentie" class="mb-0 ps-3">Agentie</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('agentie') ? 'is-invalid' : '' }}" name="agentie" value="{{ old('agentie', $apartament->agentie) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="agent_nume" class="mb-0 ps-3">Agent</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('agent_nume') ? 'is-invalid' : '' }}" name="agent_nume" value="{{ old('agent_nume', $apartament->agent->name ?? '') }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="agent_email" class="mb-0 ps-3">Email agent</label>
                <input type="email" class="form-control bg-white rounded-3 {{ $errors->has('agent_email') ? 'is-invalid' : '' }}" name="agent_email" value="{{ old('agent_email', $apartament->agent->email ?? '') }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="agent_telefon" class="mb-0 ps-3">Telefon agent</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('agent_telefon') ? 'is-invalid' : '' }}" name="agent_telefon" value="{{ old('agent_telefon', $apartament->agent->phone ?? '') }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="peb" class="mb-0 ps-3">PEB</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('peb') ? 'is-invalid' : '' }}" name="peb" value="{{ old('peb', $apartament->peb) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="peb_consum" class="mb-0 ps-3">PEB kWh/m2/an</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('peb_consum') ? 'is-invalid' : '' }}" name="peb_consum" value="{{ old('peb_consum', $apartament->peb_consum) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="tip_incalzire" class="mb-0 ps-3">Incalzire</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('tip_incalzire') ? 'is-invalid' : '' }}" name="tip_incalzire" value="{{ old('tip_incalzire', $apartament->tip_incalzire) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="stare_cladire" class="mb-0 ps-3">Stare cladire</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('stare_cladire') ? 'is-invalid' : '' }}" name="stare_cladire" value="{{ old('stare_cladire', $apartament->stare_cladire) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="stare_apartament" class="mb-0 ps-3">Stare apartament</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('stare_apartament') ? 'is-invalid' : '' }}" name="stare_apartament" value="{{ old('stare_apartament', $apartament->stare_apartament) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="orientare_lumina" class="mb-0 ps-3">Lumina / orientare</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('orientare_lumina') ? 'is-invalid' : '' }}" name="orientare_lumina" value="{{ old('orientare_lumina', $apartament->orientare_lumina) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="orientare_terasa" class="mb-0 ps-3">Orientare terasa</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('orientare_terasa') ? 'is-invalid' : '' }}" name="orientare_terasa" value="{{ old('orientare_terasa', $apartament->orientare_terasa) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="renovare_necesara" class="mb-0 ps-3">Renovare necesara</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('renovare_necesara') ? 'is-invalid' : '' }}" name="renovare_necesara" value="{{ old('renovare_necesara', $apartament->renovare_necesara) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="zgomot" class="mb-0 ps-3">Zgomot</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('zgomot') ? 'is-invalid' : '' }}" name="zgomot" value="{{ old('zgomot', $apartament->zgomot) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="zona" class="mb-0 ps-3">Zona</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('zona') ? 'is-invalid' : '' }}" name="zona" value="{{ old('zona', $apartament->zona) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="disponibil_din" class="mb-0 ps-3">Disponibil din</label>
                <input type="date" class="form-control bg-white rounded-3 {{ $errors->has('disponibil_din') ? 'is-invalid' : '' }}" name="disponibil_din" value="{{ old('disponibil_din', $apartament->disponibil_din ? $apartament->disponibil_din->format('Y-m-d') : '') }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="motivatie_achizitie" class="mb-0 ps-3">Motivatie achizitie</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('motivatie_achizitie') ? 'is-invalid' : '' }}" name="motivatie_achizitie" value="{{ old('motivatie_achizitie', $apartament->motivatie_achizitie) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <input type="hidden" name="electricitate_conforma" value="0">
                <label class="mb-0 ps-3 d-block">Electricitate conforma</label>
                <input type="checkbox" class="form-check-input ms-3" name="electricitate_conforma" value="1" @checked(old('electricitate_conforma', $apartament->electricitate_conforma))>
            </div>
            <div class="col-lg-3 mb-4">
                <input type="hidden" name="are_lift" value="0">
                <label class="mb-0 ps-3 d-block">Lift</label>
                <input type="checkbox" class="form-check-input ms-3" name="are_lift" value="1" @checked(old('are_lift', $apartament->are_lift))>
            </div>
            <div class="col-lg-3 mb-4">
                <input type="hidden" name="are_balcon" value="0">
                <label class="mb-0 ps-3 d-block">Balcon / terasa</label>
                <input type="checkbox" class="form-check-input ms-3" name="are_balcon" value="1" @checked(old('are_balcon', $apartament->are_balcon))>
            </div>
            <div class="col-lg-3 mb-4">
                <input type="hidden" name="are_parcare" value="0">
                <label class="mb-0 ps-3 d-block">Parcare</label>
                <input type="checkbox" class="form-check-input ms-3" name="are_parcare" value="1" @checked(old('are_parcare', $apartament->are_parcare))>
            </div>
            <div class="col-lg-3 mb-4">
                <input type="hidden" name="are_pivnita" value="0">
                <label class="mb-0 ps-3 d-block">Pivnita</label>
                <input type="checkbox" class="form-check-input ms-3" name="are_pivnita" value="1" @checked(old('are_pivnita', $apartament->are_pivnita))>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="link_anunt" class="mb-0 ps-3">Link anunt</label>
                <input type="url" class="form-control bg-white rounded-3 {{ $errors->has('link_anunt') ? 'is-invalid' : '' }}" name="link_anunt" value="{{ old('link_anunt', $apartament->link_anunt) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="sursa_anunt" class="mb-0 ps-3">Sursa anunt</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('sursa_anunt') ? 'is-invalid' : '' }}" name="sursa_anunt" value="{{ old('sursa_anunt', $apartament->sursa_anunt) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="referinta_anunt" class="mb-0 ps-3">Referinta anunt</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('referinta_anunt') ? 'is-invalid' : '' }}" name="referinta_anunt" value="{{ old('referinta_anunt', $apartament->referinta_anunt) }}">
            </div>
            <div class="col-lg-6 mb-4">
                <label for="contact" class="mb-0 ps-3">Contact</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('contact') ? 'is-invalid' : '' }}" name="contact" value="{{ old('contact', $apartament->contact) }}">
            </div>
        </div>

        <div class="row mb-0">
            <div class="col-lg-6 mb-4">
                <label for="puncte_bune" class="mb-0 ps-3">Ce ne place</label>
                <textarea class="form-control bg-white {{ $errors->has('puncte_bune') ? 'is-invalid' : '' }}" name="puncte_bune" rows="4">{{ old('puncte_bune', $apartament->puncte_bune) }}</textarea>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="puncte_slabe" class="mb-0 ps-3">Ce nu ne place</label>
                <textarea class="form-control bg-white {{ $errors->has('puncte_slabe') ? 'is-invalid' : '' }}" name="puncte_slabe" rows="4">{{ old('puncte_slabe', $apartament->puncte_slabe) }}</textarea>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="riscuri_intrebari" class="mb-0 ps-3">Riscuri si intrebari</label>
                <textarea class="form-control bg-white {{ $errors->has('riscuri_intrebari') ? 'is-invalid' : '' }}" name="riscuri_intrebari" rows="4">{{ old('riscuri_intrebari', $apartament->riscuri_intrebari) }}</textarea>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="observatii" class="mb-0 ps-3">Observatii</label>
                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}" name="observatii" rows="4">{{ old('observatii', $apartament->observatii) }}</textarea>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="motiv_respingere" class="mb-0 ps-3">Motiv respingere</label>
                <textarea class="form-control bg-white {{ $errors->has('motiv_respingere') ? 'is-invalid' : '' }}" name="motiv_respingere" rows="4">{{ old('motiv_respingere', $apartament->motiv_respingere) }}</textarea>
            </div>
        </div>

        <div class="row mb-0 border-top pt-3">
            <div class="col-lg-3 mb-4">
                <label for="interaction_type" class="mb-0 ps-3">Adauga interactiune</label>
                <select class="form-select bg-white rounded-3 {{ $errors->has('interaction_type') ? 'is-invalid' : '' }}" name="interaction_type">
                    <option value="">Nu adauga acum</option>
                    @foreach ($interactionOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('interaction_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-4">
                <label for="interaction_at" class="mb-0 ps-3">Data interactiune</label>
                <input
                    type="datetime-local"
                    class="form-control bg-white rounded-3 {{ $errors->has('interaction_at') ? 'is-invalid' : '' }}"
                    name="interaction_at"
                    value="{{ old('interaction_at') }}">
            </div>
            <div class="col-lg-6 mb-4">
                <label for="interaction_notes" class="mb-0 ps-3">Note interactiune</label>
                <textarea class="form-control bg-white {{ $errors->has('interaction_notes') ? 'is-invalid' : '' }}" name="interaction_notes" rows="2">{{ old('interaction_notes') }}</textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('apartamentReturnUrl') }}">Renunta</a>
            </div>
        </div>
    </div>
</div>
