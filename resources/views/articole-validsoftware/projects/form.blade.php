@csrf

<div class="row px-3">
    <div class="col-lg-6 mb-3">
        <label class="mb-0 ps-3">Nume<span class="text-danger">*</span></label>
        <input type="text" class="form-control rounded-3" name="name" value="{{ old('name', $project->name) }}">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="mb-0 ps-3">Slug</label>
        <input type="text" class="form-control rounded-3" name="slug" value="{{ old('slug', $project->slug) }}" placeholder="Se genereaza automat daca ramane gol">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="mb-0 ps-3">Nume public</label>
        <input type="text" class="form-control rounded-3" name="public_name" value="{{ old('public_name', $project->public_name) }}">
    </div>
    <div class="col-lg-6 mb-3">
        <label class="mb-0 ps-3">Client</label>
        <input type="text" class="form-control rounded-3" name="client_name" value="{{ old('client_name', $project->client_name) }}">
    </div>
    <div class="col-lg-8 mb-3">
        <label class="mb-0 ps-3">Local path</label>
        <input type="text" class="form-control rounded-3" name="local_path" value="{{ old('local_path', $project->local_path) }}">
    </div>
    <div class="col-lg-4 mb-3">
        <label class="mb-0 ps-3">Status</label>
        <select name="status" class="form-select rounded-3">
            @foreach (\App\Models\ValidSoftwareBlog\BlogProject::statusOptions() as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $project->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-12 mb-3">
        <label class="mb-0 ps-3">Notes</label>
        <textarea name="notes" class="form-control rounded-3" rows="5">{{ old('notes', $project->notes) }}</textarea>
    </div>
    <div class="col-lg-12 mb-2 d-flex justify-content-center">
        <button type="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
        <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('blogProjectReturnUrl', route('validsoftware-blog.projects.index')) }}">Renunta</a>
    </div>
</div>
