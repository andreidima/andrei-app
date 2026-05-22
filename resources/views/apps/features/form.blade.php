@csrf

<div class="row mb-0 px-3">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0">
            <div class="col-lg-6 mb-4">
                <label for="name" class="mb-0 ps-3">Name<span class="text-danger">*</span></label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('name') ? 'is-invalid' : '' }}" name="name" value="{{ old('name', $feature->name) }}">
            </div>
            <div class="col-lg-4 mb-4">
                <label for="slug" class="mb-0 ps-3">Slug</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('slug') ? 'is-invalid' : '' }}" name="slug" value="{{ old('slug', $feature->slug) }}">
            </div>
            <div class="col-lg-2 mb-4 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $feature->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="category" class="mb-0 ps-3">Category</label>
                <input type="text" class="form-control bg-white rounded-3 {{ $errors->has('category') ? 'is-invalid' : '' }}" name="category" value="{{ old('category', $feature->category) }}">
            </div>
            <div class="col-lg-6 mb-4">
                <label for="description" class="mb-0 ps-3">Description</label>
                <textarea class="form-control bg-white {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" rows="3">{{ old('description', $feature->description) }}</textarea>
            </div>
            <div class="col-lg-12 mb-4">
                <label for="standard_prompt" class="mb-0 ps-3">Standard prompt</label>
                <textarea class="form-control bg-white font-monospace {{ $errors->has('standard_prompt') ? 'is-invalid' : '' }}" name="standard_prompt" rows="12">{{ old('standard_prompt', $feature->standard_prompt) }}</textarea>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="implementation_notes" class="mb-0 ps-3">Implementation notes</label>
                <textarea class="form-control bg-white {{ $errors->has('implementation_notes') ? 'is-invalid' : '' }}" name="implementation_notes" rows="5">{{ old('implementation_notes', $feature->implementation_notes) }}</textarea>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="verification_notes" class="mb-0 ps-3">Verification notes</label>
                <textarea class="form-control bg-white {{ $errors->has('verification_notes') ? 'is-invalid' : '' }}" name="verification_notes" rows="5">{{ old('verification_notes', $feature->verification_notes) }}</textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('featureReturnUrl') ?? '/apps/features' }}">Cancel</a>
            </div>
        </div>
    </div>
</div>
