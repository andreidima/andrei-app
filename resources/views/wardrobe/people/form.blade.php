@csrf

<div class="row g-3">
    <div class="col-lg-6">
        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" id="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name', $person->name) }}" required>
    </div>
    <div class="col-lg-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email', $person->email) }}">
    </div>
    <div class="col-lg-3">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" name="phone" id="phone" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" value="{{ old('phone', $person->phone) }}">
    </div>
    <div class="col-12">
        <label for="notes" class="form-label">Notes</label>
        <textarea name="notes" id="notes" rows="5" class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}">{{ old('notes', $person->notes) }}</textarea>
    </div>
    <div class="col-12 text-center">
        <button type="submit" class="btn btn-primary text-white">{{ $buttonText }}</button>
        <a href="{{ route('wardrobe.people.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</div>
