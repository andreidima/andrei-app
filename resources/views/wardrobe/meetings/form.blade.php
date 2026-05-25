@csrf

<div class="row g-3">
    <div class="col-lg-4">
        <label for="title" class="form-label">Custom title</label>
        <input type="text" name="title" id="title" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" value="{{ old('title', $meeting->title) }}" placeholder="Leave blank to use contacts and date">
    </div>
    <div class="col-lg-4">
        <label for="met_on" class="form-label">Meeting date <span class="text-danger">*</span></label>
        <input type="date" name="met_on" id="met_on" class="form-control {{ $errors->has('met_on') ? 'is-invalid' : '' }}" value="{{ old('met_on', $meeting->met_at ? $meeting->met_at->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
    </div>
    <div class="col-lg-4">
        <label for="location" class="form-label">Location</label>
        <input type="text" name="location" id="location" class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}" value="{{ old('location', $meeting->location) }}">
    </div>
    <div class="col-lg-6">
        <label for="people" class="form-label">Contacts</label>
        @php
            $selectedPeople = collect(old('people', $meeting->exists ? $meeting->people->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->all();
        @endphp
        <select name="people[]" id="people" class="form-select {{ $errors->has('people') ? 'is-invalid' : '' }}" multiple size="8">
            @foreach ($people as $person)
                <option value="{{ $person->id }}" {{ in_array((string) $person->id, $selectedPeople, true) ? 'selected' : '' }}>{{ $person->name }} ({{ $person->contactTypeLabel() }})</option>
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
        <div id="wear-history-panel" class="alert alert-info d-none mb-0"></div>
    </div>
    <div class="col-12">
        <label for="clothes_description" class="form-label">Clothes description</label>
        <textarea name="clothes_description" id="clothes_description" rows="5" class="form-control {{ $errors->has('clothes_description') ? 'is-invalid' : '' }}">{{ old('clothes_description', $meeting->clothes_description) }}</textarea>
    </div>
    <div class="col-lg-6">
        <label for="outfit_photo" class="form-label">Full outfit photo</label>
        <input type="file" name="outfit_photo" id="outfit_photo" class="form-control {{ $errors->has('outfit_photo') ? 'is-invalid' : '' }}" accept="image/*" capture="environment">
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

@push('scripts')
<script>
    (() => {
        const people = document.getElementById('people');
        const clothingItems = document.getElementById('clothing_items');
        const panel = document.getElementById('wear-history-panel');
        const history = @json($wearHistory);
        const currentMeetingId = {{ $meeting->exists ? $meeting->id : 'null' }};
        const escapeHtml = (value) => String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        if (!people || !clothingItems || !panel) {
            return;
        }

        const selectedValues = (select) => Array.from(select.selectedOptions).map((option) => option.value);
        const selectedLabels = (select) => Array.from(select.selectedOptions).map((option) => option.textContent.trim());

        const renderHistory = () => {
            const contactIds = selectedValues(people);
            const itemIds = selectedValues(clothingItems);
            const itemIdSet = new Set(itemIds.map(String));
            const matches = [];

            contactIds.forEach((contactId) => {
                (history[contactId] || []).forEach((meeting) => {
                    if (currentMeetingId && Number(meeting.id) === Number(currentMeetingId)) {
                        return;
                    }

                    const repeatedItems = meeting.items.filter((item) => itemIdSet.has(String(item.id)));
                    if (repeatedItems.length || !itemIds.length) {
                        matches.push({
                            date: meeting.date,
                            label: meeting.label,
                            items: repeatedItems.length ? repeatedItems : meeting.items,
                            repeated: repeatedItems.length > 0,
                        });
                    }
                });
            });

            if (!contactIds.length || !matches.length) {
                panel.classList.add('d-none');
                panel.innerHTML = '';
                return;
            }

            const selectedContactText = selectedLabels(people).join(', ');
            const repeated = matches.filter((match) => match.repeated);
            const rows = (repeated.length ? repeated : matches).slice(0, 6).map((match) => {
                const itemNames = match.items.map((item) => escapeHtml(item.name)).join(', ') || 'No clothing items';
                const badge = match.repeated ? '<span class="badge bg-warning text-dark me-1">repeat</span>' : '';
                return `<li>${badge}<strong>${escapeHtml(match.date)}</strong>: ${itemNames}</li>`;
            }).join('');

            panel.className = repeated.length ? 'alert alert-warning mb-0' : 'alert alert-info mb-0';
            panel.innerHTML = `<strong>Previous wardrobe history for ${escapeHtml(selectedContactText)}</strong><ul class="mb-0 mt-2">${rows}</ul>`;
        };

        people.addEventListener('change', renderHistory);
        clothingItems.addEventListener('change', renderHistory);
        renderHistory();
    })();
</script>
@endpush
