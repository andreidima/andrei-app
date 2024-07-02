@extends ('layouts.app')

@php
    use \Carbon\Carbon;
    use \Carbon\CarbonInterval;
@endphp

<script type="application/javascript">
    aplicatii= {!! json_encode($aplicatii ?? []) !!}
    searchAplicatiiSelectate={!! json_encode($searchAplicatiiSelectate ?? []) !!}
</script>

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-chart-simple me-1"></i>Pontaje - statistică
            </span>
        </div>
        <div class="col-lg-9">
            <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                @csrf
                <div class="row mb-1 custom-search-form justify-content-center" id="datePicker">
                    <div class="col-lg-4 d-flex justify-content-center">
                        <div class="d-flex me-5 align-items-center">
                            <label for="searchInterval" class="pe-1">Interval:</label>
                            <vue-datepicker-next
                                data-veche="{{ $searchInterval }}"
                                nume-camp-db="searchInterval"
                                tip="date"
                                range="range"
                                value-type="YYYY-MM-DD"
                                format="DD.MM.YYYY"
                                :latime="{ width: '210px' }"
                            ></vue-datepicker-next>
                        </div>
                        <div class="d-flex align-items-center">
                            <a class="btn btn-sm btn-success btn-opacity-50" data-bs-toggle="collapse" href="#collapseAplicatii" role="button" aria-expanded="false" aria-controls="collapseAplicatii">
                                Selectează aplicații de calculat
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row collapse" id="collapseAplicatii">
                    <div class="col-lg-12 d-flex flex-wrap" id="statisticaPontajAppsSelect">
                        <div class="d-flex me-4 px-2 rounded-3">
                            <input class="form-check-input border border-1 border-dark me-1" type="checkbox"
                                v-on:change="select($event)"
                                id="SelectDeselectAllAplications"
                                checked
                                >
                            <label class="form-check-label" for="SelectDeselectAllAplications">
                                Selectează/ Deselectează toate
                            </label>
                        </div>
                        @foreach ($aplicatii as $aplicatie)
                            <div class="d-flex me-4 px-2 rounded-3"
                                {{-- style="width:200px" --}}
                            >
                                <input class="form-check-input border border-1 border-dark me-1" type="checkbox"
                                    name="searchAplicatiiSelectate[]"
                                    v-model="searchAplicatiiSelectate"
                                    value="{{ $aplicatie->id }}" id="Aplicatie{{ $aplicatie->id }}"
                                    {{ in_array($aplicatie->id, $searchAplicatiiSelectate) ? 'checked' : '' }}>
                                <label class="form-check-label" for="Aplicatie{{ $aplicatie->id }}">
                                    {{ $aplicatie->nume }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="row custom-search-form justify-content-center mb-3">
                    <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                        <i class="fas fa-search text-white me-1"></i>Caută
                    </button>
                    <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3" href="{{ url()->current() }}" role="button">
                        <i class="far fa-trash-alt text-white me-1"></i>Resetează căutarea
                    </a>
                </div>

                <div class="row mb-1 custom-search-form justify-content-center">
                    <div class="col-lg-8 text-center">
                        {{-- <input type="hidden" id="searchLunaCalendar" name="searchLunaCalendar" value="{{ $searchLunaCalendar }}">
                        <button class="btn btn-sm btn-primary text-white border border-light rounded-3" type="submit" name="action" value="previousMonth">
                            <i class="fa-solid fa-angles-left"></i>
                        </button>
                        {{ ucfirst($searchLunaCalendar->isoFormat('MMMM YYYY')) }}
                        <button class="btn btn-sm btn-primary text-white border border-light rounded-3" type="submit" name="action" value="nextMonth">
                            <i class="fa-solid fa-angles-right"></i>
                        </button> --}}
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body px-0 py-3">

        @include ('errors.errors')

        <style>
        #lunar {
        border-collapse: collapse;
        color: rgb(151, 0, 0);
        margin: auto;
        }

        #lunar th, #lunar td {
            border: 1px solid rgb(183, 183, 183);
        }

        #lunar th {
        text-align: center;
        padding-top: 12px;
        padding-bottom: 12px;
        }

        #lunar td {
        padding: 2px 2px;
        text-align: left;
        vertical-align: text-top;

        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
        /* white-space: nowrap; */
        }

        #line {
        height: 15px;
        width: 11px;
        /* background-color: rgb(255, 118, 118); */
        /* border-radius: 50%; */
        display: inline-block;
        }

        #dot {
        height: 11px;
        width: 11px;
        /* background-color: rgb(255, 118, 118); */
        border-radius: 50%;
        display: inline-block;
        }
        </style>

        <div class="row p-md-4 rounded-3">
            @foreach ($pontajeCumulatPeZi as $ziua=>$timp)
                @php
                    $ziua = Carbon::parse($ziua);
                @endphp
                @if ($ziua->day == 1)
                    <div class="col-lg-6 mb-5">
                        <div class="table-responsive rounded-3 px-0" style="background-color: rgb(255, 255, 255)">
                            <table class="table align-middle" id="lunar" style="width: 100%">
                                <tr>
                                    <td colspan="8" class="culoare2 text-white text-center">
                                        {{ $ziua->isoFormat('MMMM YYYY') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="culoare2 text-white text-center" width="12%">Lu</td>
                                    <td class="culoare2 text-white text-center" width="12%">Ma</td>
                                    <td class="culoare2 text-white text-center" width="12%">Mi</td>
                                    <td class="culoare2 text-white text-center" width="12%">Jo</td>
                                    <td class="culoare2 text-white text-center" width="12%">Vi</td>
                                    <td class="culoare2 text-white text-center" width="12%">Sâ</td>
                                    <td class="culoare2 text-white text-center" width="12%">Du</td>
                                    <td class="culoare2 text-white text-center" width="12%">Total</td>
                                </tr>


                @endif
                    @if ($ziua->day == 1)
                        @php
                            $timpTotalMonthly = Carbon::today();
                        @endphp
                    @endif

                    {{-- The first if is to create the empty cells for the table, at the start of each month --}}
                    @if (($ziua->day == 1) && ($ziua->dayOfWeekIso > 1))
                        <tr>
                        @for ($i=1; $i < $ziua->dayOfWeekIso; $i++ )
                                <td></td>
                        @endfor
                        @php
                            $timpTotalWeekly = Carbon::today();
                        @endphp
                    @elseif ($ziua->dayOfWeekIso == 1)
                        <tr>
                        @php
                            $timpTotalWeekly = Carbon::today();
                        @endphp
                    @endif

                            <td class="{{ $timp ? (Carbon::parse($timp)->hour > 6 ? 'bg-success text-white' : '') : '' }}">
                                <p class="m-0 text-end">
                                    <span class="culoare1 text-white px-1 rounded-3 text-center mx-2" style="display: inline-block; width: 30px">
                                        {{ $ziua->day }}
                                    </span>
                                </p>
                                <p class="m-0 p-0 text-center">
                                    {{ $timp ? Carbon::parse($timp)->isoFormat('HH:mm') : '' }}
                                </p>
                                @php
                                    $timpTotalWeekly->addHours(substr($timp, 0, 2))->addMinutes(substr($timp, 3, 2))->addSeconds(substr($timp, 6, 2));
                                    $timpTotalMonthly->addHours(substr($timp, 0, 2))->addMinutes(substr($timp, 3, 2))->addSeconds(substr($timp, 6, 2));
                                @endphp

                            </td>

                    {{-- If it got to the last day of the month, but the week is not finished (day 7), the week it will be filled with empty days --}}
                    @if (($ziua->day == $ziua->isLastOfMonth()) && ($ziua->dayOfWeekIso < 7))
                        @for ($i=$ziua->dayOfWeekIso; $i < 7; $i++ )
                            <td></td>
                        @endfor
                    @endif

                    {{-- If it's the last day of the month (allready completed the last week in the previous IF with empty days (cells)), or if it is the last day of the week, the last cell will be filled with the total time per that week --}}
                    @if (($ziua->day == $ziua->isLastOfMonth()) || ($ziua->dayOfWeekIso == 7))
                            <td class="text-end {{ $timpTotalWeekly ? (Carbon::parse($timpTotalWeekly)->diffInHours(Carbon::today()) > 40 ? 'bg-success text-white' : '') : '' }}">
                                {{ $timpTotalWeekly->diffInHours(Carbon::today()) . ':' . $timpTotalWeekly->diff(Carbon::today())->format('%I') }}
                            </td>
                        </tr>
                    @endif

                    @if ($ziua->isLastOfMonth())
                        <tr>
                            <td colspan="7" class="text-end">
                            </td>
                            <td class="text-end {{ $timpTotalMonthly ? (Carbon::parse($timpTotalMonthly)->diffInHours(Carbon::today()) > 160 ? 'bg-success text-white' : '') : '' }}">
                                {{ $timpTotalMonthly->diffInHours(Carbon::today()) . ':' . $timpTotalMonthly->diff(Carbon::today())->format('%I') }}
                            </td>
                        </tr>
                    @endif

                @if ($ziua->isLastOfMonth())
                            </table>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>


@endsection
