@extends('layouts.app')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0 0;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-trophy me-1"></i>
                Achievement Milestones
            </span>
        </div>

        {{-- Search form --}}
        <div class="col-lg-6">
            <form class="needs-validation" novalidate method="GET" action="{{ url()->current() }}">
                @csrf
                <div class="row mb-1 custom-search-form justify-content-center">
                    <div id="datePicker" class="col-lg-12 d-flex justify-content-center">
                        <button class="btn btn-sm btn-primary text-white border border-dark rounded-3 mx-1" type="submit" name="action" value="previousDay">
                            <
                        </button>
                        <vue-datepicker-next
                            data-veche="{{ $searchData }}"
                            nume-camp-db="searchData"
                            tip="date"
                            value-type="YYYY-MM-DD"
                            format="DD.MM.YYYY"
                            :latime="{ width: '125px' }"
                        ></vue-datepicker-next>
                        <button class="btn btn-sm btn-primary text-white border border-dark rounded-3 mx-1" type="submit" name="action" value="nextDay">
                            >
                        </button>
                    </div>
                </div>
                <div class="row custom-search-form justify-content-center">
                    <div class="col-lg-4">
                        <button class="btn btn-sm w-100 btn-primary text-white border border-dark rounded-3" type="submit">
                            <i class="fas fa-search text-white me-1"></i>Search
                        </button>
                    </div>
                    <div class="col-lg-4">
                        <a class="btn btn-sm w-100 btn-secondary text-white border border-dark rounded-3" href="{{ url()->current() }}" role="button">
                            <i class="far fa-trash-alt text-white me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Card Body --}}
    <div class="card-body px-0 py-3">
        <div class="row">
            <div class="col-md-6 mb-3">
            </div>
        </div>




        <div class="row">
            @foreach ($achievements as $key => $achievement)
                <div class="col-md-6 mb-4 text-center">
                    <span class="badge p-1 mb-1 culoare1 fs-5">
                        {{ $key }} - {{ $days = $achievement['since']->diffInDays(Carbon::parse($searchData), false) }} days
                        <br>
                        +1 day in:
                        {{ $achievement['additionalTimeNeededForNextDay'] ?? '' }}
                    </span>
                    @foreach($milestones as $milestone)
                        @php
                            // Calculate progress percentage (max 100%)
                            if ($days > 0) {
                                $progress = min(100, ($days / $milestone['days']) * 100);
                            } else {
                                $progress = 0;
                            }
                        @endphp

                        <!-- Progress Bar -->
                        <div class="progress mb-1" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                {{ $milestone['name'] }} - {{ $milestone['days'] }} day{{ $milestone['days'] > 1 ? 's' : '' }} - {{ round($progress) }}%
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
