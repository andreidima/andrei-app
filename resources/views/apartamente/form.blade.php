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
