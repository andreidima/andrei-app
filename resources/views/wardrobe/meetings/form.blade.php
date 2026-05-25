@csrf

<div class="row g-3">
    <div class="col-lg-4">
        <label for="title" class="form-label">Title</label>
        <input type="text" name="title" id="title" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" value="{{ old('title', $meeting->title) }}">
    </div>
    <div class="col-lg-4">
        <label for="met_at" class="form-label">Meeting date <span class="text-danger">*</span></label>
        <input type="datetime-local" name="met_at" id="met_at" class="form-control {{ $errors->has('met_at') ? 'is-invalid' : '' }}" value="{{ old('met_at', $meeting->met_at ? $meeting->met_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
    </div>
    <div class="col-lg-4">
        <label for="location" class="form-label">Location</label>
        <input type="text" name="location" id="location" class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}" value="{{ old('location', $meeting->location) }}">
    </div>
    <div class="col-lg-6">
        <label for="people" class="form-label">People</label>
        @php
            $selectedPeople = collect(old('people', $meeting->exists ? $meeting->people->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->all();
        @endphp
        <select name="people[]" id="people" class="form-select {{ $errors->has('people') ? 'is-invalid' : '' }}" multiple size="8">
            @foreach ($people as $person)
                <option value="{{ $person->id }}" {{ in_array((string) $person->id, $selectedPeople, true) ? 'selected' : '' }}>{{ $person->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-6">
        <label for="clothing_items" class="form-label">Clothing items</label>
        @php
            $selectedItems = collect(old('clothing_items', $meeting->exists ? $meeting->clothingItems->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->all();
        @endphp
        <select name="clothing_items[]" id="clothing_items" class="form-select {{ $errors->has('clothing_items') ? 'is-invalid' : '' }}" multiple size="8">
            @foreach ($clothingItems as $clothingItem)
                <option value="{{ $clothingItem->id }}" {{ in_array((string) $clothingItem->id, $selectedItems, true) ? 'selected' : '' }}>{{ $clothingItem->name }}{{ $clothingItem->category ? ' - ' . $clothingItem->category : '' }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label for="clothes_description" class="form-label">Clothes description</label>
        <textarea name="clothes_description" id="clothes_description" rows="5" class="form-control {{ $errors->has('clothes_description') ? 'is-invalid' : '' }}">{{ old('clothes_description', $meeting->clothes_description) }}</textarea>
    </div>
    <div class="col-lg-6">
        <label for="outfit_photo" class="form-label">Full outfit photo</label>
        <input type="file" name="outfit_photo" id="outfit_photo" class="form-control {{ $errors->has('outfit_photo') ? 'is-invalid' : '' }}" accept="image/*">
        @if ($meeting->outfitPhotoUrl())
            <div class="mt-2 d-flex align-items-center gap-3">
                <img src="{{ $meeting->outfitPhotoUrl() }}" alt="Outfit photo" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                <div class="form-check">
                    <input type="checkbox" name="remove_outfit_photo" value="1" id="remove_outfit_photo" class="form-check-input">
                    <label for="remove_outfit_photo" class="form-check-label">Remove current photo</label>
                </div>
            </div>
        @endif
    </div>
    <div class="col-lg-6">
        <label for="notes" class="form-label">Notes</label>
        <textarea name="notes" id="notes" rows="5" class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}">{{ old('notes', $meeting->notes) }}</textarea>
    </div>
    <div class="col-12 text-center">
        <button type="submit" class="btn btn-primary text-white">{{ $buttonText }}</button>
        <a href="{{ route('wardrobe.meetings.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</div>
