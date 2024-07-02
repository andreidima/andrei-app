@csrf

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0">
            <div class="col-lg-6 mb-4">
                <label for="nume" class="mb-0 ps-3">Nume<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
                    name="nume"
                    value="{{ old('nume', $aplicatie->nume) }}">
            </div>
            <div class="col-lg-6 mb-4">
                <label for="local_url" class="mb-0 ps-3">Local url</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('local_url') ? 'is-invalid' : '' }}"
                    name="local_url"
                    value="{{ old('local_url', $aplicatie->local_url) }}">
            </div>
            <div class="col-lg-6 mb-4">
                <label for="online_url" class="mb-0 ps-3">Online url</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('online_url') ? 'is-invalid' : '' }}"
                    name="online_url"
                    value="{{ old('online_url', $aplicatie->online_url) }}">
            </div>
            <div class="col-lg-6 mb-4">
                <label for="github_url" class="mb-0 ps-3">Github url</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('github_url') ? 'is-invalid' : '' }}"
                    name="github_url"
                    value="{{ old('github_url', $aplicatie->github_url) }}">
            </div>
            <div class="col-lg-6 mb-4">
                <label for="php_version" class="mb-0 ps-3">Php version</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('php_version') ? 'is-invalid' : '' }}"
                    name="php_version"
                    value="{{ old('php_version', $aplicatie->php_version) }}">
            </div>
            <div class="col-lg-6 mb-4">
                <label for="laravel_version" class="mb-0 ps-3">Laravel version</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('laravel_version') ? 'is-invalid' : '' }}"
                    name="laravel_version"
                    value="{{ old('laravel_version', $aplicatie->laravel_version) }}">
            </div>
            <div class="col-lg-6 mb-4">
                <label for="vue_version" class="mb-0 ps-3">Vue version</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('vue_version') ? 'is-invalid' : '' }}"
                    name="vue_version"
                    value="{{ old('vue_version', $aplicatie->vue_version) }}">
            </div>
        </div>
        <div class="row mb-0">
            <div class="col-lg-6 mb-4">
                <label for="urls" class="mb-0 ps-3">Urls</label>
                <textarea class="form-control bg-white {{ $errors->has('urls') ? 'is-invalid' : '' }}"
                    name="urls" rows="3"
                    >{{ $aplicatie->urls }}</textarea>
                <small class="ps-3">
                    Multiple links must be separated by commas
                </small>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="urls_info" class="mb-0 ps-3">Urls info</label>
                <textarea class="form-control bg-white {{ $errors->has('urls_info') ? 'is-invalid' : '' }}"
                    name="urls_info" rows="3"
                    >{{ $aplicatie->urls_info }}</textarea>
            </div>
        </div>
        <div class="row mb-0">
            <div class="col-lg-6 mb-4">
                <label for="software_tools" class="mb-0 ps-3">Software tools</label>
                <textarea class="form-control bg-white {{ $errors->has('software_tools') ? 'is-invalid' : '' }}"
                    name="software_tools" rows="3"
                    >{{ $aplicatie->software_tools }}</textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('aplicatieReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
