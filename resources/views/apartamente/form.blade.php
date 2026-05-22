@csrf

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
                <label for="cheltuieli_lunare" class="mb-0 ps-3">Cheltuieli lunare EUR</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('cheltuieli_lunare') ? 'is-invalid' : '' }}" name="cheltuieli_lunare" value="{{ old('cheltuieli_lunare', $apartament->cheltuieli_lunare) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="costuri_extra_estimate" class="mb-0 ps-3">Costuri extra estimate EUR</label>
                <input type="number" min="0" class="form-control bg-white rounded-3 {{ $errors->has('costuri_extra_estimate') ? 'is-invalid' : '' }}" name="costuri_extra_estimate" value="{{ old('costuri_extra_estimate', $apartament->costuri_extra_estimate) }}">
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
                <label for="etaj" class="mb-0 ps-3">Etaj</label>
                <input type="number" class="form-control bg-white rounded-3 {{ $errors->has('etaj') ? 'is-invalid' : '' }}" name="etaj" value="{{ old('etaj', $apartament->etaj) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="agentie" class="mb-0 ps-3">Agentie</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('agentie') ? 'is-invalid' : '' }}" name="agentie" value="{{ old('agentie', $apartament->agentie) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="peb" class="mb-0 ps-3">PEB</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('peb') ? 'is-invalid' : '' }}" name="peb" value="{{ old('peb', $apartament->peb) }}">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="orientare_lumina" class="mb-0 ps-3">Lumina / orientare</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('orientare_lumina') ? 'is-invalid' : '' }}" name="orientare_lumina" value="{{ old('orientare_lumina', $apartament->orientare_lumina) }}">
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
            <div class="col-lg-6 mb-4">
                <label for="link_anunt" class="mb-0 ps-3">Link anunt</label>
                <input type="url" class="form-control bg-white rounded-3 {{ $errors->has('link_anunt') ? 'is-invalid' : '' }}" name="link_anunt" value="{{ old('link_anunt', $apartament->link_anunt) }}">
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
