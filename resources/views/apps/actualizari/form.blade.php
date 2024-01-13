@csrf

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0 justify-content-center">
            <div class="col-lg-3 mb-4">
                <label for="aplicatie_id" class="mb-0 ps-3">Aplicație<span class="text-danger">*</span></label>
                <select name="aplicatie_id" class="form-select bg-white rounded-3 {{ $errors->has('aplicatie_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($aplicatii as $aplicatie)
                        <option value="{{ $aplicatie->id }}" {{ ($aplicatie->id === intval(old('aplicatie_id', $actualizare->aplicatie_id))) ? 'selected' : '' }}>{{ $aplicatie->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-5 mb-4">
                <label for="nume" class="mb-0 ps-3">Nume<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
                    name="nume"
                    value="{{ old('nume', $actualizare->nume) }}">
            </div>
            <div class="col-lg-2 mb-4">
                <label for="pret" class="mb-0 ps-3">Preț<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('pret') ? 'is-invalid' : '' }}"
                    name="pret"
                    value="{{ old('pret', $actualizare->pret) }}">
            </div>
            <div class="col-lg-2 mb-4">
                <label for="remunerare" class="mb-0 ps-3">Remunerare<span class="text-danger">*</span></label>
                <select name="remunerare" class="form-select bg-white rounded-3 {{ $errors->has('remunerare') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    <option value="1" {{ ((intval(old('remunerare', $actualizare->remunerare))) === 1) ? 'selected' : '' }}>Factură</option>
                    <option value="2" {{ ((intval(old('remunerare', $actualizare->remunerare))) === 2) ? 'selected' : '' }}>Mentenanță</option>
                    <option value="3" {{ ((intval(old('remunerare', $actualizare->remunerare))) === 3) ? 'selected' : '' }}>Pagubă</option>
                </select>
            </div>
        </div>
        <div class="row mb-0 justify-content-center" id="TinyMCE">
            <div class="col-lg-12 mb-4">
                <label for="descriere" class="mb-0 ps-3">Descriere:</label>
                <tinymce-vue
                    inputvalue="{{ old('descriere', $actualizare->descriere) }}"
                    height= 600
                    inputname="descriere"
                ></tinymce-vue>
            </div>
            <div class="col-lg-12 mb-4">
                <label for="observatii_pentru_client" class="mb-0 ps-3">Observații pentru client:</label>
                <tinymce-vue
                    inputvalue="{{ old('observatii_pentru_client', $actualizare->observatii_pentru_client) }}"
                    height= 300
                    inputname="observatii_pentru_client"
                ></tinymce-vue>
            </div>
            <div class="col-lg-12 mb-4">
                <label for="observatii_personale" class="mb-0 ps-3">Observații personale:</label>
                <tinymce-vue
                    inputvalue="{{ old('observatii_personale', $actualizare->observatii_personale) }}"
                    height= 300
                    inputname="observatii_personale"
                ></tinymce-vue>
            </div>
        </div>
    </div>

    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('actualizareReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
