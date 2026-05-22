@csrf

<div class="row px-3">
    <div class="col-lg-12 px-4 py-2">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <label class="mb-0 ps-3" for="name">Name<span class="text-danger">*</span></label>
                <input class="form-control bg-white rounded-3" name="name" value="{{ old('name', $user->name) }}">
            </div>
            <div class="col-lg-6 mb-4">
                <label class="mb-0 ps-3" for="email">Email<span class="text-danger">*</span></label>
                <input class="form-control bg-white rounded-3" name="email" value="{{ old('email', $user->email) }}">
            </div>
            <div class="col-lg-6 mb-4">
                <label class="mb-0 ps-3" for="role">Role<span class="text-danger">*</span></label>
                <select class="form-select bg-white rounded-3" name="role">
                    @foreach ($roleOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6 mb-4">
                <label class="mb-0 ps-3" for="password">Password{{ $user->exists ? '' : '*' }}</label>
                <input class="form-control bg-white rounded-3" type="password" name="password" autocomplete="new-password">
                @if ($user->exists)
                    <small class="ps-3">Leave empty to keep current password.</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-12 px-4 py-2">
        <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
            <a class="btn btn-lg btn-secondary rounded-3" href="{{ route('system.users.index') }}">Cancel</a>
        </div>
    </div>
</div>
