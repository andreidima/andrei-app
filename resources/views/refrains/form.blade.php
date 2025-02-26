<div class="row mb-4 pt-2 rounded-3" style="border:1px solid #e9ecef; border-left:0.25rem darkcyan solid; background-color:rgb(241, 250, 250)">
    <div class="col-lg-8 mb-4">
        <label for="name" class="mb-0 ps-3">Nume</label>
        <input
            type="text"
            class="form-control bg-white rounded-3 {{ $errors->has('name') ? 'is-invalid' : '' }}"
            name="name"
            value="{{ old('name', $refrain->name ?? '') }}">
    </div>

    <div class="col-lg-4 mb-4" id="datePicker">
        <label for="since" class="mb-0 ps-1">Refrained Since</label>
        <vue-datepicker-next
            data-veche="{{ old('data', $refrain->since ?? \Carbon\Carbon::today()) }}"
            nume-camp-db="since"
            tip="date"
            value-type="YYYY-MM-DD"
            format="DD.MM.YYYY"
            :latime="{ width: '125px' }"
        ></vue-datepicker-next>
    </div>

    <div class="col-lg-12 mb-4">
        <label for="observations" class="mb-0 ps-3">Observații</label>
        <textarea
            class="form-control bg-white rounded-3 {{ $errors->has('observations') ? 'is-invalid' : '' }}"
            name="observations"
            rows="5">{{ old('observations', $refrain->observations ?? '') }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 mb-2 d-flex justify-content-center">
        <button type="submit" class="btn btn-primary text-white me-3 rounded-3">
            <i class="fa-solid fa-save me-1"></i> {{ $buttonText }}
        </button>
        <a class="btn btn-secondary rounded-3" href="{{ Session::get('returnUrl', route('refrains.index')) }}">
            Cancel
        </a>
    </div>
</div>
