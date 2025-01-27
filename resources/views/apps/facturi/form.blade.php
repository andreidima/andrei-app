@csrf

<script type="application/javascript">
    aplicatii = {!! json_encode($aplicatii) !!}
    aplicatieIdVechi = {!! json_encode(old('aplicatie_id', ($factura->actualizari->first()->aplicatie_id ?? "")) ?? "") !!}
    actualizari = {!! json_encode($actualizari) !!}
    actualizariVechi = {!! json_encode(old('actualizariAdaugateLaFactura', $factura->actualizari)) !!}
</script>

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="facturaForm">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0 justify-content-center">
            <div class="col-lg-2 mb-4 text-center">
                <label for="data" class="mb-0 ps-0">Data<span class="text-danger">*</span></label>
                <vue-datepicker-next
                    data-veche="{{ old('data', $factura->data ?? \Carbon\Carbon::today()) }}"
                    nume-camp-db="data"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD.MM.YYYY"
                    :latime="{ width: '125px' }"
                ></vue-datepicker-next>
            </div>
            <div class="col-lg-4 mb-4" style="position:relative;" v-click-out="() => aplicatiiListaAutocomplete = ''">
                <label for="aplicatie_id" class="mb-0 ps-3">Aplicație<span class="text-danger">*</span></label>
                <input
                    type="hidden"
                    v-model="aplicatie_id"
                    name="aplicatie_id">

                <div v-on:focus="autocompleteAplicatii();" class="input-group">
                    <div class="input-group-prepend d-flex align-items-center">
                        <span v-if="!aplicatie_id" class="input-group-text" id="aplicatie_nume">?</span>
                        <span v-if="aplicatie_id" class="input-group-text bg-success text-white" id="aplicatie_nume"><i class="fa-solid fa-check"></i></span>
                    </div>
                    <input
                        type="text"
                        v-model="aplicatie_nume"
                        v-on:focus="autocompleteAplicatii();"
                        v-on:keyup="autocompleteAplicatii(); aplicatie_id = '';"
                        class="form-control bg-white rounded-3 {{ $errors->has('aplicatie_nume') ? 'is-invalid' : '' }}"
                        {{-- name="aplicatie_nume" --}}
                        placeholder=""
                        autocomplete="off"
                        aria-describedby="aplicatie_nume"
                        required>
                    <div class="input-group-prepend d-flex align-items-center">
                        <span v-if="aplicatie_id" class="input-group-text text-danger" id="aplicatie_nume" v-on:click="aplicatie_id = null; aplicatie_nume = '';"><i class="fa-solid fa-xmark"></i></span>
                    </div>
                    <div class="input-group-prepend ms-2 d-flex align-items-center">
                        <button type="submit" ref="submit" formaction="/apps/pontaje/adauga-resursa/aplicatie" class="btn btn-success text-white rounded-3 py-0 px-2"
                            style="font-size: 30px; line-height: 1.2;" title="Adaugă aplicatie nouă">+</button>
                    </div>
                </div>
                <div v-cloak v-if="aplicatiiListaAutocomplete && aplicatiiListaAutocomplete.length" class="panel-footer">
                    <div class="list-group" style="max-height: 130px; overflow:auto;">
                        <button class="list-group-item list-group-item list-group-item-action py-0"
                            v-for="aplicatie in aplicatiiListaAutocomplete"
                            v-on:click="
                                aplicatie_id = aplicatie.id;
                                aplicatie_nume = aplicatie.nume;

                                aplicatiiListaAutocomplete = ''
                            ">
                                @{{ aplicatie.nume }}
                        </button>
                    </div>
                </div>
                <small v-if="!aplicatie_id" class="ps-3">* Selectați o aplicație</small>
                <small v-else class="ps-3 text-success">* Ați selectat aplicația</small>
            </div>
            <div class="col-lg-2 mb-4 text-center">
                <label for="confirmare_client" class="mb-0 ps-0">Confirmare client</label>
                <vue-datepicker-next
                    data-veche="{{ old('confirmare_client', $factura->confirmare_client) }}"
                    nume-camp-db="confirmare_client"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD.MM.YYYY"
                    :latime="{ width: '125px' }"
                ></vue-datepicker-next>
            </div>
            <div class="col-lg-2 mb-4 text-center">
                <label for="confirmare_validsoftware" class="mb-0 ps-0">Confirmare Validsoftware</label>
                <vue-datepicker-next
                    data-veche="{{ old('confirmare_validsoftware', $factura->confirmare_validsoftware) }}"
                    nume-camp-db="confirmare_validsoftware"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD.MM.YYYY"
                    :latime="{ width: '125px' }"
                ></vue-datepicker-next>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="status_plata" class="mb-0 ps-3">Status plată</label>
                <select name="status_plata" class="form-select bg-white rounded-3 {{ $errors->has('status_plata') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    <option value="1" {{ ((intval(old('status_plata', $factura->status_plata))) === 1) ? 'selected' : '' }}>Paid by the client</option>
                    <option value="2" {{ ((intval(old('status_plata', $factura->status_plata))) === 2) ? 'selected' : '' }}>The client didn't want to pay</option>
                    <option value="2" {{ ((intval(old('status_plata', $factura->status_plata))) === 3) ? 'selected' : '' }}>Awaiting client confirmation to issue the invoice</option>
                </select>
            </div>
        </div>
    </div>
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row justify-content-center">
            <div class="col-lg-4 mb-4 px-2">
                <div class="rounded-3 bg-warning bg-gradient bg-opacity-25">
                    <p class="m-0 ps-3">Actualizări nefacturate</p>
                    <div v-cloak class="panel-footer" v-if="actualizariNefacturate && actualizariNefacturate.length">
                        <div class="list-group" style="max-height: 130px; overflow:auto;">
                            <button type="button" class="list-group-item list-group-item list-group-item-action py-0 bg-warning text-dark d-flex justify-content-between align-items-center"
                                v-for="(actualizare, index) in actualizariNefacturate"
                                v-on:click="
                                    actualizariNefacturate.splice(index, 1);

                                    actualizariAdaugateLaFactura.push(actualizare);
                                    actualizariAdaugateLaFactura = sortareActualizari(actualizariAdaugateLaFactura);

                                ">
                                    @{{ index + 1 }}. @{{ actualizare.nume }} <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4 px-2">
                <div class="rounded-3 bg-success bg-gradient bg-opacity-25">
                    <p class="m-0 ps-3">Actualizări adăugate la factură</p>
                    <div v-cloak class="panel-footer" v-if="actualizariAdaugateLaFactura && actualizariAdaugateLaFactura.length">
                        <div class="list-group" style="max-height: 130px; overflow:auto;">
                            <button type="button" class="list-group-item list-group-item list-group-item-action py-0 bg-success text-white"
                                v-for="(actualizare, index) in actualizariAdaugateLaFactura"
                                v-on:click="
                                    actualizariNefacturate.push(actualizare);
                                    actualizariNefacturate = sortareActualizari(actualizariNefacturate);

                                    actualizariAdaugateLaFactura.splice(index, 1);
                                ">
                                    <input
                                        type="hidden"
                                        :name="'actualizariAdaugateLaFactura[' + index + '][id]'"
                                        v-model="actualizariAdaugateLaFactura[index].id"
                                        >
                                    <i class="fa-solid fa-arrow-left"></i> @{{ index + 1 }}. @{{ actualizare.nume }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('facturaReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
