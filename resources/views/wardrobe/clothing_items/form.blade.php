@csrf

<div class="row g-3">
    <div class="col-lg-4">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name', $clothingItem->name) }}" placeholder="Auto-filled from brand, color, category">
    </div>
    <div class="col-lg-3">
        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
        <input type="text" name="category" id="category" list="wardrobeCategoryOptions" class="form-control {{ $errors->has('category') ? 'is-invalid' : '' }}" value="{{ old('category', $clothingItem->category) }}" required>
        <datalist id="wardrobeCategoryOptions">
            @foreach ($categories as $category)
                <option value="{{ $category }}">
            @endforeach
        </datalist>
    </div>
    <div class="col-lg-2">
        <label for="color" class="form-label">Color</label>
        <input type="text" name="color" id="color" list="wardrobeColorOptions" class="form-control {{ $errors->has('color') ? 'is-invalid' : '' }}" value="{{ old('color', $clothingItem->color) }}">
        <datalist id="wardrobeColorOptions">
            @foreach ($colors as $color)
                <option value="{{ $color }}">
            @endforeach
        </datalist>
    </div>
    <div class="col-lg-3">
        <label for="brand" class="form-label">Brand</label>
        <input type="text" name="brand" id="brand" list="wardrobeBrandOptions" class="form-control {{ $errors->has('brand') ? 'is-invalid' : '' }}" value="{{ old('brand', $clothingItem->brand) }}">
        <datalist id="wardrobeBrandOptions">
            @foreach ($brands as $brand)
                <option value="{{ $brand }}">
            @endforeach
        </datalist>
    </div>
    <div class="col-lg-6">
        <label for="photo" class="form-label">Photo</label>
        <input type="file" name="photo" id="photo" class="form-control {{ $errors->has('photo') ? 'is-invalid' : '' }}" accept="image/*" capture="environment">
        @if ($clothingItem->photoUrl())
            <div class="mt-2 d-flex align-items-center gap-3">
                <img src="{{ $clothingItem->photoUrl() }}" alt="{{ $clothingItem->name }}" class="img-thumbnail" style="width: 96px; height: 96px; object-fit: cover;">
                <div class="form-check">
                    <input type="checkbox" name="remove_photo" value="1" id="remove_photo" class="form-check-input">
                    <label for="remove_photo" class="form-check-label">Remove current photo</label>
                </div>
            </div>
        @endif
    </div>
    <div class="col-12">
        <label for="notes" class="form-label">Notes</label>
        <textarea name="notes" id="notes" rows="5" class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}">{{ old('notes', $clothingItem->notes) }}</textarea>
    </div>
    <div class="col-12 text-center">
        <button type="submit" class="btn btn-primary text-white">{{ $buttonText }}</button>
        <a href="{{ route('wardrobe.clothing-items.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</div>

@push('scripts')
<script>
    (() => {
        const name = document.getElementById('name');
        const category = document.getElementById('category');
        const color = document.getElementById('color');
        const brand = document.getElementById('brand');
        let nameTouched = Boolean(name?.value);

        name?.addEventListener('input', () => {
            nameTouched = Boolean(name.value.trim());
        });

        const updateName = () => {
            if (!name || nameTouched) {
                return;
            }

            const generated = [brand?.value, color?.value, category?.value]
                .map((value) => (value || '').trim())
                .filter(Boolean)
                .join(' ');

            name.value = generated;
        };

        [category, color, brand].forEach((field) => field?.addEventListener('input', updateName));
    })();
</script>
@endpush
